/**
 * Appliances Management System - Main JavaScript File
 * Handles animations, interactions, and dynamic functionality
 */

// Global application object
const AppliancesApp = {
    init: function() {
        this.bindEvents();
        this.initAnimations();
        this.initModals();
        this.initForms();
        this.initTables();
        this.checkSessionTimeout();
    },

    // Event Binding
    bindEvents: function() {
        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                navMenu.classList.toggle('active');
            });
        }

        // Sidebar toggle
        const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (sidebar && sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !e.target.matches('[data-sidebar-toggle]')) {
                sidebar.classList.remove('active');
            }
        });

        // Header scroll effect
        window.addEventListener('scroll', this.handleHeaderScroll);

        // Alert close buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('alert-close')) {
                const alert = e.target.closest('.alert');
                if (alert) {
                    alert.style.animation = 'slideOutRight 0.3s ease-out';
                    setTimeout(() => alert.remove(), 300);
                }
            }
        });

        // Form submission with loading state
        document.addEventListener('submit', this.handleFormSubmit);

        // Auto-hide flash messages
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (!alert.classList.contains('alert-persistent')) {
                    alert.style.animation = 'slideOutRight 0.3s ease-out';
                    setTimeout(() => alert.remove(), 300);
                }
            });
        }, 5000);
    },

    // Header scroll animation
    handleHeaderScroll: function() {
        const header = document.querySelector('.header');
        if (header) {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
    },

    // Initialize animations
    initAnimations: function() {
        // Fade in elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease-out';
                    entry.target.style.opacity = '1';
                }
            });
        }, observerOptions);

        // Observe elements with fade-in class
        document.querySelectorAll('.fade-in').forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });

        // Card hover effects
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Button hover effects
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                if (!this.disabled) {
                    this.style.transform = 'translateY(-2px)';
                }
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    },

    // Modal functionality
    initModals: function() {
        // Modal triggers
        document.addEventListener('click', function(e) {
            const modalTrigger = e.target.closest('[data-modal-target]');
            if (modalTrigger) {
                e.preventDefault();
                const targetId = modalTrigger.getAttribute('data-modal-target');
                const modal = document.getElementById(targetId);
                if (modal) {
                    AppliancesApp.showModal(modal);
                }
            }

            // Modal close
            if (e.target.classList.contains('modal') || 
                e.target.classList.contains('modal-close') ||
                e.target.closest('.modal-close')) {
                const modal = e.target.closest('.modal');
                if (modal) {
                    AppliancesApp.hideModal(modal);
                }
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.modal[style*="display: block"]');
                if (activeModal) {
                    AppliancesApp.hideModal(activeModal);
                }
            }
        });
    },

    // Show modal
    showModal: function(modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Focus management
        const firstFocusable = modal.querySelector('input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) {
            setTimeout(() => firstFocusable.focus(), 100);
        }
    },

    // Hide modal
    hideModal: function(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    },

    // Form handling
    initForms: function() {
        // Real-time validation
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('form-control')) {
                AppliancesApp.validateField(e.target);
            }
        });

        // Password strength indicator
        const passwordFields = document.querySelectorAll('input[type="password"]');
        passwordFields.forEach(field => {
            if (field.name === 'password' || field.id === 'password') {
                field.addEventListener('input', function() {
                    AppliancesApp.checkPasswordStrength(this);
                });
            }
        });

        // Confirm password validation
        const confirmPasswordField = document.querySelector('input[name="confirm_password"]');
        const passwordField = document.querySelector('input[name="password"]');
        
        if (confirmPasswordField && passwordField) {
            confirmPasswordField.addEventListener('input', function() {
                if (this.value !== passwordField.value) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
        }
    },

    // Field validation
    validateField: function(field) {
        const value = field.value.trim();
        let isValid = true;

        // Email validation
        if (field.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            isValid = emailRegex.test(value);
        }

        // Required field validation
        if (field.required && value === '') {
            isValid = false;
        }

        // Phone number validation
        if (field.name === 'phone' || field.type === 'tel') {
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            isValid = phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''));
        }

        // Update field appearance
        if (isValid) {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        } else {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }

        return isValid;
    },

    // Password strength checker
    checkPasswordStrength: function(passwordField) {
        const password = passwordField.value;
        const strengthIndicator = document.getElementById('password-strength');
        
        if (!strengthIndicator) return;

        let strength = 0;
        let feedback = [];

        // Length check
        if (password.length >= 8) strength++;
        else feedback.push('At least 8 characters');

        // Lowercase check
        if (/[a-z]/.test(password)) strength++;
        else feedback.push('Lowercase letter');

        // Uppercase check
        if (/[A-Z]/.test(password)) strength++;
        else feedback.push('Uppercase letter');

        // Number check
        if (/\d/.test(password)) strength++;
        else feedback.push('Number');

        // Special character check
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        else feedback.push('Special character');

        // Update strength indicator
        const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const strengthColors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'];
        
        strengthIndicator.textContent = strengthLevels[strength] || 'Very Weak';
        strengthIndicator.style.color = strengthColors[strength] || '#dc3545';
        
        if (feedback.length > 0) {
            strengthIndicator.textContent += ' (Missing: ' + feedback.join(', ') + ')';
        }
    },

    // Form submission handling
    handleFormSubmit: function(e) {
        const form = e.target;
        if (form.tagName !== 'FORM') return;

        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
            
            // Re-enable after 5 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Submit';
            }, 5000);
        }
    },

    // Table functionality
    initTables: function() {
        // Sortable tables
        document.querySelectorAll('.table-sortable th[data-sort]').forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', function() {
                AppliancesApp.sortTable(this);
            });
        });

        // Search functionality
        const searchInputs = document.querySelectorAll('[data-table-search]');
        searchInputs.forEach(input => {
            input.addEventListener('input', function() {
                AppliancesApp.searchTable(this);
            });
        });

        // Row selection
        document.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox' && e.target.closest('table')) {
                AppliancesApp.handleRowSelection(e.target);
            }
        });
    },

    // Table sorting
    sortTable: function(th) {
        const table = th.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(th.parentNode.children).indexOf(th);
        const sortOrder = th.getAttribute('data-sort-order') || 'asc';
        
        rows.sort((a, b) => {
            const aVal = a.children[columnIndex].textContent.trim();
            const bVal = b.children[columnIndex].textContent.trim();
            
            // Try to parse as numbers
            const aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
            const bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ''));
            
            let comparison = 0;
            if (!isNaN(aNum) && !isNaN(bNum)) {
                comparison = aNum - bNum;
            } else {
                comparison = aVal.localeCompare(bVal);
            }
            
            return sortOrder === 'asc' ? comparison : -comparison;
        });
        
        // Update table
        rows.forEach(row => tbody.appendChild(row));
        
        // Update sort order
        th.setAttribute('data-sort-order', sortOrder === 'asc' ? 'desc' : 'asc');
        
        // Update sort indicators
        table.querySelectorAll('th[data-sort]').forEach(header => {
            header.classList.remove('sort-asc', 'sort-desc');
        });
        th.classList.add(sortOrder === 'asc' ? 'sort-desc' : 'sort-asc');
    },

    // Table search
    searchTable: function(input) {
        const targetTable = document.querySelector(input.getAttribute('data-table-search'));
        if (!targetTable) return;
        
        const searchTerm = input.value.toLowerCase();
        const rows = targetTable.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    },

    // Row selection handling
    handleRowSelection: function(checkbox) {
        const table = checkbox.closest('table');
        const isHeaderCheckbox = checkbox.closest('thead');
        
        if (isHeaderCheckbox) {
            // Select/deselect all rows
            const bodyCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]');
            bodyCheckboxes.forEach(cb => {
                cb.checked = checkbox.checked;
                AppliancesApp.toggleRowHighlight(cb);
            });
        } else {
            AppliancesApp.toggleRowHighlight(checkbox);
        }
        
        // Update bulk action buttons
        const selectedCount = table.querySelectorAll('tbody input[type="checkbox"]:checked').length;
        const bulkActions = document.querySelectorAll('[data-bulk-action]');
        
        bulkActions.forEach(action => {
            if (selectedCount > 0) {
                action.style.display = 'inline-block';
            } else {
                action.style.display = 'none';
            }
        });
    },

    // Toggle row highlight
    toggleRowHighlight: function(checkbox) {
        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.classList.add('table-row-selected');
        } else {
            row.classList.remove('table-row-selected');
        }
    },

    // Session timeout check
    checkSessionTimeout: function() {
        setInterval(() => {
            fetch('/api/check-session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        this.showSessionExpiredModal();
                    }
                })
                .catch(error => {
                    console.log('Session check failed:', error);
                });
        }, 300000); // Check every 5 minutes
    },

    // Show session expired modal
    showSessionExpiredModal: function() {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Session Expired</h5>
                    </div>
                    <div class="modal-body">
                        <p>Your session has expired. Please log in again to continue.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="window.location.href='/login.php'">
                            Login Again
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.showModal(modal);
    },

    // Utility functions
    utils: {
        // Format currency
        formatCurrency: function(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },

        // Format date
        formatDate: function(date, options = {}) {
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                ...options
            }).format(new Date(date));
        },

        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Show toast notification
        showToast: function(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} toast`;
            toast.innerHTML = `
                ${message}
                <button type="button" class="alert-close">&times;</button>
            `;
            
            // Position toast
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            toast.style.minWidth = '300px';
            toast.style.animation = 'slideInRight 0.3s ease-out';
            
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        },

        // Confirm dialog
        confirm: function(message, callback) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Confirm Action</h5>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-action="cancel">Cancel</button>
                            <button type="button" class="btn btn-danger" data-action="confirm">Confirm</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            AppliancesApp.showModal(modal);
            
            modal.addEventListener('click', function(e) {
                const action = e.target.getAttribute('data-action');
                if (action === 'confirm') {
                    callback(true);
                    AppliancesApp.hideModal(modal);
                    modal.remove();
                } else if (action === 'cancel') {
                    callback(false);
                    AppliancesApp.hideModal(modal);
                    modal.remove();
                }
            });
        }
    }
};

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    AppliancesApp.init();
});

// Add additional CSS for JavaScript enhancements
const additionalStyles = `
    <style>
        .toast {
            animation: slideInRight 0.3s ease-out;
        }
        
        .table-row-selected {
            background-color: rgba(198, 216, 112, 0.1) !important;
        }
        
        .sort-asc::after {
            content: ' ↑';
            color: var(--primary-green);
        }
        
        .sort-desc::after {
            content: ' ↓';
            color: var(--primary-green);
        }
        
        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100px);
            }
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary-green);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    </style>
`;

document.head.insertAdjacentHTML('beforeend', additionalStyles);