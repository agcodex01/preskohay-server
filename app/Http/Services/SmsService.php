<?php

namespace App\Http\Services;

interface SmsService
{
    public function to($to);

    public function message($message);

    public function send();

}
