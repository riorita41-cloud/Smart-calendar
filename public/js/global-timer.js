document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('global-timer-widget');
    const widgetTime = document.getElementById('global-timer-time');
    
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content') || 'anonymous';
    const TIMER_STORAGE_KEY = 'pomodoro_timer_state_' + userId;
    
    let widgetInterval = null;

    function updateGlobalWidget() {
        const savedData = localStorage.getItem(TIMER_STORAGE_KEY);
        if (!savedData) {
            if (widget) widget.style.display = 'none';
            if (widgetInterval) clearInterval(widgetInterval);
            return;
        }

        const data = JSON.parse(savedData);
        const now = Date.now();
        const remainingMs = data.endTime - now;
        const timeLeft = Math.ceil(remainingMs / 1000);

        const currentPath = window.location.pathname;
        const isHomePage = currentPath === '/home' || currentPath === '/';

        if (timeLeft > 0 && data.isRunning && !isHomePage) {
            if (widget) widget.style.display = 'flex';
            if (widgetTime) {
                widgetTime.textContent = formatTime(timeLeft);
            }
        } else {
            if (widget) widget.style.display = 'none';
            if (widgetInterval) clearInterval(widgetInterval);
        }
    }

    if (widget && widgetTime) {
        updateGlobalWidget();
        widgetInterval = setInterval(updateGlobalWidget, 1000);
        
        widget.addEventListener('click', (e) => {
            if (e.target.tagName !== 'A' && !e.target.closest('a')) {
                window.location.href = '/home';
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