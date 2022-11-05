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
        $response = $this->http->post('api.php',[
            'form_params' => [
                '1' => $this->to,
                '2' => $this->message,
                '3' => config('sms.api_code'),
                'passwd' => config('sms.password'),
            ]
        ]);

        $this->result = $response;

        return $this->result;
    }

}
