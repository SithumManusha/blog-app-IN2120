<?php
namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Models\Post;
use App\Models\Comment;

class PostController {
    public function index(): void {
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $search   = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');

        $data       = Post::getAll($page, 6, $search, $category);
        $categories = Post::getAllCategories();

        $posts       = $data['posts'];
        $totalPages  = $data['pages'];
        $currentPage = $data['current_page'];
        $totalPosts  = $data['total'];

        require __DIR__ . '/../../views/posts/index.php';
    }

    public function show(): void {
        $slug = trim($_GET['slug'] ?? '');
        if (empty($slug)) {
            header('Location: ?route=home');
            exit;
        }

        $post = Post::getBySlug($slug);
        if (!$post) {
            http_response_code(404);
            require __DIR__ . '/../../views/posts/404.php';
            return;
        }

        // Increment views count
        Post::incrementViews($post['id']);
        $comments = Comment::getByPostId($post['id']);

        require __DIR__ . '/../../views/posts/show.php';
    }

    public function create(): void {
        Auth::requireAuth();
        $categories = Post::getAllCategories();
        require __DIR__ . '/../../views/posts/create.php';
    }

    public function store(): void {
        Auth::requireAuth();

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            header('Location: ?route=posts.create&error=csrf');
            exit;
        }

        $title       = trim($_POST['title'] ?? '');
        $content     = trim($_POST['content'] ?? '');
        $categoryId  = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $coverImage  = trim($_POST['cover_image'] ?? '');
        $aiSummary   = trim($_POST['ai_summary'] ?? '');
        $aiTags      = trim($_POST['ai_tags'] ?? '');
        $status      = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';

        if (empty($title) || empty($content)) {
            header('Location: ?route=posts.create&error=empty_fields');
            exit;
        }

        $postId = Post::create([
            'user_id'     => Auth::id(),
            'category_id' => $categoryId,
            'title'       => $title,
            'content'     => $content,
            'cover_image' => $coverImage,
            'ai_summary'  => $aiSummary,
            'ai_tags'     => $aiTags,
            'status'      => $status
        ]);

        $post = Post::getById($postId);
        header('Location: ?route=posts.show&slug=' . urlencode($post['slug']) . '&msg=created');
        exit;
    }

    public function edit(): void {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $post = Post::getById($id);

        if (!$post) {
            header('Location: ?route=home');
            exit;
        }

        // Authorization check: only author or admin can edit
        $currentUser = Auth::user();
        if ($post['user_id'] !== $currentUser['id'] && $currentUser['role'] !== 'admin') {
            http_response_code(403);
            die("Unauthorized: You do not have permission to edit this post.");
        }

        $categories = Post::getAllCategories();
        require __DIR__ . '/../../views/posts/edit.php';
    }

    public function update(): void {
        Auth::requireAuth();

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            header('Location: ?route=home&error=csrf');
            exit;
        }

        $id   = (int)($_POST['id'] ?? 0);
        $post = Post::getById($id);

        if (!$post) {
            header('Location: ?route=home');
            exit;
        }

        $currentUser = Auth::user();
        if ($post['user_id'] !== $currentUser['id'] && $currentUser['role'] !== 'admin') {
            http_response_code(403);
            die("Unauthorized action.");
        }

        $title      = trim($_POST['title'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $coverImage = trim($_POST['cover_image'] ?? '');
        $aiSummary  = trim($_POST['ai_summary'] ?? '');
        $aiTags     = trim($_POST['ai_tags'] ?? '');
        $status     = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';

        Post::update($id, [
            'category_id' => $categoryId,
            'title'       => $title,
            'content'     => $content,
            'cover_image' => $coverImage,
            'ai_summary'  => $aiSummary,
            'ai_tags'     => $aiTags,
            'status'      => $status
        ]);

        header('Location: ?route=posts.show&slug=' . urlencode($post['slug']) . '&msg=updated');
        exit;
    }

    public function delete(): void {
        Auth::requireAuth();

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            header('Location: ?route=home&error=csrf');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $currentUser = Auth::user();
        $isAdmin = ($currentUser['role'] === 'admin');

        Post::delete($id, $currentUser['id'], $isAdmin);
        header('Location: ?route=home&msg=deleted');
        exit;
    }

    public function like(): void {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
            exit;
        }

        $newLikes = Post::incrementLikes($id);
        echo json_encode(['success' => true, 'likes' => $newLikes]);
        exit;
    }
}
