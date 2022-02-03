<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\Lever;
use App\Libraries\Webflow;
use Exception;

class SyncLeverJobs extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Lever:Job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Sync the jobs of lever with collection";


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_lever = new Lever;
        $this->_webflow = new Webflow;
    }

    /**
     * Execute the console command.
     *
     * @param  
     * @return mixed
     */
    public function handle()
    {
        $this->report('Started Job sync. time: ' . time() . "\n");
        try {
            $result = $this->_lever->postings();
            //Fetching collection
            $leverPostings = collect($result['data']);
            // Show all the job postings
            $records = $this->_webflow->items();
            $webflowPosts = collect($records['items']);
            if ($leverPostings->count()) {
                $leverPostings->each(function ($post) use (&$webflowPosts) {
                    $exists = $this->__checkIfPostExists($post, $webflowPosts);
                    if ($exists['status']) {
                        $existingPost = (object)($exists['post']);
                        $payload = $this->__createpayload($post, $existingPost, 1);
                        //Update webflow post
                        $this->_webflow->updateItem($existingPost->_id, $payload);
                        $webflowPosts = $webflowPosts->filter(function($element) use($post) { return $element['lever-id-2'] !== $post['id']; });
                    } else {
                        //Add webflow post for publishing
                        $payload = $this->__createpayload($post, (object)[], 0);
                        $this->_webflow->addItem($payload);
                    }
                });
                
                //Disabling the remaining posts on webflow
                if ( $webflowPosts->count() ) {
                    $webflowPosts->each(function ($post) {
                        $payload['fields']['name'] = $post['name'];
                        $payload['fields']['slug'] = $post['slug'];
                        $payload['fields']['_draft'] = true;
                        $payload['fields']['_archived'] = true;
                        //Update webflow post
                        $this->_webflow->updateItem($post['_id'], $payload);
                    });
                }
            } else {
                $webflowPosts->each(function ($post) {
                    $payload['fields']['name'] = $post['name'];
                    $payload['fields']['slug'] = $post['slug'];
                    $payload['fields']['_draft'] = true;
                    $payload['fields']['_archived'] = true;
                    //Update webflow post
                    $this->_webflow->updateItem($post['_id'], $payload);
                });
            }
            publishSite();
        } catch (Exception $e) {
            $this->info('lever exception: ' . $e->getMessage());
            $this->report('lever exception: ' . $e->getMessage());
        }
        $message = 'Lever command execution completed ' . time() . "   ";
        $this->info($message);
        $this->report($message);
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
    private function __createpayload(array $post, $existing, $update = false)
    {

        if ($update) {
            $payload['fields']['slug'] = $existing->slug;
            $payload['fields']['name'] = $post['text'];
            $payload['fields']['_draft'] = iswebflowDraft($post['state']);
            $payload['fields']['_archived'] = iswebflowArchived($post['state']);
            $payload['fields']['career-description'] = $post['categories']['location'];
        } else {
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
        }

        return $payload;
    }
    /**
     *
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
     * Report message to logs
     * @param string $message
     * @return void
     */
    private function report($message)
    {
        file_put_contents(storage_path('logs/sync-logs.log'), (string) $message, FILE_APPEND);
    }
}
