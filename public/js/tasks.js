document.addEventListener('DOMContentLoaded', () => {
    const saveBtn = document.getElementById('saveTaskBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const titleInput = document.getElementById('taskTitle');
            const dateInput = document.getElementById('taskDate');
            const examSelect = document.getElementById('taskExam');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!titleInput || !dateInput) return;

            const title = titleInput.value.trim();
            const date = dateInput.value;
            const examId = examSelect ? examSelect.value : null;

            if (!title) {
                alert('Введите название задачи');
                return;
            }


            if (!csrfToken) {
                alert('Ошибка безопасности: токен не найден');
                return;
            }

            fetch('/api/task/quick-add', {
                method: 'POST',
                credentials: 'include',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken 
                },
                body: JSON.stringify({ title: title, date: date, examId: examId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); 
                } else {
                    alert(data.message || 'Ошибка сохранения');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Ошибка соединения');
            });
        });
    }

    const bulkModeBtn = document.getElementById('bulkModeBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const cancelBulkBtn = document.getElementById('cancelBulkBtn');
    const bulkSelectHeader = document.getElementById('bulkSelectHeader');
    const selectAllCheckbox = document.getElementById('selectAllTasks');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    let bulkMode = false;

    function enterBulkMode() {
        bulkMode = true;
        if (bulkModeBtn) bulkModeBtn.style.display = 'none';
        if (cancelBulkBtn) cancelBulkBtn.style.display = 'inline-flex';
        if (bulkSelectHeader) bulkSelectHeader.style.display = 'flex';
        
        document.querySelectorAll('.normal-mode').forEach(el => {
            if (!el.classList.contains('task-actions')) {
                el.style.display = 'none';
            }
        });
        document.querySelectorAll('.bulk-mode-checkbox').forEach(el => el.style.display = 'flex');
        document.querySelectorAll('.task-card').forEach(el => el.classList.add('bulk-mode'));
        
        updateBulkDeleteButton();
    }

    function exitBulkMode() {
        bulkMode = false;
        if (bulkModeBtn) bulkModeBtn.style.display = 'inline-flex';
        if (cancelBulkBtn) cancelBulkBtn.style.display = 'none';
        if (bulkDeleteBtn) bulkDeleteBtn.style.display = 'none';
        if (bulkSelectHeader) bulkSelectHeader.style.display = 'none';
        
        document.querySelectorAll('.normal-mode').forEach(el => el.style.display = '');
        document.querySelectorAll('.bulk-mode-checkbox').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.task-card').forEach(el => el.classList.remove('bulk-mode'));
        
        document.querySelectorAll('.task-select-checkbox').forEach(cb => cb.checked = false);
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
    }

    function updateBulkDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.task-select-checkbox:not(#selectAllTasks):checked');
        const count = checkedBoxes.length;
        if (selectedCountSpan) selectedCountSpan.textContent = count;
        if (bulkDeleteBtn) {
            bulkDeleteBtn.style.display = count > 0 ? 'inline-flex' : 'none';
        }
        
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                alert('Ошибка безопасности: токен не найден');
                return;
            }

            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.textContent = 'Удаление...';

            fetch('/api/tasks/delete-bulk', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
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

function openTaskModal(dateStr) {
    const modal = document.getElementById('taskModal');
    const dateInput = document.getElementById('taskDate');
    if (modal && dateInput) {
        dateInput.value = dateStr;
        modal.style.display = 'block';
    }
}

function toggleTask(taskId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        console.error('CSRF токен не найден');
        alert('Ошибка безопасности: токен не найден. Обновите страницу.');
        return;
    }

    fetch('/api/task/' + taskId + '/toggle', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            alert(data.message || 'Ошибка при обновлении задачи');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Ошибка соединения');
    });
}

function toggleDropdown(id) {
    const menu = document.getElementById(id);
    if (!menu) return;
    
    document.querySelectorAll('.dropdown-menu').forEach(d => {
        if (d.id !== id) d.classList.remove('show');
    });
    menu.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.month-year-selector')) {
        document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.remove('show'));
    }
});