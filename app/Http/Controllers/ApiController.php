<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

use App\Libraries\Lever;
use App\Libraries\Webflow;
use Exception;
use GuzzleHttp\Exception\ClientException;

class ApiController extends Controller
{
    private $_lever;
    private $_webflow;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //Setup the contructor for apis
        $this->_lever = new Lever;
        $this->_webflow = new Webflow;
    }

    /**
     * Fetch all the information from 
     * lever and check to upload to 
     * Webflow collection
     * @param Request $request
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $result = $this->_lever->postings();
            //Fetching collection
            $leverPostings = collect($result['data']);
            if ($leverPostings->count()) {
                // Show all the job postings
                $records = $this->_webflow->items();
                $webflowPosts = collect($records['items']);
                collect($result['data'])->each(function ($post) use ($webflowPosts) {
                    $exists = $this->__checkIfPostExists($post, $webflowPosts);
                    if ($exists['status']) {
                        $existingPost = (object)($exists['post']);
                        $payload = $this->__createpayload($post, 1, $existingPost);
                        //Update webflow post
                        $this->_webflow->updateItem($existingPost->_id, $payload);
                        dd($this->_webflow->getItem($existingPost->_id));
                    } else {
                        //Add webflow post for publishing
                        $payload = $this->__createpayload($post, 0, (object)[]);
                        $this->_webflow->addItem($payload);
                    }
                });
                //Sending response
                return response()->json([
                    'items' => $webflowPosts
                ], 200);
            }
            throw new Exception('No posting on lever.');
        } catch (Exception $e) {
            dd($e->getResponse()->getBody()->getContents());
        }
        return response()->json([], 400);
    }

    /**
     * Sort list data
     */
    private function sortLists($data)
    {
        $lists = '';
        foreach ($data as $list) {
            $lists .= $list['text'] . ':' . $list['content'];
        }
        return $lists;
    }

    /**
     * Check if record exists on webflow
     * @param array $post
     * @param Collection $records
     * @return array
     */
    private function __checkIfPostExists(array $post, $records)
    {
        $result = [
            'status' => false,
            'post' => []
        ];
        //Check if records exists
        if ($records->contains('lever-id-2', $post['id'])) {
            $result['status'] = true;
            $result['post'] = $records->where('lever-id-2', $post['id'])->first();
        }

        return $result;
    }

    /**
     * Create payload for webflow
     * @param array $post
     * @param bool
     * @param object
     */
    private function __createpayload(array $post, $update = false, $existing)
    {
        $payload =  [
            'fields' => [
                'lever-id-2'        => $post['id'],
                'name'              => $post['text'],
                'job-description'   => str_replace(PHP_EOL, '<br/>', $post['content']['description']),
                'closing'           => $post['content']['closingHtml'],
                'lists'             => $this->sortLists($post['content']['lists']),
                'link-to-job'       => $post['urls']['show'],
                'workplace'         => $post['categories']['commitment'],
                'career-description' => $post['categories']['location'],
                'team'              => $post['categories']['team'],
                '_draft'            => iswebflowDraft($post['state']),
                '_archived'         => iswebflowArchived($post['state'])
            ]
        ];

        if ( $update ) {
            $payload['fields']['slug'] = $existing->slug;
        }

        return $payload;
    }
}
