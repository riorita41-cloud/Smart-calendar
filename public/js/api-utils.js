function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function apiFetch(url, options = {}) {
    const token = getCsrfToken();
    
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    const finalOptions = {
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...(options.headers || {})
        }
    };

    try {
        const response = await fetch(url, finalOptions);
        return await response.json();
    } catch (error) {
        console.error('API Fetch Error:', error);
        alert('Ошибка соединения с сервером');
        return null;
    }
}

function formatTime(seconds) {
    const mins = Math.max(0, Math.floor(seconds / 60));
    const secs = Math.max(0, seconds % 60);
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}