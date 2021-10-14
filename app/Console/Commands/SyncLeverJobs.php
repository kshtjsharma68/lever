<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\Lever;
use App\Libraries\Webflow;
use Exception;

class SyncLeverJobs extends Command {

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
        try {
            $this->info('Executing the command');
            $result = $this->_lever->postings();
            //Fetching collection
            $leverPostings = collect($result['data']);
            $this->info('Posts Found'.   $leverPostings->count());
            if (!$leverPostings->count()) {
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
                        $this->info('Updated Collection '.$existingPost->_id);

                    } else {
                        //Add webflow post for publishing
                        $this->info('Creating Collection ');
                        $payload = $this->__createpayload($post, 0, (object)[]);
                        $this->_webflow->addItem($payload);
                    }
                });
                //Sending response
                return response()->json([
                    'items' => $webflowPosts
                ], 200);
            }
        } catch (Exception $e) {
            $this->info('lever exception: '.$e->getMessage());
        }
        $this->info('Lever command execution completed');
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
