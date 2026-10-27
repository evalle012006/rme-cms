<?php

namespace Tests\Unit;

use Tests\TestCase;

class CmsAdminUrlTest extends TestCase
{
    public function testBuildsAnAbsoluteCmsUrl()
    {
        config(['services.cms.url' => 'https://cms.example.com/']);

        $this->assertSame(
            'https://cms.example.com/review/edit/9',
            cmsAdminUrl('/review/edit/9')
        );
        $this->assertSame(
            'https://cms.example.com/builder/edit/4?from=pending',
            cmsAdminUrl('/builder/edit/4?from=pending')
        );
        $this->assertSame(
            'https://cms.example.com/users?request=12',
            cmsAdminUrl('/users?request=12')
        );
    }

    public function testReturnsNullWhenCmsUrlIsMissingOrPathIsUnsafe()
    {
        config(['services.cms.url' => '']);

        $this->assertNull(cmsAdminUrl('/contact'));
        $this->assertNull(cmsAdminUrl('review/edit/1'));
        $this->assertNull(cmsAdminUrl('//evil.example'));
        $this->assertNull(cmsAdminUrl('https://evil.example/review/edit/1'));
    }

    public function testAdminEmailsRenderTheLinkOnlyWhenAUrlIsPresent()
    {
        config(['services.cms.url' => 'https://cms.example.com']);

        $linked = [
            'email-template.review-submitted' => [
                'details' => [
                    'display_name' => 'Ada',
                    'full_name' => 'Ada Lovelace',
                    'property_name' => 'Harbour',
                ],
                'adminUrl' => cmsAdminUrl('/review/edit/9'),
                'href' => 'https://cms.example.com/review/edit/9',
            ],
            'email-template.company-registered' => [
                'company_name' => 'Acme',
                'adminUrl' => cmsAdminUrl('/company/edit/3'),
                'href' => 'https://cms.example.com/company/edit/3',
            ],
            'email-template.builder-company-access-request' => [
                'company_name' => 'Acme',
                'user_email' => 'builder@example.com',
                'adminUrl' => cmsAdminUrl('/builder/edit/4?from=pending'),
                'href' => 'https://cms.example.com/builder/edit/4?from=pending',
            ],
            'email-template.change-request-admin' => [
                'details' => [
                    'display_name' => 'Ada',
                    'full_name' => 'Ada Lovelace',
                    'requested_display_name' => 'Ada L',
                ],
                'adminUrl' => cmsAdminUrl('/users?request=12'),
                'href' => 'https://cms.example.com/users?request=12',
            ],
            'email-template.report-user' => [
                'data' => [
                    'reported_user' => 'Ada Lovelace',
                    'user' => 'Grace Hopper',
                    'reason' => 'Spam',
                    'company_name' => 'Acme',
                ],
                'adminUrl' => cmsAdminUrl('/builder/edit/4?from=approved'),
                'href' => 'https://cms.example.com/builder/edit/4?from=approved',
            ],
            'email-template.contact-us' => [
                'details' => [
                    'firstname' => 'Ada',
                    'lastname' => 'Lovelace',
                    'email' => 'ada@example.com',
                    'subject' => 'Hello',
                    'message' => 'A question',
                ],
                'adminUrl' => cmsAdminUrl('/contact'),
                'href' => 'https://cms.example.com/contact',
            ],
        ];

        foreach ($linked as $view => $data) {
            $html = view($view, $data)->render();
            $this->assertStringContainsString('View in admin', $html, $view);
            $this->assertStringContainsString($data['href'], $html, $view);
        }

        config(['services.cms.url' => '']);
        $html = view('email-template.company-registered', [
            'company_name' => 'Acme',
            'adminUrl' => cmsAdminUrl('/company/edit/3'),
        ])->render();
        $this->assertStringNotContainsString('View in admin', $html);
    }
}
