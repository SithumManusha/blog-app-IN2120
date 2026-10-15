/**
 * Pulse AI Blog Platform - Core Application JS
 * Dark mode, Toast notifications, Text-to-Speech (TTS),
 * Reading Progress Bar, Interactive Likes & Code Block Copy
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initTextToSpeech();
    initReadingProgress();
    initPostLikes();
    initCodeBlockCopy();
});

// 1. Theme Toggle (Dark / Light Mode)
function initThemeToggle() {
    const toggleBtn = document.getElementById('theme-toggle');
    if (!toggleBtn) return;

    toggleBtn.addEventListener('click', () => {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    });
}

// 2. Global Toast Notification Function
window.showToast = function(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const bgColors = {
        'success': 'bg-emerald-600 text-white',
        'error': 'bg-rose-600 text-white',
        'info': 'bg-indigo-600 text-white'
    };
    const icons = {
        'success': 'fa-solid fa-circle-check',
        'error': 'fa-solid fa-circle-exclamation',
        'info': 'fa-solid fa-circle-info'
    };

    toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-xs font-semibold ${bgColors[type] || bgColors.info} transition-all duration-300 transform translate-x-10 opacity-0`;
    toast.innerHTML = `<i class="${icons[type] || icons.info} text-sm"></i><span>${message}</span>`;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-10', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-x-5');
        setTimeout(() => toast.remove(), 300);
    }, 4500);
};

// 3. Text-to-Speech (TTS) Web Speech API Engine
function initTextToSpeech() {
    const playBtn = document.getElementById('btn-tts-play');
    const speedSelect = document.getElementById('tts-speed');
    const statusText = document.getElementById('tts-status');
    const icon = document.getElementById('tts-icon');
    const articleBody = document.getElementById('article-body');

    if (!playBtn || !articleBody || !('speechSynthesis' in window)) {
        if (playBtn) playBtn.parentElement.parentElement.style.display = 'none';
        return;
    }

    let isPlaying = false;
    let utterance = null;

    playBtn.addEventListener('click', () => {
        if (isPlaying) {
            window.speechSynthesis.cancel();
            isPlaying = false;
            icon.className = 'fa-solid fa-play text-sm ml-0.5';
            statusText.textContent = 'Paused. Click to resume.';
            return;
        }

        window.speechSynthesis.cancel();
        const textToRead = articleBody.innerText;
        if (!textToRead.trim()) return;

        utterance = new SpeechSynthesisUtterance(textToRead);
        utterance.rate = parseFloat(speedSelect.value) || 1.25;

        utterance.onstart = () => {
            isPlaying = true;
            icon.className = 'fa-solid fa-pause text-sm';
            statusText.textContent = 'Playing audio article...';
        };

        utterance.onend = () => {
            isPlaying = false;
            icon.className = 'fa-solid fa-play text-sm ml-0.5';
            statusText.textContent = 'Finished reading.';
        };

        utterance.onerror = () => {
            isPlaying = false;
            icon.className = 'fa-solid fa-play text-sm ml-0.5';
            statusText.textContent = 'Speech synthesis interrupted.';
        };

        window.speechSynthesis.speak(utterance);
    });

    if (speedSelect) {
        speedSelect.addEventListener('change', () => {
            if (isPlaying) {
                playBtn.click();
                playBtn.click();
            }
        });
    }
}

// 4. Reading Progress Bar (Medium-style top indicator)
function initReadingProgress() {
    const progressBar = document.getElementById('reading-progress-bar');
    if (!progressBar) return;

    window.addEventListener('scroll', () => {
        const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
        progressBar.style.width = Math.min(100, Math.max(0, scrolled)) + '%';
    });
}

// 5. 1-Click Interactive Like / Heart Button
function initPostLikes() {
    const likeBtn = document.getElementById('btn-like-post');
    const likeCountSpan = document.getElementById('like-count');
    const likeIcon = document.getElementById('like-icon');

    if (!likeBtn || !likeCountSpan) return;

    likeBtn.addEventListener('click', async () => {
        const postId = likeBtn.getAttribute('data-post-id');
        if (!postId) return;

        // Visual pop effect
        if (likeIcon) {
            likeIcon.classList.add('scale-150', 'text-rose-600');
            setTimeout(() => likeIcon.classList.remove('scale-150'), 250);
        }

        try {
            const res = await fetch('?route=api.posts.like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: postId })
            });
            const data = await res.json();
            if (data.success && data.likes !== undefined) {
                likeCountSpan.textContent = data.likes;
                showToast('Thanks for liking this article!', 'success');
            }
        } catch (err) {
            // Optimistic fallback increment
            const current = parseInt(likeCountSpan.textContent) || 0;
            likeCountSpan.textContent = current + 1;
        }
    });
}

// 6. Code Block Copy Button Auto-Injector
function initCodeBlockCopy() {
    const codeBlocks = document.querySelectorAll('pre');
    codeBlocks.forEach((pre) => {
        if (pre.querySelector('.btn-copy-code')) return;

        pre.classList.add('relative', 'group');
        const copyBtn = document.createElement('button');
        copyBtn.className = 'btn-copy-code absolute top-3 right-3 px-2 py-1 text-[11px] font-mono rounded bg-gray-800 text-gray-300 hover:bg-gray-700 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity border border-gray-700 flex items-center gap-1';
        copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i><span>Copy</span>';

        copyBtn.addEventListener('click', () => {
            const code = pre.querySelector('code') ? pre.querySelector('code').innerText : pre.innerText;
            navigator.clipboard.writeText(code).then(() => {
                copyBtn.innerHTML = '<i class="fa-solid fa-check text-emerald-400"></i><span class="text-emerald-400">Copied!</span>';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i><span>Copy</span>';
                }, 2000);
            });
        });

        pre.appendChild(copyBtn);
    });
}
