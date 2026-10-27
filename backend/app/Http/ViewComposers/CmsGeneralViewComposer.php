<?php

namespace App\Http\ViewComposers;

use App\Models\CmsPages;
use App\Models\CmsPageSections;
use Illuminate\Contracts\View\View;

class CmsGeneralViewComposer
{
    public function compose(View $view)
    {
        $viewToPage = [
            'welcome' => 'home',
            'about' => 'about',
            'property-guide' => 'property_guide',
            'property-coach' => 'property_coach',
            'contact' => 'contact',
            'property-listing' => 'reviews_listing',
            'review-guideline' => 'review_guidelines',
            'term-of-use' => 'terms_of_use',
            'privacy-policy' => 'privacy_policy'
        ];
        $sectionData = CmsPageSections::whereIn('cms_page_id', CmsPages::where('name', $viewToPage[$view->name()])
            ->select('id'))
            ->get()
            ->groupBy('section_slug')
            ->transform(function ($item) {
                return $item[0];
            })
            ->toArray();

        foreach ($sectionData as $slug => $section) {
            if (isset($section['body'])) {
                $section['body'] = $this->normalizeCmsSpacing($section['body']);
            }
            if (isset($section['other_params'])) {
                $section['other_params'] = $this->normalizeCmsSpacing($section['other_params']);
            }
            $sectionData[$slug] = $section;
        }

        $view->with('sectionData', $sectionData);
    }

    private function normalizeCmsSpacing($value)
    {
        if (is_string($value)) {
            $value = str_replace(
                ["\xc2\xa0", '&nbsp;', '&#160;', '&#xA0;', '&#xa0;', '&#x00A0;', '&#x00a0;'],
                ' ',
                $value
            );

            return $this->joinSoftLineBreaks($value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalizeCmsSpacing($item);
            }
        }

        return $value;
    }

    private function joinSoftLineBreaks(string $html): string
    {
        if (stripos($html, '<p') === false) {
            return $html;
        }

        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8"?><div id="cms-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('cms-root');
        if (!$root) {
            return $html;
        }

        $this->mergeParagraphRuns($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    private function mergeParagraphRuns(\DOMElement $root): void
    {
        $children = [];
        foreach ($root->childNodes as $child) {
            $children[] = $child;
        }

        $run = [];
        $flush = function () use (&$run) {
            if (count($run) > 1) {
                $first = $run[0];
                $doc = $first->ownerDocument;
                for ($i = 1, $count = count($run); $i < $count; $i++) {
                    $first->appendChild($doc->createElement('br'));
                    while ($run[$i]->firstChild) {
                        $first->appendChild($run[$i]->firstChild);
                    }
                    $run[$i]->parentNode->removeChild($run[$i]);
                }
            }
            $run = [];
        };

        foreach ($children as $child) {
            if ($child->nodeType === XML_TEXT_NODE && trim($child->textContent) === '') {
                continue;
            }

            if ($this->isParagraph($child) && $this->isEmptyParagraph($child)) {
                $flush();
                if ($child->parentNode) {
                    $child->parentNode->removeChild($child);
                }
                continue;
            }

            if ($this->isParagraph($child)) {
                $run[] = $child;
                continue;
            }

            $flush();
        }

        $flush();
    }

    private function isParagraph(\DOMNode $node): bool
    {
        return $node->nodeType === XML_ELEMENT_NODE && strtolower($node->nodeName) === 'p';
    }

    private function isEmptyParagraph(\DOMNode $node): bool
    {
        if (!$this->isParagraph($node)) {
            return false;
        }

        if (trim($node->textContent) !== '') {
            return false;
        }

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }
            if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'br') {
                continue;
            }

            return false;
        }

        return true;
    }
}
