<?php
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Markdown;

$pageTitle = htmlspecialchars($post['title']) . " | " . ($appName ?? 'Pulse AI Blog');
require __DIR__ . '/../layouts/header.php';
$currentUser = Auth::user();
$isAuthorOrAdmin = $currentUser && ($currentUser['id'] === $post['user_id'] || $currentUser['role'] === 'admin');
?>

<article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Back Button & Breadcrumb -->
    <div class="mb-6">
        <a href="?route=home" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Back to all articles
        </a>
    </div>

    <!-- Article Header -->
    <header class="mb-8">
        
        <!-- Category & Reading Time -->
        <div class="flex items-center gap-3 mb-4">
            <?php if (!empty($post['category_name'])): ?>
                <a href="?route=home&category=<?= urlencode($post['category_slug']) ?>" 
                    class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-brand-50 dark:bg-brand-950 text-brand-600 dark:text-brand-400">
                    <?= htmlspecialchars($post['category_name']) ?>
                </a>
            <?php endif; ?>
            <span class="text-xs text-gray-400">•</span>
            <span class="text-xs text-gray-500 font-medium"><i class="fa-regular fa-clock mr-1"></i><?= (int)$post['reading_time'] ?> min read</span>
            <span class="text-xs text-gray-400">•</span>
            <span class="text-xs text-gray-500 font-medium"><i class="fa-regular fa-eye mr-1"></i><?= (int)$post['views'] ?> views</span>
        </div>

        <!-- Post Title -->
        <h1 class="text-3xl sm:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight mb-6">
            <?= htmlspecialchars($post['title']) ?>
        </h1>

        <!-- Author Bar & Actions -->
        <div class="flex flex-wrap items-center justify-between gap-4 py-4 border-y border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <img src="<?= htmlspecialchars($post['author_avatar']) ?>" alt="Author" class="w-11 h-11 rounded-full object-cover">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($post['author_name']) ?></h3>
                    <p class="text-xs text-gray-500"><?= date('F d, Y', strtotime($post['created_at'])) ?></p>
                </div>
            </div>

            <!-- Author Controls (Edit / Delete) -->
            <?php if ($isAuthorOrAdmin): ?>
                <div class="flex items-center gap-2">
                    <a href="?route=posts.edit&id=<?= (int)$post['id'] ?>" 
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 transition-colors flex items-center gap-1.5">
                        <i class="fa-regular fa-pen-to-square"></i> Edit
                    </a>
                    <form action="?route=posts.delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this article?');">
                        <?= Csrf::getField() ?>
                        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                        <button type="submit" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 hover:bg-red-100 transition-colors flex items-center gap-1.5">
                            <i class="fa-regular fa-trash-can"></i> Delete
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Cover Image -->
    <div class="aspect-video w-full rounded-2xl overflow-hidden mb-10 shadow-lg bg-gray-100 dark:bg-gray-800">
        <img src="<?= htmlspecialchars($post['cover_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-full object-cover">
    </div>

    <!-- ✨ AI Executive Summary Section -->
    <div id="ai-summary-container" class="mb-10 p-6 rounded-2xl bg-gradient-to-br from-indigo-50/80 via-purple-50/50 to-pink-50/30 dark:from-indigo-950/40 dark:via-purple-950/20 dark:to-gray-900 border border-indigo-100 dark:border-indigo-900/50 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-gradient-to-tr from-brand-600 to-purple-600 text-white flex items-center justify-center text-xs shadow-sm">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </span>
                <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-200">AI Key Insights</h3>
            </div>
            
            <button id="btn-generate-summary" 
                data-post-id="<?= (int)$post['id'] ?>"
                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 hover:bg-brand-50 border border-brand-200 dark:border-gray-700 shadow-sm transition-all">
                <i class="fa-solid fa-rotate text-[10px]"></i> <?= empty($post['ai_summary']) ? 'Generate Summary' : 'Refresh Summary' ?>
            </button>
        </div>

        <div id="summary-content" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed font-sans">
            <?php if (!empty($post['ai_summary'])): ?>
                <?= nl2br(htmlspecialchars($post['ai_summary'])) ?>
            <?php else: ?>
                <p class="text-gray-500 italic">No summary generated yet. Click above to analyze this article with Gemini AI in seconds.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 🎧 Text-to-Speech Audio Player Bar -->
    <div class="mb-10 p-4 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <button id="btn-tts-play" class="w-10 h-10 rounded-full bg-brand-600 text-white flex items-center justify-center hover:bg-brand-500 transition-transform active:scale-95 shadow-md">
                <i id="tts-icon" class="fa-solid fa-play text-sm ml-0.5"></i>
            </button>
            <div>
                <p class="text-xs font-bold text-gray-900 dark:text-white">Listen to Article (AI Voice)</p>
                <p id="tts-status" class="text-[11px] text-gray-500">Click play to listen to this post</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">Speed:</span>
            <select id="tts-speed" class="text-xs rounded-lg bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 py-1 px-2 text-gray-700 dark:text-gray-300">
                <option value="1">1.0x</option>
                <option value="1.25" selected>1.25x</option>
                <option value="1.5">1.5x</option>
            </select>
        </div>
    </div>

    <!-- Article Body (Parsed Markdown) -->
    <div id="article-body" class="prose prose-lg dark:prose-invert max-w-none mb-12">
        <?= Markdown::toHtml($post['content']) ?>
    </div>

    <!-- AI Tags -->
    <?php if (!empty($post['ai_tags'])): ?>
        <div class="py-6 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-2"><i class="fa-solid fa-tags mr-1"></i> Topics:</span>
            <?php foreach (explode(',', $post['ai_tags']) as $tag): ?>
                <span class="text-xs font-medium px-3 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    #<?= htmlspecialchars(trim($tag)) ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Author Biography Box -->
    <div class="my-10 p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 flex items-start gap-4">
        <img src="<?= htmlspecialchars($post['author_avatar']) ?>" alt="Author" class="w-14 h-14 rounded-full object-cover">
        <div>
            <h4 class="text-base font-bold text-gray-900 dark:text-white mb-1">Written by <?= htmlspecialchars($post['author_name']) ?></h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed"><?= htmlspecialchars($post['author_bio'] ?? 'Full-stack software architect & tech explorer.') ?></p>
        </div>
    </div>

    <!-- Comments Section -->
    <section id="comments" class="pt-10 border-t border-gray-200 dark:border-gray-800">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fa-regular fa-comments text-brand-600"></i> Discussion (<?= count($comments) ?>)
            </h3>
        </div>

        <!-- Add Comment Form -->
        <?php if ($currentUser): ?>
            <form action="?route=comments.store" method="POST" class="mb-8">
                <?= Csrf::getField() ?>
                <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                <div class="flex gap-3">
                    <img src="<?= htmlspecialchars($currentUser['avatar']) ?>" alt="User" class="w-9 h-9 rounded-full object-cover">
                    <div class="flex-1">
                        <textarea name="content" rows="3" required
                            placeholder="Share your thoughts or ask a question..." 
                            class="w-full rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-3 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent text-gray-900 dark:text-white resize-none"></textarea>
                        <div class="mt-2 flex justify-end">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold text-xs shadow-sm transition-all">
                                Post Comment
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="p-4 rounded-xl bg-gray-100 dark:bg-gray-800/60 text-center mb-8">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <a href="?route=login" class="font-bold text-brand-600 dark:text-brand-400 hover:underline">Sign in</a> to join the discussion.
                </p>
            </div>
        <?php endif; ?>

        <!-- Comments List -->
        <div class="space-y-4">
            <?php foreach ($comments as $comm): ?>
                <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800/80 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <img src="<?= htmlspecialchars($comm['avatar']) ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($comm['username']) ?></span>
                                <span class="text-[10px] text-gray-400"><?= date('M d, H:i', strtotime($comm['created_at'])) ?></span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed"><?= nl2br(htmlspecialchars($comm['content'])) ?></p>
                        </div>
                    </div>

                    <?php if ($currentUser && ($currentUser['id'] === $comm['user_id'] || $currentUser['role'] === 'admin')): ?>
                        <form action="?route=comments.delete" method="POST" onsubmit="return confirm('Delete this comment?');">
                            <?= Csrf::getField() ?>
                            <input type="hidden" name="comment_id" value="<?= (int)$comm['id'] ?>">
                            <input type="hidden" name="slug" value="<?= htmlspecialchars($post['slug']) ?>">
                            <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors p-1" title="Delete comment">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</article>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
