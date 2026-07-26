document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-exam-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const confirmed = await CustomModal.confirm(
                'Удалить этот экзамен? Это действие нельзя отменить.',
                'Подтверждение удаления'
            );
            
            if (confirmed) {
                this.submit();
            }
        });
    });
});