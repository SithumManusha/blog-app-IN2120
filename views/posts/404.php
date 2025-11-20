<?php
$pageTitle = "404 Not Found | " . ($appName ?? 'Pulse AI Blog');
require __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-xl mx-auto px-4 py-24 text-center">
    <div class="w-20 h-20 mx-auto mb-6 rounded-3xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 flex items-center justify-center text-3xl font-black">
        404
    </div>
    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-3">Article Not Found</h1>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
        The article you are looking for might have been removed, had its name changed, or is temporarily unavailable.
    </p>
    <a href="?route=home" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm shadow-md transition-all">
        <i class="fa-solid fa-house"></i> Return to Home
    </a>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
