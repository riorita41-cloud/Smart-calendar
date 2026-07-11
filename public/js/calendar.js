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
    
    // Закрыть dropdown при клике вне его
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.month-year-selector')) {
            document.querySelectorAll('.dropdown-menu').forEach(d => {
                d.classList.remove('show');
            });
        }
    });