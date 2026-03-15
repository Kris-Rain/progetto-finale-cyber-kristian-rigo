<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // aggiunto

class HttpService
{
    protected Client $client;
    protected array $allowedDomains = ['internal.finance', 'newsapi.org'];
    protected array $allowedProtocols = ['http', 'https'];
    protected string $refererHeader;

    public function __construct()
    {
        $this->refererHeader = config('app.url');
        $this->client = new Client();
    }

    public function getRequest($url)
    {
        $parsedUrl = parse_url($url);

        if (!$parsedUrl || !isset($parsedUrl['scheme'], $parsedUrl['host'])) {
            Log::warning(
                'SSRF blocked: invalid url -'
                . ' url: ' . $url
                . ', user_id: ' . auth()->id()
                . ', ip: ' . request()->ip()
            );
            throw new \RuntimeException('Invalid URL');
        }

        $scheme = strtolower($parsedUrl['scheme']);
        $host = strtolower($parsedUrl['host']);

        if (!in_array($scheme, $this->allowedProtocols, true)) {
            Log::warning(
                'SSRF blocked: protocol not allowed -'
                . ' scheme: ' . $scheme
                . ', url: ' . $url
                . ', user_id: ' . auth()->id()
                . ', ip: ' . request()->ip()
            );
            throw new \RuntimeException('Protocol not allowed');
        }

        if (!in_array($host, $this->allowedDomains, true)) {
            Log::warning(
                'SSRF blocked: domain not allowed -'
                . ' host: ' . $host
                . ', url: ' . $url
                . ', user_id: ' . auth()->id()
                . ', ip: ' . request()->ip()
            );
            throw new \RuntimeException('Domain not allowed');
        }

        if ($host === 'internal.finance' && (!Auth::check() || !Auth::user()->is_admin)) {
            Log::warning(
                'SSRF blocked: unauthorized internal target -'
                . ' host: ' . $host
                . ', url: ' . $url
                . ', user_id: ' . auth()->id()
                . ', ip: ' . request()->ip()
            );
            throw new \RuntimeException('Unauthorized target');
        }

        $options = [
            'timeout' => 5,
            'allow_redirects' => false,
            'headers' => [],
        ];

        if ($host === 'internal.finance') {
            $options['headers']['Referer'] = $this->refererHeader;
        }

        try {
            $response = $this->client->request('GET', $url, $options);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            Log::warning(
                'HTTP request failed -'
                . ' url: ' . $url
                . ', user_id: ' . auth()->id()
                . ', ip: ' . request()->ip()
                . ', error: ' . $e->getMessage()
            );
            throw new \RuntimeException('Something went wrong: ' . $e->getMessage());
        }
    }
}
