document.addEventListener('DOMContentLoaded', () => {
    const saveBtn = document.getElementById('saveTaskBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const titleInput = document.getElementById('taskTitle');
            const dateInput = document.getElementById('taskDate');
            const examSelect = document.getElementById('taskExam');

            if (!titleInput || !dateInput) return;

            const title = titleInput.value.trim();
            const date = dateInput.value;
            const examId = examSelect ? examSelect.value : null;

            if (!title) {
                alert('Введите название задачи');
                return;
            }


            apiFetch('/api/task/quick-add', {
                method: 'POST',
                credentials: 'include',
                body: JSON.stringify({ title: title, date: date, examId: examId })
            }).then(data => {
                if (data && data.status === 'success') {
                    location.reload(); 
                } else if (data) {
                    alert(data.message || 'Ошибка сохранения');
                }
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
        bulkDeleteBtn.addEventListener('click', async function() {
            const confirmed = await CustomModal.confirm(
                'Удалить выбранные задачи? Это действие нельзя отменить.',
                'Подтверждение удаления'
            );
            
            if (!confirmed) return;

            const checkedBoxes = document.querySelectorAll('.task-select-checkbox:not(#selectAllTasks):checked');
            const taskIds = Array.from(checkedBoxes).map(cb => cb.value);

            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.textContent = 'Удаление...';

            apiFetch('/api/tasks/delete-bulk', {
                method: 'POST',
                credentials: 'include',
                body: JSON.stringify({ taskIds: taskIds })
            }).then(data => {
                if (data && data.status === 'success') {
                    location.reload();
                } else if (data) {
                    alert(data.message || 'Ошибка при удалении');
                    bulkDeleteBtn.disabled = false;
                    bulkDeleteBtn.innerHTML = 'Удалить выбранные (<span id="selectedCount">' + taskIds.length + '</span>)';
                }
            }).catch(() => {
                bulkDeleteBtn.disabled = false;
            });
        });
    }

    document.querySelectorAll('.delete-task-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault(); 
            
            const confirmed = await CustomModal.confirm(
                'Удалить эту задачу? Это действие нельзя отменить.',
                'Подтверждение удаления'
            );
            
            if (confirmed) {
                this.submit(); 
            }
        });
    });

    const dayCells = document.querySelectorAll('.day-cell:not(.empty)');
    
    let activeCell = null;
    let activeTooltip = null;

    dayCells.forEach(cell => {
        cell.addEventListener('click', function(event) {
            if (event.target.closest('.tooltip-create-btn') || 
                event.target.closest('.task-checkbox') || 
                event.target.closest('.day-tooltip a')) {
                return;
            }

            event.stopPropagation();

            const tooltip = this.querySelector('.day-tooltip');

            if (activeCell === this) {
                activeCell.classList.remove('active');
                activeCell.appendChild(activeTooltip);
                activeTooltip.style.cssText = '';
                activeCell = null;
                activeTooltip = null;
                return;
            }

            if (activeCell) {
                activeCell.classList.remove('active');
                activeCell.appendChild(activeTooltip);
                activeTooltip.style.cssText = '';
            }

            this.classList.add('active');
            activeCell = this;
            activeTooltip = tooltip;

            const calendarCard = this.closest('.calendar-card');
            if (calendarCard && window.innerWidth <= 768) {
                calendarCard.appendChild(tooltip);
                
                tooltip.style.setProperty('display', 'block', 'important');
                tooltip.style.setProperty('visibility', 'visible', 'important');
                tooltip.style.setProperty('opacity', '1', 'important');
                tooltip.style.setProperty('position', 'relative', 'important');
                tooltip.style.setProperty('width', '100%', 'important');
                tooltip.style.setProperty('left', '0', 'important');
                tooltip.style.setProperty('right', '0', 'important');
                tooltip.style.setProperty('margin', '15px 0 0 0', 'important');
                tooltip.style.setProperty('padding', '15px', 'important');
                tooltip.style.setProperty('box-sizing', 'border-box', 'important');
                tooltip.style.setProperty('border-radius', '12px', 'important');
                tooltip.style.setProperty('box-shadow', '0 4px 15px rgba(0, 0, 0, 0.1)', 'important');
                tooltip.style.setProperty('border', '1px solid var(--color-border)', 'important');
                tooltip.style.setProperty('background', 'var(--color-bg-light)', 'important');
                tooltip.style.setProperty('z-index', '10', 'important');
                tooltip.style.setProperty('max-height', 'none', 'important');
                tooltip.style.setProperty('overflow-y', 'visible', 'important');
                tooltip.style.setProperty('transform', 'none', 'important');
            }
        });
    });

    document.addEventListener('click', function(event) {
        if (!event.target.closest('.day-tooltip') && !event.target.closest('.day-cell')) {
            if (activeCell) {
                activeCell.classList.remove('active');
                activeCell.appendChild(activeTooltip);
                activeTooltip.style.cssText = '';
                activeCell = null;
                activeTooltip = null;
            }
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && activeCell) {
            activeCell.classList.remove('active');
            activeCell.appendChild(activeTooltip);
            activeTooltip.style.cssText = '';
            activeCell = null;
            activeTooltip = null;
        }
    });
});


function openTaskModalFromTooltip(event, dateStr) {
    event.preventDefault();
    event.stopPropagation();
    openTaskModal(dateStr);
}

function openTaskModal(dateStr) {
    const modal = document.getElementById('taskModal');
    const dateInput = document.getElementById('taskDate');
    if (modal && dateInput) {
        dateInput.value = dateStr;
        modal.style.display = 'block';
    }
}

function toggleTask(taskId) {
    apiFetch('/api/task/' + taskId + '/toggle', {
        method: 'POST',
        credentials: 'include'
    }).then(data => {
        if (data && data.status === 'success') {
            location.reload();
        } else {
            alert('Ошибка при обновлении задачи');
        }
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

function toggleStudied(questionId, btn) {
    const originalText = btn.textContent;
    btn.textContent = '...';
    btn.disabled = true;
    
    apiFetch('/api/question/' + questionId + '/toggle-studied', {
        method: 'POST',
        credentials: 'include'
    }).then(data => {
        const item = btn.closest('.tooltip-study-item');
        if (data && data.status === 'success') {
            if (data.studied) {
                item.classList.add('studied');
                btn.textContent = '✓ Выучено';
            } else {
                item.classList.remove('studied');
                btn.textContent = 'Отметить как выучено';
            }
        } else {
            alert(data?.message || 'Ошибка при обновлении');
            btn.textContent = originalText;
            btn.disabled = false;
        }
    }).catch(() => {
        btn.textContent = originalText;
        btn.disabled = false;
    });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.month-year-selector')) {
        document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.remove('show'));
    }
});