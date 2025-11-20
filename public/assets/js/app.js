/**
 * Pulse AI Blog Platform - Core Application JS
 * Dark mode, Toast notifications, and Text-to-Speech (TTS)
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initTextToSpeech();
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

    // Animate in
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-10', 'opacity-0');
    });

    // Auto remove after 4.5 seconds
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

        window.speechSynthesis.cancel(); // Reset any existing speech
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
                // Restart with new speed
                playBtn.click();
                playBtn.click();
            }
        });
    }
}
