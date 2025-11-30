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