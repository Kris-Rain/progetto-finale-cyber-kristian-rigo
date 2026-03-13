<?php

namespace App\Logging;

use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class PapertrailHandler extends AbstractProcessingHandler
{
    protected string $url;
    protected string $token;

    public function __construct(string $url, string $token, $level = \Monolog\Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->url = $url;
        $this->token = $token;
    }

    protected function write(LogRecord $record): void
    {
        $message = $record->formatted ?? $record->message;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/octet-stream',
                'Authorization' => "Bearer {$this->token}",
            ])->withBody($message, 'application/octet-stream')
                ->post($this->url);
            if ($response->failed()) {
                error_log("Papertrail HTTP request failed: " . $response->body());
            }
        } catch (\Throwable $e) {
            error_log("Papertrail logging errors: " . $e->getMessage());
        }
    }
}