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

            if (!examId) {
                alert('Выберите экзамен');
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