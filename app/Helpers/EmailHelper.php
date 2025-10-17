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
     * Append 1x1 tracking pixel for opens.
     */
    public static function appendOpenTrackingPixel(string $html, int $templateId, string $recipientEmail): string
    {
        $recipientData = base64_encode(json_encode(['email' => $recipientEmail]));
        $pixelUrl = url(route('track.open', ['campaign' => $templateId, 'recipient' => $recipientData]));
        $pixelTag = '<img src="' . $pixelUrl . '" width="1" height="1" style="display:none;" alt="" />';
        // Try append before body end; else append at end
        if (stripos($html, '</body>') !== false) {
            return str_ireplace('</body>', $pixelTag . '</body>', $html);
        }
        return $html . $pixelTag;
    }

    /**
     * Convert all links to tracked links for clicks.
     */
    public static function replaceLinksWithTracking(string $html, int $templateId, string $recipientEmail): string
    {
        $recipientData = base64_encode(json_encode(['email' => $recipientEmail]));
        $pattern = '/<a\s+[^>]*href=([\'\"])(?!mailto:)([^\'\"]*)\\1[^>]*>(.*?)<\/a>/i';
        return preg_replace_callback($pattern, function ($matches) use ($templateId, $recipientData) {
            $href = $matches[2];
            $text = $matches[3];
            $encodedUrl = base64_encode($href);
            $trackingUrl = url(route('track.click', [
                'campaign' => $templateId,
                'recipient' => $recipientData,
                'url' => $encodedUrl,
            ]));
            return '<a href="' . $trackingUrl . '">' . $text . '</a>';
        }, $html);
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
        
        // Remove TinyMCE specific attributes and classes
        $html = preg_replace('/\s*data-mce-[^=]*=("|\')[^"\']*("|\')/', '', $html);
        $html = preg_replace('/\s*class=("|\')[^"\']*mce-[^"\']*("|\')/', '', $html);
        $html = preg_replace('/<p[^>]*>\s*&nbsp;\s*<\/p>/', '<p></p>', $html);
        
        // Remove empty class attributes
        $html = preg_replace('/\s*class=("|\')("|\')/', '', $html);
        
        // Fix double-escaped quotes and other common issues
        $html = str_replace('\"', '"', $html);
        $html = str_replace('\/', '/', $html);
        
        // Fix double spaces
        $html = preg_replace('/\s{2,}/', ' ', $html);
        
        // Fix non-breaking spaces
        $html = str_replace('\u00a0', '&nbsp;', $html);
        
        // Ensure proper HTML structure for tracking pixels
        if (strpos($html, '<html') === false && strpos($html, '<body') === false) {
            // If it's just a fragment, wrap it in basic HTML structure
            if (strpos($html, '<!DOCTYPE') === false) {
                $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>';
            }
        }
        
        return $html;
    }
} 