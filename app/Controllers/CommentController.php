<?php
namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Models\Comment;
use App\Models\Post;

class CommentController {
    public function store(): void {
        Auth::requireAuth();

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            header('Location: ?route=home&error=csrf');
            exit;
        }

        $postId  = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        $post = Post::getById($postId);
        if (!$post || empty($content)) {
            header('Location: ?route=home');
            exit;
        }

        Comment::create($postId, Auth::id(), $content);
        header('Location: ?route=posts.show&slug=' . urlencode($post['slug']) . '#comments');
        exit;
    }

    public function delete(): void {
        Auth::requireAuth();

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            header('Location: ?route=home&error=csrf');
            exit;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $slug      = trim($_POST['slug'] ?? '');
        $currentUser = Auth::user();
        $isAdmin = ($currentUser['role'] === 'admin');

        Comment::delete($commentId, $currentUser['id'], $isAdmin);
        header('Location: ?route=posts.show&slug=' . urlencode($slug) . '#comments');
        exit;
    }
}
