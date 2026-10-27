<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ResendService
{
    /** @var string|null */
    private $apiKey;

    /** @var string */
    private $fromAddress;

    /** @var string */
    private $fromName;

    public function __construct()
    {
        $this->apiKey = config('services.resend.api_key');
        $this->fromAddress = config('mail.from.address');
        $this->fromName = config('mail.from.name');
    }

    /**
     * Render a Blade view and send it via the Resend HTTP API.
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return bool
     */
    public function send(string $to, string $subject, string $view, array $data = []): bool
    {
        try {
            $html = view($view, $data)->render();

            // In automated tests, skip the HTTP call but treat as success so mail
            // assertions are not required. Outside testing, a missing key is a failure.
            if (app()->environment('testing')) {
                Log::info('RESEND_SKIPPED', [
                    'to' => $to,
                    'subject' => $subject,
                    'view' => $view,
                    'reason' => 'testing',
                ]);
                return true;
            }

            if (empty($this->apiKey)) {
                Log::error('RESEND_SKIPPED', [
                    'to' => $to,
                    'subject' => $subject,
                    'view' => $view,
                    'reason' => 'missing_api_key',
                ]);
                return false;
            }

            return $this->sendWithGuzzle($to, $subject, $html);
        } catch (\Exception $e) {
            Log::error('RESEND_SEND_ERROR: ' . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject,
                'view' => $view,
            ]);

            return false;
        }
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $html
     * @return bool
     */
    private function sendWithGuzzle(string $to, string $subject, string $html): bool
    {
        $client = new Client([
            'http_errors' => false,
            'timeout' => 15,
        ]);

        $payload = [
            'from' => sprintf('%s <%s>', $this->fromName, $this->fromAddress),
            'to' => [$to],
            'subject' => $subject,
            'html' => $html,
        ];

        $logo = $this->logoAttachment();
        if ($logo) {
            $payload['attachments'] = [$logo];
        }

        $response = $client->post('https://api.resend.com/emails', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        }

        Log::error('RESEND_SEND_FAILED', [
            'to' => $to,
            'subject' => $subject,
            'status' => $statusCode,
            'body' => (string) $response->getBody(),
        ]);

        return false;
    }

    /**
     * Inline logo so mail clients do not have to fetch a remote image.
     *
     * @return array|null
     */
    private function logoAttachment()
    {
        $path = public_path('images/logo.png');

        if (!is_readable($path)) {
            Log::error('RESEND_LOGO_MISSING', ['path' => $path]);
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            Log::error('RESEND_LOGO_MISSING', ['path' => $path]);
            return null;
        }

        return [
            'filename' => 'logo.png',
            'content' => base64_encode($content),
            'content_type' => 'image/png',
            'content_id' => 'logo',
        ];
    }
}
