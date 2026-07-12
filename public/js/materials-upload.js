const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');

if (dropZone && fileInput) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.style.borderColor = '#6366F1';
            dropZone.style.background = '#E8E4FF';
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.style.borderColor = '#D4CFC4';
            dropZone.style.background = '#FAF7F0';
        }, false);
    });
    
    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFileList(files);
        }
    }, false);
    
    fileInput.addEventListener('change', (e) => {
        updateFileList(e.target.files);
    });
    
    function updateFileList(files) {
        fileList.innerHTML = '<strong>Выбрано файлов:</strong><br>';
        for (let file of files) {
            fileList.innerHTML += '📄 ' + file.name + '<br>';
        }
    }
}