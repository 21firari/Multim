// Authentication related functions
document.addEventListener('DOMContentLoaded', function() {
    // Login form submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!validateForm(this)) return;

            const formData = new FormData(this);
            ajaxRequest('/login', 'POST', Object.fromEntries(formData))
                .then(response => {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showAlert('error', response.message);
                    }
                })
                .catch(error => {
                    showAlert('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');
                });
        });
    }

    // Register form submission
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!validateForm(this)) return;

            const formData = new FormData(this);
            ajaxRequest('/register', 'POST', Object.fromEntries(formData))
                .then(response => {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showAlert('error', response.message);
                    }
                })
                .catch(error => {
                    showAlert('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');
                });
        });
    }
});

// Show alert message
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    if (alertContainer) {
        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
        alertElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alertElement);

        setTimeout(() => {
            alertElement.remove();
        }, 5000);
    }
}