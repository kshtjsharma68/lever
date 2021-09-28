<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Webflow
{
    private $client;
    private $options;

    function __construct()
    {
        $this->options = [
            'http_errors' => false,
            'debug' => false,
            'base_uri' => 'https://api.webflow.com/collections/'.config('webflow.collection_id').'/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                "Authorization"=>"Bearer ".config('webflow.key'),
                "accept-version"=>"1.0.0"
            ]
        ];
        $this->client = Http::withOptions($this->options);
    }

    function updateCollection($data)
    {
        return $this->client->post('postings')->throw()->json();
    }

    function addItems($fields)
    {
        return $this->client->post('items', $fields)->throw()->json();
    }

    function items()
    {
        return $this->client->get('items')->throw()->json();
    }

}
