function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    
    allDropdowns.forEach(d => {
        if (d.id !== id) {
            d.classList.remove('show');
        }
    });
    
    dropdown.classList.toggle('show');
}

document.addEventListener('click', function(event) {
    if (!event.target.closest('.month-year-selector')) {
        document.querySelectorAll('.dropdown-menu').forEach(d => {
            d.classList.remove('show');
        });
    }
});

function openTaskModal(date) {
    document.getElementById('taskDate').value = date;
    document.getElementById('taskModal').style.display = 'block';
}

document.getElementById('saveTaskBtn').addEventListener('click', function() {
    const data = {
        title: document.getElementById('taskTitle').value,
        date: document.getElementById('taskDate').value,
        examId: document.getElementById('examSelect').value
    };

    const csrfToken = document.getElementById('csrf_token').value;

    fetch('/api/task/quick-add', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});