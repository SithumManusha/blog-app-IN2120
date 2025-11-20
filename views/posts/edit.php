<?php
use App\Helpers\Csrf;

$pageTitle = "Edit Post: " . htmlspecialchars($post['title']) . " | " . ($appName ?? 'Pulse AI Blog');
require __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-brand-600"></i> Edit Article
            </h1>
            <p class="text-sm text-gray-500 mt-1">Refine and enhance your content with Gemini AI tools.</p>
        </div>
        <a href="?route=posts.show&slug=<?= urlencode($post['slug']) ?>" class="text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
            View Article
        </a>
    </div>

    <!-- Edit Form -->
    <form action="?route=posts.update" method="POST" id="post-form">
        <?= Csrf::getField() ?>
        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Cols: Main Editor -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Title -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Article Title *
                        </label>
                        <button type="button" id="btn-ai-catchy-titles" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> AI Title Ideas
                        </button>
                    </div>
                    <input type="text" name="title" id="post-title" required 
                        value="<?= htmlspecialchars($post['title']) ?>"
                        class="w-full text-lg sm:text-xl font-bold rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 focus:ring-2 focus:ring-brand-500 focus:border-transparent text-gray-900 dark:text-white">
                </div>

                <!-- AI Copilot Toolbar -->
                <div class="p-3 rounded-xl bg-gradient-to-r from-brand-50/80 via-purple-50/50 to-indigo-50/80 dark:from-brand-950/40 dark:via-purple-950/20 dark:to-indigo-950/40 border border-brand-100 dark:border-brand-900/40 flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-bold text-brand-900 dark:text-brand-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-robot"></i> Gemini Copilot:
                    </span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" id="btn-ai-fix-grammar" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-1">
                            <i class="fa-solid fa-spell-check text-[10px]"></i> Fix Grammar & Tone
                        </button>
                        <button type="button" id="btn-ai-expand" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-1">
                            <i class="fa-solid fa-arrows-maximize text-[10px]"></i> Expand Draft
                        </button>
                    </div>
                </div>

                <!-- Content Area -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                        Article Content (Markdown Supported) *
                    </label>
                    <textarea name="content" id="post-content" rows="18" required 
                        class="w-full rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 font-mono text-sm leading-relaxed focus:ring-2 focus:ring-brand-500 focus:border-transparent text-gray-900 dark:text-white"><?= htmlspecialchars($post['content']) ?></textarea>
                </div>

            </div>

            <!-- Right Column: Settings -->
            <div class="space-y-6">
                
                <!-- Category -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                        Category
                    </label>
                    <select name="category_id" class="w-full rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-sm text-gray-900 dark:text-white p-2.5">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= $post['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Cover Image -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                        Cover Image URL
                    </label>
                    <input type="url" name="cover_image" id="post-cover" 
                        value="<?= htmlspecialchars($post['cover_image']) ?>"
                        class="w-full text-xs rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 p-2.5 text-gray-900 dark:text-white mb-2">
                </div>

                <!-- AI Tags -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            AI Tags
                        </label>
                        <button type="button" id="btn-ai-tags" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-tags text-[10px]"></i> Auto-Tag
                        </button>
                    </div>
                    <input type="text" name="ai_tags" id="post-tags" 
                        value="<?= htmlspecialchars($post['ai_tags'] ?? '') ?>"
                        class="w-full text-xs rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 p-2.5 text-gray-900 dark:text-white">
                </div>

                <!-- AI Summary -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            AI Executive Summary
                        </label>
                        <button type="button" id="btn-ai-pre-summary" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> Summarize
                        </button>
                    </div>
                    <textarea name="ai_summary" id="post-summary" rows="4" 
                        class="w-full text-xs rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 p-2.5 text-gray-900 dark:text-white"><?= htmlspecialchars($post['ai_summary'] ?? '') ?></textarea>
                </div>

                <!-- Actions -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-3">
                    <button type="submit" name="status" value="published" 
                        class="w-full py-3 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-md shadow-brand-500/25 transition-all">
                        Update Article
                    </button>
                    <button type="submit" name="status" value="draft" 
                        class="w-full py-2.5 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 transition-all">
                        Save as Draft
                    </button>
                </div>

            </div>

        </div>
    </form>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
