<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Lever
{
    private $client;
    private $options;

    function __construct()
    {
        $this->options = [
            'http_errors' => false,
            'debug' => false,
            'base_uri' => 'https://api.lever.co/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Basic ".base64_encode(config('lever.key'))
            ]
        ];
        $this->client = Http::withOptions($this->options);
    }

    function postings()
    {
        $query = [
            'state' => 'published'
        ];
        return $this->client->get('postings', $query)->throw()->json();
    }
}
