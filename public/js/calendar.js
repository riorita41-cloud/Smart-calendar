function openTaskModal(dateStr) {
    const modal = document.getElementById('taskModal');
    const dateInput = document.getElementById('taskDate');
    if (modal && dateInput) {
        dateInput.value = dateStr;
        modal.style.display = 'block';
    }
}

const saveBtn = document.getElementById('saveTaskBtn');
if (saveBtn) {
    saveBtn.addEventListener('click', function() {
        const title = document.getElementById('taskTitle').value.trim();
        const date = document.getElementById('taskDate').value;
        const csrfToken = document.getElementById('csrf_token').value;

        if (!title) {
            alert('Введите название задачи');
            return;
        }

        const data = { title: title, date: date, examId: null };

        fetch('/api/task/quick-add', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken 
            },
            body: JSON.stringify(data)
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

function toggleTask(taskId) {
    const csrfToken = document.getElementById('csrf_token').value;
    fetch(`/api/task/${taskId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(() => location.reload())
    .catch(err => console.error(err));
}

function toggleDropdown(id) {
    document.querySelectorAll('.dropdown-menu').forEach(d => {
        if (d.id !== id) d.classList.remove('show');
    });
    document.getElementById(id).classList.toggle('show');
}