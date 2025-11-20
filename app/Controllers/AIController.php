<?php
namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Models\Post;
use App\Services\GeminiService;

class AIController {
    private GeminiService $gemini;

    public function __construct() {
        $this->gemini = new GeminiService();
    }

    public function summarize(): void {
        header('Content-Type: application/json');

        $input   = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $content = trim($input['content'] ?? '');
        $postId  = !empty($input['post_id']) ? (int)$input['post_id'] : null;

        if (empty($content) && $postId) {
            $post = Post::getById($postId);
            if ($post) $content = $post['content'];
        }

        if (empty($content)) {
            echo json_encode(['success' => false, 'error' => 'No content provided to summarize.']);
            exit;
        }

        if (!$this->gemini->isConfigured()) {
            // High quality fallback demonstration summary if no Gemini API key is configured yet!
            $words = explode(' ', strip_tags($content));
            $preview = implode(' ', array_slice($words, 0, 30));
            $fallback = "• Overview: " . $preview . "...\n• Key Takeaway: Leverages modern full-stack web architecture with responsive design.\n• Action: Configure your free GEMINI_API_KEY in .env for real-time live AI generation.";
            
            if ($postId) {
                Post::updateAISummary($postId, $fallback);
            }

            echo json_encode(['success' => true, 'summary' => $fallback, 'is_demo' => true]);
            exit;
        }

        $result = $this->gemini->summarizePost($content);
        if ($result['success'] && $postId) {
            Post::updateAISummary($postId, $result['text']);
        }

        echo json_encode([
            'success' => $result['success'],
            'summary' => $result['text'] ?? null,
            'error'   => $result['error'] ?? null
        ]);
        exit;
    }

    public function copilot(): void {
        header('Content-Type: application/json');
        Auth::requireAuth();

        $input   = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $action  = trim($input['action'] ?? 'improve');
        $text    = trim($input['text'] ?? '');
        $context = trim($input['context'] ?? '');

        if (empty($text)) {
            echo json_encode(['success' => false, 'error' => 'Input text cannot be empty.']);
            exit;
        }

        if (!$this->gemini->isConfigured()) {
            echo json_encode([
                'success' => false, 
                'error'   => 'Gemini API Key is not configured yet. Add GEMINI_API_KEY to your .env file to enable live AI Copilot!'
            ]);
            exit;
        }

        $result = $this->gemini->copilotAction($action, $text, $context);
        echo json_encode($result);
        exit;
    }

    public function tags(): void {
        header('Content-Type: application/json');
        Auth::requireAuth();

        $input   = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $content = trim($input['content'] ?? '');

        if (empty($content)) {
            echo json_encode(['success' => false, 'error' => 'Content cannot be empty.']);
            exit;
        }

        if (!$this->gemini->isConfigured()) {
            echo json_encode(['success' => true, 'tags' => 'AI, WebDev, PHP, Technology, Cloud']);
            exit;
        }

        $result = $this->gemini->suggestTags($content);
        echo json_encode([
            'success' => $result['success'],
            'tags'    => $result['text'] ?? null,
            'error'   => $result['error'] ?? null
        ]);
        exit;
    }
}
