// Image preview before upload (add_item.php / edit_item.php)
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.querySelector('input[name="image"]');
    if (imageInput) {
        const preview = document.createElement('img');
        preview.style.width = '150px';
        preview.style.marginTop = '10px';
        preview.style.borderRadius = '8px';
        preview.style.display = 'none';
        imageInput.parentNode.insertBefore(preview, imageInput.nextSibling);

        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Character counter for description textarea
    const description = document.querySelector('textarea[name="description"]');
    if (description) {
        const counter = document.createElement('p');
        counter.style.fontSize = '12px';
        counter.style.color = '#94a3b8';
        counter.style.marginTop = '4px';
        description.parentNode.insertBefore(counter, description.nextSibling);

        const updateCounter = () => {
            counter.textContent = description.value.length + ' characters';
        };
        updateCounter();
        description.addEventListener('input', updateCounter);
    }

    // Simple client-side validation on forms with required fields
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#b91c1c';
                } else {
                    field.style.borderColor = '#dfe3e8';
                }
            });
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
});

// Confirm before delete (replaces inline onclick, cleaner)
function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this item?')) {
        event.preventDefault();
    }
}