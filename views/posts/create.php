<?php
use App\Helpers\Csrf;

$pageTitle = "Write New Post | " . ($appName ?? 'Pulse AI Blog');
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
        min-height: 380px;
        background: #ffffff;
    }
    .dark .ql-container.ql-snow {
        border-color: #374151;
        background: #030712;
        color: #f3f4f6;
    }
    .ql-editor {
        min-height: 380px;
        line-height: 1.75;
    }
    .ql-editor.ql-blank::before {
        color: #9ca3af;
        font-style: normal;
    }
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-brand-600"></i> Compose Article
            </h1>
            <p class="text-sm text-gray-500 mt-1">Full manual Word-style formatting with optional AI assistance.</p>
        </div>
        <a href="?route=home" class="text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
            Cancel
        </a>
    </div>

    <!-- Main Creation Form -->
    <form action="?route=posts.store" method="POST" id="post-form">
        <?= Csrf::getField() ?>
        <!-- Hidden input to store Quill HTML content for form submission -->
        <input type="hidden" name="content" id="post-content">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Cols: Main Editor -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Title & AI Suggestion Button -->
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
                        placeholder="e.g. The Complete Guide to Modern Web Development in 2026" 
                        class="w-full text-lg sm:text-xl font-bold rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 focus:ring-2 focus:ring-brand-500 focus:border-transparent text-gray-900 dark:text-white shadow-sm">
                </div>

                <!-- Optional AI Assistant Drawer / Bar -->
                <div class="p-3 rounded-xl bg-gradient-to-r from-brand-50/70 via-purple-50/40 to-indigo-50/70 dark:from-brand-950/30 dark:via-purple-950/20 dark:to-indigo-950/30 border border-brand-100/80 dark:border-brand-900/30 flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-sparkles text-brand-600"></i> AI Tools (Optional):
                    </span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" id="btn-ai-draft" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 text-brand-700 dark:text-brand-300 hover:bg-brand-50 border border-brand-200 dark:border-gray-700 shadow-sm flex items-center gap-1 transition-all">
                            <i class="fa-solid fa-bolt text-[10px]"></i> Auto-Draft from Title
                        </button>
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
                        <span class="text-[11px] text-gray-400">Use the toolbar below for Bold, Headings, Images & Videos</span>
                    </div>

                    <!-- Quill Editor Toolbar + Body -->
                    <div id="editor-container" class="shadow-sm">
                        <!-- Custom Word-Style Toolbar -->
                        <div id="toolbar-container">
                            <!-- Font Sizes & Headings -->
                            <span class="ql-formats">
                                <select class="ql-header">
                                    <option selected>Normal Text</option>
                                    <option value="1">Heading 1</option>
                                    <option value="2">Heading 2</option>
                                    <option value="3">Heading 3</option>
                                </select>
                            </span>

                            <!-- Text Formatting (Word-like Bold, Italic, Underline, Strikethrough) -->
                            <span class="ql-formats">
                                <button class="ql-bold" title="Bold (Ctrl+B)"></button>
                                <button class="ql-italic" title="Italic (Ctrl+I)"></button>
                                <button class="ql-underline" title="Underline (Ctrl+U)"></button>
                                <button class="ql-strike" title="Strikethrough"></button>
                            </span>

                            <!-- Colors & Highlights -->
                            <span class="ql-formats">
                                <select class="ql-color" title="Text Color"></select>
                                <select class="ql-background" title="Highlight Color"></select>
                            </span>

                            <!-- Lists & Indent -->
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered" title="Numbered List"></button>
                                <button class="ql-list" value="bullet" title="Bullet List"></button>
                                <button class="ql-indent" value="-1" title="Outdent"></button>
                                <button class="ql-indent" value="+1" title="Indent"></button>
                            </span>

                            <!-- Alignment -->
                            <span class="ql-formats">
                                <select class="ql-align" title="Text Alignment"></select>
                            </span>

                            <!-- Blockquote & Code Block -->
                            <span class="ql-formats">
                                <button class="ql-blockquote" title="Quote"></button>
                                <button class="ql-code-block" title="Code Block"></button>
                            </span>

                            <!-- Rich Media Embeds: Links, Images, Videos (YouTube) -->
                            <span class="ql-formats">
                                <button class="ql-link" title="Insert Link"></button>
                                <button class="ql-image" title="Insert Image"></button>
                                <button class="ql-video" title="Embed YouTube Video"></button>
                            </span>

                            <!-- Clear formatting -->
                            <span class="ql-formats">
                                <button class="ql-clean" title="Clear Formatting"></button>
                            </span>
                        </div>

                        <!-- Editor Content Area -->
                        <div id="quill-editor">
                            <p>Start writing your story here... You can format bold letters, change font sizes, bullet points, and add pictures or YouTube videos using the toolbar above.</p>
                        </div>
                    </div>

                    <!-- Word & Character Counter -->
                    <div class="flex items-center justify-between text-xs text-gray-400 mt-2 px-1">
                        <span id="char-count">0 characters</span>
                        <span id="word-count">0 words</span>
                    </div>
                </div>

            </div>

            <!-- Right Column: Settings & Metadata -->
            <div class="space-y-6">
                
                <!-- Category Select -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                        Category
                    </label>
                    <select name="category_id" class="w-full rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-sm text-gray-900 dark:text-white p-2.5">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Cover Image URL -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                        Cover Banner Image
                    </label>
                    <input type="url" name="cover_image" id="post-cover" 
                        value="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80"
                        placeholder="https://..." 
                        class="w-full text-xs rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 p-2.5 text-gray-900 dark:text-white mb-2">
                    <p class="text-[11px] text-gray-400">Header cover banner image URL</p>
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
                        placeholder="WebDev, Design, Tutorials" 
                        class="w-full text-xs rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 p-2.5 text-gray-900 dark:text-white">
                    <p class="text-[11px] text-gray-400 mt-1">Comma-separated keywords</p>
                </div>

                <!-- Summary (Optional) -->
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
                        placeholder="Bullet points summarizing the article..." 
                        class="w-full text-xs rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 p-2.5 text-gray-900 dark:text-white"></textarea>
                </div>

                <!-- Publish / Draft Actions -->
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-3">
                    <button type="submit" name="status" value="published" 
                        class="w-full py-3 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-md shadow-brand-500/25 transition-all">
                        <i class="fa-solid fa-paper-plane mr-1.5"></i> Publish Article
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
    // Initialize Quill Editor with full toolbar
    const quill = new Quill('#quill-editor', {
        modules: {
            toolbar: '#toolbar-container'
        },
        placeholder: 'Write your story here... format bold, add headings, embed images & YouTube videos.',
        theme: 'snow'
    });

    const hiddenContentInput = document.getElementById('post-content');
    const form = document.getElementById('post-form');
    const wordCountSpan = document.getElementById('word-count');
    const charCountSpan = document.getElementById('char-count');

    // Update word & character counters
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

    // Sync Quill HTML content to hidden input before form submit
    form.addEventListener('submit', () => {
        hiddenContentInput.value = quill.root.innerHTML;
    });

    // Make Quill instance accessible to ai-copilot.js
    window.quillEditor = quill;
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
