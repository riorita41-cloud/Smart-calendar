document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('.questions-textarea');
    const form = document.querySelector('.questions-form-box form');
    
    if (!textarea || !form) return;
    
    function validateQuestions(text) {
        const lines = text.split('\n');
        const errors = [];
        
        lines.forEach((line, index) => {
            const lineNumber = index + 1;
            const trimmedLine = line.trim();
            
            if (trimmedLine === '') return;
            
            const pipeCount = (trimmedLine.match(/\|/g) || []).length;
            if (pipeCount > 1) {
                errors.push(`Строка ${lineNumber}: найдено ${pipeCount} символов "|". Допускается только один.`);
            }
            
            const answerCount = (trimmedLine.match(/ответ:/gi) || []).length;
            if (answerCount > 1) {
                errors.push(`Строка ${lineNumber}: найдено ${answerCount} слов "Ответ:". Допускается только одно.`);
            }
            
            if (pipeCount > 0 && answerCount > 0) {
                errors.push(`Строка ${lineNumber}: нельзя использовать "|" и "Ответ:" одновременно. Выберите один разделитель.`);
            }
        });
        
        return errors;
    }
    
    function showErrors(errors) {
        const oldError = document.querySelector('.validation-error-box');
        if (oldError) oldError.remove();
        
        if (errors.length === 0) return;
        
        const errorBox = document.createElement('div');
        errorBox.className = 'validation-error-box';
        errorBox.innerHTML = `
            <div style="background: #FEE2E2; border-left: 4px solid #DC2626; padding: 20px; border-radius: 10px; margin-bottom: 20px; font-family: Georgia, serif;">
                <h3 style="color: #DC2626; margin: 0 0 15px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Найдены ошибки в формате вопросов:
                </h3>
                <ul style="margin: 0; padding-left: 20px; color: #7F1D1D; line-height: 1.8;">
                    ${errors.map(err => `<li>${err}</li>`).join('')}
                </ul>
                <p style="margin: 15px 0 0 0; color: #7F1D1D; font-size: 14px; background: rgba(255,255,255,0.5); padding: 10px; border-radius: 6px;">
                    <strong>Правильный формат:</strong><br>
                    • Вопрос? Ответ: правильный ответ<br>
                    • Вопрос? | правильный ответ
                </p>
            </div>
        `;
        
        const formBox = document.querySelector('.questions-form-box');
        if (formBox) {
            formBox.insertBefore(errorBox, formBox.firstChild);
        }
        
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    form.addEventListener('submit', function(e) {
        const text = textarea.value;
        const errors = validateQuestions(text);
        
        if (errors.length > 0) {
            e.preventDefault(); 
            showErrors(errors);
            return false;
        }
    });
});