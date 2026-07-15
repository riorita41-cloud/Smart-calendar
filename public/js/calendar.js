function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    if (!dropdown) return;
    
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    allDropdowns.forEach(d => {
        if (d.id !== id) {
            d.classList.remove('show');
        }
    });
    
    dropdown.classList.toggle('show');
}

document.addEventListener('click', function(event) {
    if (!event.target.closest('.selector-btn') && !event.target.closest('.dropdown-menu')) {
        document.querySelectorAll('.dropdown-menu').forEach(d => {
            d.classList.remove('show');
        });
    }
});

function openTaskModal(date) {
    const modal = document.getElementById('taskModal');
    if (modal) {
        document.getElementById('taskDate').value = date;
        modal.style.display = 'block';
    }
}

const saveBtn = document.getElementById('saveTaskBtn');
if (saveBtn) {
    saveBtn.addEventListener('click', function() {
        const title = document.getElementById('taskTitle').value.trim();
        if (!title) {
            alert('Введите название задачи');
            return;
        }

        const data = {
            title: title,
            date: document.getElementById('taskDate').value
        };

        const csrfInput = document.getElementById('csrf_token');
        const csrfToken = csrfInput ? csrfInput.value : '';

        fetch('/api/task/quick-add', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken 
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) throw new Error('Ошибка сети');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert(data.message || 'Ошибка сохранения');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при отправке запроса');
        });
    });
}

function toggleTask(taskId) {
    fetch(`/api/task/${taskId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.getElementById('csrf_token').value
        }
    })
    .then(() => location.reload())
    .catch(err => console.error(err));
}