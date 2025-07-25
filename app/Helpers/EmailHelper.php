<?php

namespace App\Helpers;

class EmailHelper
{
    /**
     * Personalize content for specific recipient.
     *
     * @param  string  $content
     * @param  array  $recipient
     * @return string
     */
    public static function personalizeContent($content, array $recipient)
    {
        // Clean the HTML first
        $content = self::cleanHtml($content);
        
        // Replace placeholders
        $content = str_replace('{name}', $recipient['name'] ?? 'Participant', $content);
        $content = str_replace('{email}', $recipient['email'] ?? '', $content);
        
        return $content;
    }
    
    /**
     * Clean HTML from unnecessary classes and formatting.
     *
     * @param  string  $html
     * @return string
     */
    public static function cleanHtml($html)
    {
        if (empty($html)) {
            return '';
        }
        
        // Remove ng-star-inserted classes
        $html = preg_replace('/\s*class=("|\')ng-star-inserted("|\')/', '', $html);
        
        // Remove class attributes containing ng-star-inserted
        $html = preg_replace('/\s*class=("|\')[^"\']*ng-star-inserted[^"\']*("|\')/', '', $html);
        
        // Remove empty class attributes
        $html = preg_replace('/\s*class=("|\')("|\')/', '', $html);
        
        // Fix double-escaped quotes and other common issues
        $html = str_replace('\"', '"', $html);
        $html = str_replace('\/', '/', $html);
        
        // Fix double spaces
        $html = preg_replace('/\s{2,}/', ' ', $html);
        
        // Fix non-breaking spaces
        $html = str_replace('\u00a0', '&nbsp;', $html);
        
        return $html;
    }
} 