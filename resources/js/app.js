// Menu handling
document.addEventListener('DOMContentLoaded', function() {
    // Menu toggle for mobile
    const menuToggle = document.querySelector('.layout-menu-toggle');
    const menu = document.querySelector('.layout-menu');
    const overlay = document.querySelector('.layout-overlay');

    if (menuToggle && menu && overlay) {
        menuToggle.addEventListener('click', () => {
            menu.classList.toggle('show');
            overlay.classList.toggle('d-block');
        });

        overlay.addEventListener('click', () => {
            menu.classList.remove('show');
            overlay.classList.remove('d-block');
        });
    }

    // Active menu item
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.menu-item a');

    menuItems.forEach(item => {
        if (item.getAttribute('href') === currentPath) {
            item.closest('.menu-item').classList.add('active');
        }
    });

    // Password visibility toggle
    const passwordToggles = document.querySelectorAll('.form-password-toggle i');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            const input = e.target.closest('.input-group').querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            toggle.classList.toggle('bx-show');
            toggle.classList.toggle('bx-hide');
        });
    });

    // Dropdown menus
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            const dropdown = e.target.closest('.dropdown');
            dropdown.classList.toggle('show');
            const menu = dropdown.querySelector('.dropdown-menu');
            menu.classList.toggle('show');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown.show').forEach(dropdown => {
                dropdown.classList.remove('show');
                dropdown.querySelector('.dropdown-menu').classList.remove('show');
            });
        }
    });

    // Toast notifications
    window.showToast = function(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast bg-${type} text-white`;
        toast.innerHTML = `
            <div class="toast-header bg-${type} text-white">
                <i class="bx bx-bell me-2"></i>
                <strong class="me-auto">Notificación</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    };

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Dark mode toggle
const darkModeToggle = document.querySelector('.dark-mode-toggle');
if (darkModeToggle) {
    darkModeToggle.addEventListener('click', () => {
        document.body.setAttribute(
            'data-theme',
            document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'
        );
        localStorage.setItem(
            'theme',
            document.body.getAttribute('data-theme')
        );
    });
}

// Apply saved theme
const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
    document.body.setAttribute('data-theme', savedTheme);
}

// AJAX request helper
window.ajaxRequest = async function(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
        showToast(error.message, 'danger');
        throw error;
    }
};
