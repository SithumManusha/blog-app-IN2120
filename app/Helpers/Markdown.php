<?php
namespace App\Helpers;

/**
 * Lightweight and Secure Markdown to HTML parser
 * Sanitizes unsafe tags to prevent Stored XSS.
 */
class Markdown {
    public static function toHtml(string $markdown): string {
        // 1. Initial sanitization: escape raw HTML tags
        $text = htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8');

        // 2. Fenced Code Blocks (```lang ... ```)
        $text = preg_replace_callback('/```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/m', function ($matches) {
            $lang = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
            $code = trim($matches[2]);
            return '<pre class="bg-gray-900 text-gray-100 p-4 rounded-xl overflow-x-auto my-4 text-sm font-mono"><code class="language-' . $lang . '">' . $code . '</code></pre>';
        }, $text);

        // 3. Inline Code (`code`)
        $text = preg_replace('/`([^`]+)`/', '<code class="bg-gray-100 dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded text-sm font-mono">$1</code>', $text);

        // 4. Headers (# h1, ## h2, ### h3)
        $text = preg_replace('/^### (.*?)$/m', '<h3 class="text-xl font-bold text-gray-900 dark:text-white mt-6 mb-3">$1</h3>', $text);
        $text = preg_replace('/^## (.*?)$/m', '<h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">$1</h2>', $text);
        $text = preg_replace('/^# (.*?)$/m', '<h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mt-8 mb-4">$1</h1>', $text);

        // 5. Blockquotes (> quote)
        $text = preg_replace('/^> (.*?)$/m', '<blockquote class="border-l-4 border-indigo-500 pl-4 py-1 italic text-gray-700 dark:text-gray-300 my-4 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-r-lg">$1</blockquote>', $text);

        // 6. Bold & Italics
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $text);

        // 7. Unordered Lists (- item or * item)
        $text = preg_replace('/^\s*[\-\*]\s+(.*?)$/m', '<li class="ml-4 list-disc text-gray-700 dark:text-gray-300 leading-relaxed">$1</li>', $text);

        // 8. Markdown Links [text](url) - strictly validate protocols
        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($matches) {
            $label = $matches[1];
            $url = trim($matches[2]);
            if (preg_match('/^(https?:\/\/|\/|#)/i', $url)) {
                return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 underline font-medium hover:text-indigo-800">' . $label . '</a>';
            }
            return $label;
        }, $text);

        // 9. Paragraphs and line breaks
        $paragraphs = explode("\n\n", $text);
        $output = [];
        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para === '') continue;
            // Do not wrap headers, blockquotes, code blocks, or lists in <p>
            if (!preg_match('/^<(h[1-6]|pre|blockquote|li|ul)/i', $para)) {
                $para = '<p class="text-gray-700 dark:text-gray-300 leading-relaxed my-4 text-base md:text-lg">' . nl2br($para) . '</p>';
            }
            $output[] = $para;
        }

        return implode("\n", $output);
    }
}
