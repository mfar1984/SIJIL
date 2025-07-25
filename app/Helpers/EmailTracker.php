<?php

namespace App\Helpers;

class EmailTracker
{
    /**
     * Replace all links in the email content with tracking links.
     *
     * @param string $content
     * @param int $campaignId
     * @param string $recipientData
     * @return string
     */
    public static function replaceLinkWithTracking($content, $campaignId, $recipientData)
    {
        $pattern = '/<a\s+[^>]*href=([\'"])(?!mailto:)([^\'"]*)\\1[^>]*>(.*?)<\/a>/i';
        
        return preg_replace_callback($pattern, function ($matches) use ($campaignId, $recipientData) {
            $originalUrl = $matches[2];
            $linkText = $matches[3];
            
            // Encode the target URL
            $encodedUrl = base64_encode($originalUrl);
            
            // Create the tracking URL
            $trackingUrl = url(route('track.click', [
                'campaign' => $campaignId,
                'recipient' => $recipientData,
                'url' => $encodedUrl,
            ]));
            
            return '<a href="' . $trackingUrl . '">' . $linkText . '</a>';
        }, $content);
    }
} 