<?php
use App\Helpers\Env;
$appName = Env::get('APP_NAME', 'Pulse AI Blog');
?>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 mt-20 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-600 to-indigo-600 flex items-center justify-center text-white">
                            <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($appName) ?></span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 max-w-sm leading-relaxed mb-4">
                        Next-generation publishing platform with native Gemini AI integration. Write faster, summarize seamlessly, and engage deeper.
                    </p>
                    <div class="flex gap-4 text-gray-400">
                        <a href="https://github.com/SithumManusha" target="_blank" rel="noopener noreferrer" class="hover:text-brand-500 transition-colors"><i class="fa-brands fa-github text-lg"></i></a>
                        <a href="#" class="hover:text-brand-500 transition-colors"><i class="fa-brands fa-twitter text-lg"></i></a>
                        <a href="#" class="hover:text-brand-500 transition-colors"><i class="fa-brands fa-linkedin text-lg"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">AI Features</h4>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li><span class="hover:text-brand-500 cursor-default">✨ 1-Click Executive Summary</span></li>
                        <li><span class="hover:text-brand-500 cursor-default">✍️ Smart Writing Copilot</span></li>
                        <li><span class="hover:text-brand-500 cursor-default">🏷️ Automatic Tag Generation</span></li>
                        <li><span class="hover:text-brand-500 cursor-default">🎧 Text-to-Speech Player</span></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Stack & Performance</h4>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li><span>PHP 8.0+ Clean MVC</span></li>
                        <li><span>MySQL Prepared Statements</span></li>
                        <li><span>Tailwind CSS</span></li>
                        <li><span>Google Gemini API</span></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-4">
                <p>© <?= date('Y') ?> <?= htmlspecialchars($appName) ?>. Developed by <a href="https://github.com/SithumManusha" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">Sithum Manusha</a>.</p>
                <div class="flex gap-6">
                    <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Systems Operational</span>
                    <span>v2.0 AI Edition</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/app.js"></script>
    <script src="assets/js/ai-copilot.js"></script>

    <?php if (isset($_GET['msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const msgs = {
                'welcome': 'Welcome back! You are logged in.',
                'registered': 'Account created successfully! Welcome to <?= htmlspecialchars($appName) ?>.',
                'logged_out': 'You have been safely logged out.',
                'created': 'Article published successfully with AI enhancements!',
                'updated': 'Article updated successfully.',
                'deleted': 'Article deleted.'
            };
            const code = "<?= htmlspecialchars($_GET['msg']) ?>";
            if (msgs[code]) {
                showToast(msgs[code], 'success');
            }
        });
    </script>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const errors = {
                'csrf': 'Security token expired. Please try again.',
                'empty_fields': 'Please fill in all required fields.'
            };
            const code = "<?= htmlspecialchars($_GET['error']) ?>";
            showToast(errors[code] || 'An error occurred.', 'error');
        });
    </script>
    <?php endif; ?>

</body>
</html>
