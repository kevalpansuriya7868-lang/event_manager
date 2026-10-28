/**
 * Event Management System
 * Client-Side JavaScript: Validation, UI Interactions, and Search Filtering
 */

document.addEventListener('DOMContentLoaded', function () {
    // ------------------------------------------------------------
    // 1. Auto-dismiss Flash Alerts after 5 seconds
    // ------------------------------------------------------------
    const flashAlert = document.getElementById('flashAlert');
    if (flashAlert) {
        setTimeout(function () {
            flashAlert.style.transition = 'opacity 0.5s ease';
            flashAlert.style.opacity = '0';
            setTimeout(() => flashAlert.style.display = 'none', 500);
        }, 5000);
    }

    // ------------------------------------------------------------
    // 2. Client-Side Form Validation
    // ------------------------------------------------------------
    const forms = document.querySelectorAll('.needs-validation');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            let isValid = true;
            const inputs = form.querySelectorAll('input, select, textarea');

            inputs.forEach(function (input) {
                // Remove existing errors first
                clearFieldError(input);

                // Ignore hidden inputs and buttons
                if (input.type === 'hidden' || input.type === 'submit' || input.type === 'button') {
                    return;
                }

                const val = input.value.trim();

                // Required field check
                if (input.hasAttribute('required') && val === '') {
                    showFieldError(input, 'This field is required.');
                    isValid = false;
                    return;
                }

                // Email validation
                if (input.type === 'email' && val !== '') {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(val)) {
                        showFieldError(input, 'Please enter a valid email address.');
                        isValid = false;
                        return;
                    }
                }

                // Phone number validation (10 to 15 digits)
                if (input.name && input.name.toLowerCase().includes('phone') && val !== '') {
                    const phonePattern = /^[0-9+\s\-()]{10,15}$/;
                    if (!phonePattern.test(val)) {
                        showFieldError(input, 'Please enter a valid phone number (at least 10 digits).');
                        isValid = false;
                        return;
                    }
                }

                // Number validation (Capacity, Quantity, Sponsorship Amount > 0)
                if (input.type === 'number' && val !== '') {
                    const numVal = parseFloat(val);
                    const min = parseFloat(input.getAttribute('min') || '0');
                    if (isNaN(numVal) || numVal < min) {
                        showFieldError(input, `Value must be at least ${min}.`);
                        isValid = false;
                        return;
                    }
                }
            });

            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
                // Focus the first invalid input
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.focus();
                }
            }
        });

        // Real-time error clearing on input/change
        form.querySelectorAll('input, select, textarea').forEach(function (input) {
            input.addEventListener('input', function () {
                if (input.classList.contains('is-invalid')) {
                    clearFieldError(input);
                }
            });
            input.addEventListener('change', function () {
                if (input.classList.contains('is-invalid')) {
                    clearFieldError(input);
                }
            });
        });
    });

    function showFieldError(input, message) {
        input.classList.add('is-invalid');
        let errorDiv = input.parentElement.querySelector('.form-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'form-error';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    function clearFieldError(input) {
        input.classList.remove('is-invalid');
        const errorDiv = input.parentElement.querySelector('.form-error');
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }
    }

    // ------------------------------------------------------------
    // 3. Confirm Deletion Prompts
    // ------------------------------------------------------------
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const itemName = btn.getAttribute('data-item') || 'this record';
            const confirmed = confirm(`Are you sure you want to permanently delete "${itemName}"?\n\nThis action cannot be undone.`);
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });

    // ------------------------------------------------------------
    // 4. Live Table Instant Search Filter (Client-Side)
    // ------------------------------------------------------------
    const liveSearchInput = document.getElementById('tableLiveSearch');
    if (liveSearchInput) {
        liveSearchInput.addEventListener('keyup', function () {
            const filter = liveSearchInput.value.toLowerCase();
            const table = document.querySelector('.data-table tbody');
            if (!table) return;

            const rows = table.querySelectorAll('tr');
            rows.forEach(function (row) {
                // Skip empty state row if present
                if (row.querySelector('.empty-state')) return;
                
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
