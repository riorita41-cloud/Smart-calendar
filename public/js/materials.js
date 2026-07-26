document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-material-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const confirmed = await CustomModal.confirm(
                'Удалить этот материал и все связанные с ним вопросы? Это действие нельзя отменить.',
                'Подтверждение удаления'
            );
            
            if (confirmed) {
                this.submit();
            }
        });
    });
});