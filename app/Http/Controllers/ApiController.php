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
            // $result1 = $this->_lever->postings();
            // collect($result1['data'])->each(function ($post) {
            //     $payload = [
            //         'fields' => [
            //             'lever-id-2'        => $post['id'],
            //             'name'              => $post['text'],
            //             'job-description'   => str_replace(PHP_EOL, '<br/>', $post['content']['description']),
            //             'closing'           => $post['content']['closingHtml'],
            //             'lists'             => $this->sortLists($post['content']['lists']),
            //             'link-to-job'       => $post['urls']['show'],
            //             'workplace'         => $post['categories']['commitment'],
            //             'career-description'=> $post['categories']['location'],
            //             'team'              => $post['categories']['team'],
            //             '_draft'            => false,
            //             '_archived'         => false
            //         ]
            //     ];
            //     $this->_webflow->addItems($payload);
            // });
            // Show all the job postings
            $items = $this->_webflow->items(); dd($items);
        } catch (Exception $e) {
            
        }
    }

    /**
     * Sort list data
     */
    private function sortLists($data)
    {
        $lists = '';
        foreach($data as $list) {
            $lists .= $list['text'].':'.$list['content'];
        }
        return $lists;
    }
}
