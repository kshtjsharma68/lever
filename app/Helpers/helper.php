<?php 

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