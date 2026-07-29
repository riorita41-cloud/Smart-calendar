document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('global-timer-widget');
    const widgetTime = document.getElementById('global-timer-time');
    
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content') || 'anonymous';
    const TIMER_STORAGE_KEY = 'pomodoro_timer_state_' + userId;
    
    let widgetInterval = null;

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    function updateGlobalWidget() {
        const savedData = localStorage.getItem(TIMER_STORAGE_KEY);
        
        if (!savedData) {
            if (widget) {
                widget.style.display = 'none';
                widget.classList.remove('is-running');
            }
            if (widgetInterval) clearInterval(widgetInterval);
            return;
        }

        let data;
        try {
            data = JSON.parse(savedData);
        } catch (e) {
            localStorage.removeItem(TIMER_STORAGE_KEY);
            if (widget) {
                widget.style.display = 'none';
                widget.classList.remove('is-running');
            }
            return;
        }

        if (!data.endTime || !data.isRunning) {
            localStorage.removeItem(TIMER_STORAGE_KEY);
            if (widget) {
                widget.style.display = 'none';
                widget.classList.remove('is-running');
            }
            return;
        }

        const now = Date.now();
        const remainingMs = data.endTime - now;
        const timeLeft = Math.ceil(remainingMs / 1000);

        if (timeLeft > 0 && data.isRunning) {
            if (widget) {
                widget.style.display = 'flex';
                widget.classList.add('is-running');
            }
            if (widgetTime) {
                widgetTime.textContent = formatTime(timeLeft);
                widgetTime.style.color = timeLeft <= 60 ? '#DC2626' : '';
            }
        } else {
            localStorage.removeItem(TIMER_STORAGE_KEY);
            if (widget) {
                widget.style.display = 'none';
                widget.classList.remove('is-running');
            }
            if (widgetInterval) clearInterval(widgetInterval);
        }
    }

    if (widget && widgetTime) {
        updateGlobalWidget();
        widgetInterval = setInterval(updateGlobalWidget, 1000);
        
        widget.addEventListener('click', (e) => {
            if (e.target.tagName !== 'A' && !e.target.closest('a')) {
                window.location.href = '/pomodoro';
            }
        });
    }
    
    const logoutLink = document.querySelector('a[href*="logout"]');
    if (logoutLink) {
        logoutLink.addEventListener('click', () => {
            localStorage.removeItem(TIMER_STORAGE_KEY);
        });
    }
});