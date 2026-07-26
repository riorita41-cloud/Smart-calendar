class CustomModal {

    static confirm(message, title = 'Подтверждение') {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            
            overlay.innerHTML = `
                <div class="modal-content">
                    <div class="modal-icon warning">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div class="modal-title">${title}</div>
                    <div class="modal-message">${message}</div>
                    <div class="modal-buttons">
                        <button class="modal-btn modal-btn-secondary" id="modal-cancel">Отмена</button>
                        <button class="modal-btn modal-btn-danger" id="modal-confirm">Удалить</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            const cancelBtn = overlay.querySelector('#modal-cancel');
            const confirmBtn = overlay.querySelector('#modal-confirm');
            
            const closeModal = (result) => {
                overlay.style.animation = 'fadeIn 0.2s ease reverse';
                setTimeout(() => {
                    overlay.remove();
                    resolve(result);
                }, 200);
            };
            
            cancelBtn.addEventListener('click', () => closeModal(false));
            confirmBtn.addEventListener('click', () => closeModal(true));
            
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeModal(false);
            });
            
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    closeModal(false);
                    document.removeEventListener('keydown', handleEscape);
                }
            };
            document.addEventListener('keydown', handleEscape);
        });
    }
    
    static alert(message, title = 'Информация') {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            
            overlay.innerHTML = `
                <div class="modal-content">
                    <div class="modal-icon info">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                    </div>
                    <div class="modal-title">${title}</div>
                    <div class="modal-message">${message}</div>
                    <div class="modal-buttons">
                        <button class="modal-btn modal-btn-primary" id="modal-ok">OK</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            const okBtn = overlay.querySelector('#modal-ok');
            
            const closeModal = () => {
                overlay.style.animation = 'fadeIn 0.2s ease reverse';
                setTimeout(() => {
                    overlay.remove();
                    resolve();
                }, 200);
            };
            
            okBtn.addEventListener('click', closeModal);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeModal();
            });
            
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    closeModal();
                    document.removeEventListener('keydown', handleEscape);
                }
            };
            document.addEventListener('keydown', handleEscape);
        });
    }
}

window.CustomModal = CustomModal;