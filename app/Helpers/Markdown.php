<?php
namespace App\Helpers;

/**
 * Universal Content Formatter & Sanitizer
 * Supports both Rich-Text WYSIWYG (HTML with video/image embeds) and Markdown.
 * Strict XSS sanitization protects against malicious scripts.
 */
class Markdown {
    public static function toHtml(string $content): string {
        $content = trim($content);
        if (empty($content)) return '';

        // Check if content is already Rich HTML (from Quill WYSIWYG editor)
        if (preg_match('/<[a-z][\s\S]*>/i', $content)) {
            return self::sanitizeHtml($content);
        }

        // Otherwise parse as Markdown
        return self::parseMarkdown($content);
    }

    /**
     * Sanitize Rich Text HTML to allow Word-like features (headings, bold, image, video iframes)
     * while stripping XSS vectors (<script>, on* event handlers, javascript: protocols).
     */
    public static function sanitizeHtml(string $html): string {
        // Strip dangerous tags: <script>, <style>, <link>, <form>, <input>, <meta>, <applet>, <object>
        $cleaned = preg_replace('/<\s*(script|style|link|form|input|button|meta|applet|object|embed)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $html);
        $cleaned = preg_replace('/<\s*(script|style|link|form|input|button|meta|applet|object|embed)[^>]*>/is', '', $cleaned);

        // Strip inline event handlers: onclick, onerror, onload, onmouseover, etc.
        $cleaned = preg_replace('/(\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+))/i', '', $cleaned);

        // Strip javascript: URLs from href and src
        $cleaned = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', '', $cleaned);

        // Make embedded videos (YouTube / Vimeo iframes) responsive with a 16:9 aspect ratio container
        $cleaned = preg_replace_callback('/<iframe[^>]*src=["\']([^"\']*)["\'][^>]*>(?:<\/iframe>)?/i', function ($matches) {
            $src = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
            // Only allow legitimate video embedding domains
            if (preg_match('/^(https:\/\/www\.youtube\.com\/embed\/|https:\/\/player\.vimeo\.com\/video\/)/i', $src)) {
                return '<div class="relative w-full aspect-video rounded-2xl overflow-hidden my-6 shadow-md"><iframe src="' . $src . '" class="absolute inset-0 w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
            }
            return '';
        }, $cleaned);

        // Style images nicely (rounded, responsive, lazy loading)
        $cleaned = preg_replace('/<img\s+([^>]*?)src="([^"]+)"([^>]*?)>/i', '<img src="$2" class="rounded-2xl max-w-full h-auto my-6 shadow-md border border-gray-200 dark:border-gray-800" loading="lazy" $1 $3>', $cleaned);

        return $cleaned;
    }

    /**
     * Fallback standard Markdown parser
     */
    public static function parseMarkdown(string $markdown): string {
        $text = htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8');

        // Fenced Code Blocks
        $text = preg_replace_callback('/```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/m', function ($matches) {
            $lang = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
            $code = trim($matches[2]);
            return '<pre class="bg-gray-900 text-gray-100 p-4 rounded-xl overflow-x-auto my-4 text-sm font-mono"><code class="language-' . $lang . '">' . $code . '</code></pre>';
        }, $text);

        // Inline Code
        $text = preg_replace('/`([^`]+)`/', '<code class="bg-gray-100 dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded text-sm font-mono">$1</code>', $text);

        // Headers
        $text = preg_replace('/^### (.*?)$/m', '<h3 class="text-xl font-bold text-gray-900 dark:text-white mt-6 mb-3">$1</h3>', $text);
        $text = preg_replace('/^## (.*?)$/m', '<h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">$1</h2>', $text);
        $text = preg_replace('/^# (.*?)$/m', '<h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mt-8 mb-4">$1</h1>', $text);

        // Blockquotes
        $text = preg_replace('/^> (.*?)$/m', '<blockquote class="border-l-4 border-indigo-500 pl-4 py-1 italic text-gray-700 dark:text-gray-300 my-4 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-r-lg">$1</blockquote>', $text);

        // Bold & Italics
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $text);

        // Lists
        $text = preg_replace('/^\s*[\-\*]\s+(.*?)$/m', '<li class="ml-4 list-disc text-gray-700 dark:text-gray-300 leading-relaxed">$1</li>', $text);

        // Links
        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($matches) {
            $label = $matches[1];
            $url = trim($matches[2]);
            if (preg_match('/^(https?:\/\/|\/|#)/i', $url)) {
                return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 underline font-medium hover:text-indigo-800">' . $label . '</a>';
            }
            return $label;
        }, $text);

        // Paragraphs
        $paragraphs = explode("\n\n", $text);
        $output = [];
        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para === '') continue;
            if (!preg_match('/^<(h[1-6]|pre|blockquote|li|ul)/i', $para)) {
                $para = '<p class="text-gray-700 dark:text-gray-300 leading-relaxed my-4 text-base md:text-lg">' . nl2br($para) . '</p>';
            }
            $output[] = $para;
        }

        return implode("\n", $output);
    }
}
