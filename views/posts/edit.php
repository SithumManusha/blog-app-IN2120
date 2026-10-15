<?php
use App\Helpers\Csrf;

$pageTitle = "Edit Post: " . htmlspecialchars($post['title']) . " | " . ($appName ?? 'Pulse AI Blog');
require __DIR__ . '/../layouts/header.php';
?>

<!-- Quill.js Rich Text Editor (MS Word-like Toolbar) -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        border-color: #e5e7eb;
        background: #f9fafb;
    }
    .dark .ql-toolbar.ql-snow {
        border-color: #374151;
        background: #111827;
    }
    .dark .ql-snow .ql-stroke { stroke: #9ca3af; }
    .dark .ql-snow .ql-fill { fill: #9ca3af; }
    .dark .ql-snow .ql-picker { color: #9ca3af; }
    .dark .ql-snow .ql-picker-options { background-color: #1f2937; border-color: #374151; }
    
    .ql-container.ql-snow {
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
        border-color: #e5e7eb;
        font-family: inherit;
        font-size: 1rem;
        min-height: 400px;
        background: #ffffff;
    }
    .dark .ql-container.ql-snow {
        border-color: #374151;
        background: #030712;
        color: #f3f4f6;
    }
    .ql-editor {
        min-height: 400px;
        line-height: 1.75;
    }
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-brand-600"></i> Edit Article
            </h1>
            <p class="text-sm text-gray-500 mt-1">Full manual Word-style formatting with optional AI assistance.</p>
        </div>
        <a href="?route=posts.show&slug=<?= urlencode($post['slug']) ?>" class="text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
            View Article
        </a>
    </div>

    <!-- Edit Form -->
    <form action="?route=posts.update" method="POST" id="post-form">
        <?= Csrf::getField() ?>
        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
        <!-- Hidden input to store Quill HTML content for form submission -->
        <input type="hidden" name="content" id="post-content">

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
                            <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> AI Title Ideas (Optional)
                        </button>
                    </div>
                    <input type="text" name="title" id="post-title" required 
                        value="<?= htmlspecialchars($post['title']) ?>"
                        class="w-full text-lg sm:text-xl font-bold rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 focus:ring-2 focus:ring-brand-500 focus:border-transparent text-gray-900 dark:text-white shadow-sm">
                </div>

                <!-- Optional AI Assistant Drawer -->
                <div class="p-3 rounded-xl bg-gradient-to-r from-brand-50/70 via-purple-50/40 to-indigo-50/70 dark:from-brand-950/30 dark:via-purple-950/20 dark:to-indigo-950/30 border border-brand-100/80 dark:border-brand-900/30 flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-sparkles text-brand-600"></i> AI Tools (Optional):
                    </span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" id="btn-ai-fix-grammar" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-1 transition-all">
                            <i class="fa-solid fa-spell-check text-[10px]"></i> Fix Grammar & Tone
                        </button>
                        <button type="button" id="btn-ai-expand" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-1 transition-all">
                            <i class="fa-solid fa-arrows-maximize text-[10px]"></i> Expand Text
                        </button>
                    </div>
                </div>

                <!-- Word-Style WYSIWYG Editor Container -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Article Body (Rich Editor) *
                        </label>
                        <span class="text-[11px] text-gray-400">Use toolbar for Bold, Headings, Images & Videos</span>
                    </div>

                    <!-- Quill Editor Toolbar + Body -->
                    <div id="editor-container" class="shadow-sm">
                        <!-- Custom Word-Style Toolbar -->
                        <div id="toolbar-container">
                            <span class="ql-formats">
                                <select class="ql-header">
                                    <option selected>Normal Text</option>
                                    <option value="1">Heading 1</option>
                                    <option value="2">Heading 2</option>
                                    <option value="3">Heading 3</option>
                                </select>
                            </span>

                            <span class="ql-formats">
                                <button class="ql-bold" title="Bold (Ctrl+B)"></button>
                                <button class="ql-italic" title="Italic (Ctrl+I)"></button>
                                <button class="ql-underline" title="Underline (Ctrl+U)"></button>
                                <button class="ql-strike" title="Strikethrough"></button>
                            </span>

                            <span class="ql-formats">
                                <select class="ql-color" title="Text Color"></select>
                                <select class="ql-background" title="Highlight Color"></select>
                            </span>

                            <span class="ql-formats">
                                <button class="ql-list" value="ordered" title="Numbered List"></button>
                                <button class="ql-list" value="bullet" title="Bullet List"></button>
                                <button class="ql-indent" value="-1" title="Outdent"></button>
                                <button class="ql-indent" value="+1" title="Indent"></button>
                            </span>

                            <span class="ql-formats">
                                <select class="ql-align" title="Text Alignment"></select>
                            </span>

                            <span class="ql-formats">
                                <button class="ql-blockquote" title="Quote"></button>
                                <button class="ql-code-block" title="Code Block"></button>
                            </span>

                            <span class="ql-formats">
                                <button class="ql-link" title="Insert Link"></button>
                                <button class="ql-image" title="Insert Image"></button>
                                <button class="ql-video" title="Embed YouTube Video"></button>
                            </span>

                            <span class="ql-formats">
                                <button class="ql-clean" title="Clear Formatting"></button>
                            </span>
                        </div>

                        <!-- Editor Content Area with existing post content -->
                        <div id="quill-editor">
                            <?= $post['content'] ?>
                        </div>
                    </div>

                    <!-- Word & Character Counter -->
                    <div class="flex items-center justify-between text-xs text-gray-400 mt-2 px-1">
                        <span id="char-count">0 characters</span>
                        <span id="word-count">0 words</span>
                    </div>
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
                        Cover Banner Image
                    </label>
                    <input type="url" name="cover_image" id="post-cover" 
                        value="<?= htmlspecialchars($post['cover_image']) ?>"
                        class="w-full text-xs rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 p-2.5 text-gray-900 dark:text-white mb-2">
                </div>

                <!-- Tags -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Tags & Topics
                        </label>
                        <button type="button" id="btn-ai-tags" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-tags text-[10px]"></i> Auto-Tag
                        </button>
                    </div>
                    <input type="text" name="ai_tags" id="post-tags" 
                        value="<?= htmlspecialchars($post['ai_tags'] ?? '') ?>"
                        class="w-full text-xs rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 p-2.5 text-gray-900 dark:text-white">
                </div>

                <!-- Summary -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Executive Summary (Optional)
                        </label>
                        <button type="button" id="btn-ai-pre-summary" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> AI Summary
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

<!-- Quill.js Editor Script -->
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
    const quill = new Quill('#quill-editor', {
        modules: {
            toolbar: '#toolbar-container'
        },
        theme: 'snow'
    });

    const hiddenContentInput = document.getElementById('post-content');
    const form = document.getElementById('post-form');
    const wordCountSpan = document.getElementById('word-count');
    const charCountSpan = document.getElementById('char-count');

    function updateCounters() {
        const text = quill.getText().trim();
        const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
        const chars = text.length;
        if (wordCountSpan) wordCountSpan.textContent = words + ' words';
        if (charCountSpan) charCountSpan.textContent = chars + ' characters';
    }

    quill.on('text-change', () => {
        updateCounters();
    });
    updateCounters();

    form.addEventListener('submit', () => {
        hiddenContentInput.value = quill.root.innerHTML;
    });

    window.quillEditor = quill;
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
