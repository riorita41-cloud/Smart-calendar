document.addEventListener('DOMContentLoaded', () => {
    const bulkModeBtn = document.getElementById('bulkModeBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const cancelBulkBtn = document.getElementById('cancelBulkBtn');
    const bulkSelectHeader = document.getElementById('bulkSelectHeader');
    const selectAllCheckbox = document.getElementById('selectAllTasks');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    let bulkMode = false;

    function enterBulkMode() {
        bulkMode = true;
        bulkModeBtn.style.display = 'none';
        cancelBulkBtn.style.display = 'inline-flex';
        bulkSelectHeader.style.display = 'flex';
        
        document.querySelectorAll('.normal-mode').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.bulk-mode-checkbox').forEach(el => el.style.display = 'flex');
        document.querySelectorAll('.task-card').forEach(el => el.classList.add('bulk-mode'));
        
        updateBulkDeleteButton();
    }

    function exitBulkMode() {
        bulkMode = false;
        bulkModeBtn.style.display = 'inline-flex';
        cancelBulkBtn.style.display = 'none';
        bulkDeleteBtn.style.display = 'none';
        bulkSelectHeader.style.display = 'none';
        
        document.querySelectorAll('.normal-mode').forEach(el => el.style.display = '');
        document.querySelectorAll('.bulk-mode-checkbox').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.task-card').forEach(el => el.classList.remove('bulk-mode'));
        
        document.querySelectorAll('.task-select-checkbox').forEach(cb => cb.checked = false);
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
    }

    function updateBulkDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.task-select-checkbox:not(#selectAllTasks):checked');
        const count = checkedBoxes.length;
        selectedCountSpan.textContent = count;
        bulkDeleteBtn.style.display = count > 0 ? 'inline-flex' : 'none';
        
        if (selectAllCheckbox) {
            const allBoxes = document.querySelectorAll('.task-select-checkbox:not(#selectAllTasks)');
            selectAllCheckbox.checked = count === allBoxes.length && count > 0;
            selectAllCheckbox.indeterminate = count > 0 && count < allBoxes.length;
        }
    }

    if (bulkModeBtn) bulkModeBtn.addEventListener('click', enterBulkMode);
    if (cancelBulkBtn) cancelBulkBtn.addEventListener('click', exitBulkMode);

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            document.querySelectorAll('.task-select-checkbox:not(#selectAllTasks)').forEach(cb => cb.checked = this.checked);
            updateBulkDeleteButton();
        });
    }

    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('task-select-checkbox') && e.target.id !== 'selectAllTasks') {
            updateBulkDeleteButton();
        }
    });

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            if (!confirm('Удалить выбранные задачи? Это действие нельзя отменить.')) return;

            const checkedBoxes = document.querySelectorAll('.task-select-checkbox:not(#selectAllTasks):checked');
            const taskIds = Array.from(checkedBoxes).map(cb => cb.value);
            const csrfToken = document.getElementById('csrf_token_bulk')?.value;

            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.textContent = 'Удаление...';

            fetch('/api/tasks/delete-bulk', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ taskIds: taskIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || 'Ошибка при удалении');
                    bulkDeleteBtn.disabled = false;
                    bulkDeleteBtn.innerHTML = 'Удалить выбранные (<span id="selectedCount">' + taskIds.length + '</span>)';
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Ошибка соединения');
                bulkDeleteBtn.disabled = false;
            });
        });
    }
});