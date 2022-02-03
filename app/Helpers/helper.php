<?php 

use App\Libraries\Webflow;

/**
 * Post a draft for webflow
 */
if ( !function_exists('iswebflowDraft') ) {
    function iswebflowDraft( $status ) 
    {
        return $status !== 'published';
    }
}

/**
 * Webflow archived
 */
if ( !function_exists('iswebflowArchived') ) {
    function iswebflowArchived( $status ) 
    {
        return $status !== 'published';
    }
}

/**
 * Publish site
 */
if ( !function_exists('publishSite') ) {
    function publishSite() {
        try {
            $webflow = new Webflow;
            $sites = collect($webflow->siteLists());
            if ($sites->count()) {
                $site = $sites->first();
                $domains = collect($webflow->getSiteDomains($site['_id']))->map(function($domain) { return $domain['name']; })->toArray();
                $webflow->publishSingleSite($site['_id'], $domains);
            }
        } catch ( Exception $e ) {
            
        }
    }
}

