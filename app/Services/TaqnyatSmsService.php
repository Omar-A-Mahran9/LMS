<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TaqnyatSmsService
{
    protected $bearer;
    protected $sender;

    public function __construct()
    {
        $this->bearer = config('services.taqnyat.bearer');
        $this->sender = config('services.taqnyat.sender');
    }

    public function sendMessage($phone, $message)
    {
        $response = Http::withToken($this->bearer)
            ->post('https://api.taqnyat.sa/v1/messages', [
                'recipients' => [$phone],
                'body'       => $message,
                'sender'     => $this->sender,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Taqnyat SMS failed: ' . $response->body());
        }

        return $response->json();
    }
}
