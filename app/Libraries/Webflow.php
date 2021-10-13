<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Webflow
{
    private $client;
    private $options;

    function __construct($isProposal = false)
    {
        $this->options = [
            'http_errors' => false,
            'debug' => false,
            'base_uri' => 'https://api.webflow.com/collections/'.config($isProposal ? 'webflow.porposal_collection_id' : 'webflow.collection_id').'/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                "Authorization"=>"Bearer ".config('webflow.key'),
                "accept-version"=>"1.0.0"
            ]
        ];
        $this->client = Http::withOptions($this->options);
    }

    /**
     * Update a collection item
     */
    function updateCollection($data)
    {
        return $this->client->post('postings')->throw()->json();
    }

    /**
     * Add single post/item to webflow collection
     * @param array $fields
     */
    function addItem($fields)
    {
        return $this->client->post('items', $fields)->throw()->json();
    }

    /**
     * Update a single item/post in collection
     * @param string $id
     * @param array $fields
     */
    function updateItem($id, $fields)
    {   
        return $this->client->put('items/'.$id, $fields)->throw()->json();
    }

    /**
     * Fetch all collection items/posts
     */
    function items()
    {
        return $this->client->get('items')->throw()->json();
    }

    /**
     * Get single item/post
     * @param string $id
     */
    function getItem($id)
    {
        return $this->client->get('items/'.$id)->throw()->json();
    }

    /**
     * Get proposal collection item by slug
     */
    function getProposalBySlug($slug = '')
    {
        $query = [
            'slug' => $slug
        ];
        return $this->client->get('items', $query)->throw()->json();
    }

}
