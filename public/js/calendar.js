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
    apiFetch('/api/task/' + taskId + '/toggle', {
        method: 'POST'
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
        method: 'POST'
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

let tooltipTimer;

document.querySelectorAll('.day-cell').forEach(cell => {
    const tooltip = cell.querySelector('.day-tooltip');
    if (!tooltip) return;

    cell.addEventListener('mouseenter', () => {
        clearTimeout(tooltipTimer);
        tooltip.style.visibility = 'visible';
        tooltip.style.opacity = '1';
    });

    cell.addEventListener('mouseleave', () => {
        tooltipTimer = setTimeout(() => {
            tooltip.style.visibility = 'hidden';
            tooltip.style.opacity = '0';
        }, 300);
    });

    tooltip.addEventListener('mouseenter', () => {
        clearTimeout(tooltipTimer);
    });

    tooltip.addEventListener('mouseleave', () => {
        tooltip.style.visibility = 'hidden';
        tooltip.style.opacity = '0';
    });
});