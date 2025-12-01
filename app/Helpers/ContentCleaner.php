<?php

namespace App\Helpers;

class ContentCleaner
{
    /**
     * Clean and format content for better readability
     * 
     * @param string $content Raw content to clean
     * @param bool $isExcerpt Whether this is an excerpt (will be converted to plain text)
     * @return string Cleaned content
     */
    public static function clean(string $content, bool $isExcerpt = false): string
    {
        if (empty($content)) {
            return '';
        }
        
        // Normalize line breaks
        $content = preg_replace('/\r\n|\r/', "\n", $content);
        
        // Fix markdown headers - ensure proper line breaks before headers
        $content = preg_replace('/([^\n])(##\s+)/', "$1\n\n$2", $content);
        $content = preg_replace('/([^\n])(###\s+)/', "$1\n\n$2", $content);
        $content = preg_replace('/([^\n])(####\s+)/', "$1\n\n$2", $content);
        
        // Fix bullet points - ensure line break before bullets
        $content = preg_replace('/([^\n])(\*\s+)/', "$1\n$2", $content);
        $content = preg_replace('/([^\n])(-\s+)/', "$1\n$2", $content);
        
        // Fix numbered lists - ensure line break before numbered items
        $content = preg_replace('/([^\n])(\d+\.\s+)/', "$1\n$2", $content);
        
        // Fix bold/italic markdown that might be missing spaces
        $content = preg_replace('/([^\s])\*\*([^\s])/', "$1 **$2", $content); // Add space before **
        $content = preg_replace('/([^\s])\*\*([^\s])/', "$1** $2", $content); // Add space after **
        
        // Clean up multiple consecutive spaces (but preserve intentional spacing)
        $content = preg_replace('/[ \t]{2,}/', ' ', $content);
        
        // Remove trailing spaces from each line
        $content = preg_replace('/[ \t]+$/m', '', $content);
        
        // Normalize multiple line breaks (max 2 consecutive)
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        // Remove leading/trailing whitespace from entire content
        $content = trim($content);
        
        // Convert line breaks to HTML (only if not excerpt and content doesn't already have HTML tags)
        if (!$isExcerpt && !preg_match('/<[a-z][\s\S]*>/i', $content)) {
            // Split by double line breaks to create paragraphs
            $paragraphs = preg_split('/\n\s*\n/', $content);
            $paragraphs = array_filter(array_map('trim', $paragraphs)); // Remove empty paragraphs
            
            if (count($paragraphs) > 1) {
                // Multiple paragraphs: wrap each in <p> tags
                $htmlContent = '';
                foreach ($paragraphs as $paragraph) {
                    if (empty(trim($paragraph))) {
                        continue;
                    }
                    // Convert single line breaks within paragraph to <br>
                    $paragraph = nl2br($paragraph, false);
                    $htmlContent .= '<p>' . $paragraph . '</p>' . "\n";
                }
                $content = trim($htmlContent);
            } else {
                // Single paragraph or no double breaks: just convert \n to <br>
                $content = nl2br($content, false);
            }
        }
        
        // For excerpt, convert to plain text
        if ($isExcerpt) {
            // Remove HTML tags
            $content = strip_tags($content);
            // Remove markdown formatting
            $content = preg_replace('/\*\*([^*]+)\*\*/', '$1', $content); // Remove bold
            $content = preg_replace('/\*([^*]+)\*/', '$1', $content); // Remove italic
            $content = preg_replace('/#+\s+/', '', $content); // Remove headers
            // Convert to single line
            $content = preg_replace('/\s+/', ' ', $content);
            $content = trim($content);
        }
        
        return $content;
    }
}