<?php

namespace App\Services;

use Taqnyat\TaqnyatSms;
use TaqnyatSms as GlobalTaqnyatSms;

class TaqnyatSmsService
{
    protected $client;

    public function __construct()
    {
        $bearer = config('services.taqnyat.bearer');
        $this->client = new GlobalTaqnyatSms($bearer);
    }

    public function getStatus()
    {
        return $this->client->sendStatus();
    }

    public function sendMessage($to, $message, $sender = null)
    {
        return $this->client->sendMsg($to, $message, $sender ?? config('services.taqnyat.sender'));
    }
}
