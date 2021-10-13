<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

use App\Libraries\Lever;
use App\Libraries\Webflow;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

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

    /**
     * Check password for proposal Password
     * @param Request $request
     */
    function checkProposalPassword(Request $request) 
    {
        $status = 400;
        try {
            //Get data from request attributes bag
            $password = $request->input('password', '');
            $slug = $request->input('slug', '');

            //Check if data exists
            if ( strlen($password) && strlen($slug) ){
                //Conversation with webflow for items
                $this->_webflow = new Webflow(true);
                $proposals = $this->_webflow->getProposalBySlug($slug);
                $proposal = collect($proposals['items'])->where('slug', $slug)->first();

                //Check if credentials match 
                if ( $password == $proposal['password'] ) {
                    return $this->__sendJsonResponse(200);
                } else {
                    throw new Exception('Incorrect password', 403);
                }
            }
            throw new Exception('', 403);
        } catch (Exception $e) {
            //Fetching error code from exception
            $status = $e->getCode();
        }
        return $this->__sendJsonResponse($status);
    }

    /**
     * generating common response 
     * @param int $status
     * @param array $data
     */
    private function __sendJsonResponse($status = 400, $data = []) 
    {
        return response()->json(['status' => $status, 'data' => $data ], $status);
    }
}
