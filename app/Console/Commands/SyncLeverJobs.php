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
    protected $name = 'LeverJobs';

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
            $result1 = $this->_lever->postings();
            collect($result1['data'])->each(function ($post) {dd($post);
                $payload = [
                    'fields' => [
                        'lever-id-2'        => $post['id'],
                        'name'              => $post['text'],
                        'job-description'   => str_replace(PHP_EOL, '<br/>', $post['content']['description']),
                        'closing'           => $post['content']['closingHtml'],
                        'lists'             => $this->sortLists($post['content']['lists']),
                        'link-to-job'       => $post['urls']['show'],
                        'workplace'         => $post['categories']['commitment'],
                        'career-description'=> $post['categories']['location'],
                        'team'              => $post['categories']['team'],
                        '_draft'            => false,
                        '_archived'         => false
                    ]
                ];
                $this->_webflow->addItems($payload);
            });
            // Show all the job postings
            $items = $this->_webflow->items();
        } catch (Exception $e) {
            
        }
    }

}
