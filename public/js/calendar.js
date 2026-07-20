document.addEventListener('DOMContentLoaded', () => {
    const saveBtn = document.getElementById('saveTaskBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const titleInput = document.getElementById('taskTitle');
            const dateInput = document.getElementById('taskDate');
            const examSelect = document.getElementById('taskExam');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!titleInput || !dateInput || !csrfToken) return;

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

            fetch('/api/task/quick-add', {
                method: 'POST',
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
            .catch(err => console.error('Error:', err));
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
        return;
    }

    fetch('/api/task/' + taskId + '/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            alert('Ошибка при обновлении задачи');
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

function toggleStudied(questionId, btn) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        alert('Ошибка безопасности: токен не найден');
        return;
    }
    
    const originalText = btn.textContent;
    btn.textContent = '...';
    btn.disabled = true;
    
    fetch('/api/question/' + questionId + '/toggle-studied', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        const item = btn.closest('.tooltip-study-item');
        
        if (data.status === 'success') {
            if (data.studied) {
                item.classList.add('studied');
                btn.textContent = '✓ Выучено';
            } else {
                item.classList.remove('studied');
                btn.textContent = 'Отметить как выучено';
            }
        } else {
            alert(data.message || 'Ошибка при обновлении');
            btn.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Ошибка соединения');
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