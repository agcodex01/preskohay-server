<?php

namespace App\Http\Implementations;

use GuzzleHttp\Client;
use App\Http\Services\SmsService;

class SmsImplement implements SmsService
{
    private Client $http;

    public $to;
    public $message;
    public $result;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('sms.end_point')
        ]);
    }

    public function to($to)
    {
        $this->to = $to;

        return $this;
    }

    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    public function send()
    {
        $basic = new \Vonage\Client\Credentials\Basic(config('sms.nexmo_key'), config('sms.nexmo_secret'));
        $client = new \Vonage\Client($basic);

        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS(
                $this->to,
                'Preskohay',
                $this->message
                )
        );

        $this->result = $response->current();

        return $this->result;
    }

}
