        </div> <!-- End page-content -->
    </main> <!-- End admin-main -->

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Loading...</p>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="modal-close" data-modal-close>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="confirmation-message">Are you sure you want to perform this action?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-action">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Admin Panel Specific JavaScript
        const AdminPanel = {
            init: function() {
                this.initDataTables();
                this.initConfirmationModal();
                this.initFormValidation();
                this.initFileUploads();
                this.initTooltips();
            },

            // Initialize data tables
            initDataTables: function() {
                const tables = document.querySelectorAll('.data-table');
                tables.forEach(table => {
                    // Add search functionality
                    const searchInput = table.dataset.search;
                    if (searchInput) {
                        const input = document.querySelector(searchInput);
                        if (input) {
                            input.addEventListener('input', function() {
                                AdminPanel.searchTable(table, this.value);
                            });
                        }
                    }

                    // Add sorting functionality
                    const headers = table.querySelectorAll('th[data-sort]');
                    headers.forEach(header => {
                        header.style.cursor = 'pointer';
                        header.addEventListener('click', function() {
                            AdminPanel.sortTable(table, this);
                        });
                    });
                });
            },

            // Search table functionality
            searchTable: function(table, searchTerm) {
                const rows = table.querySelectorAll('tbody tr');
                const term = searchTerm.toLowerCase();

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            },

            // Sort table functionality
            sortTable: function(table, header) {
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const columnIndex = Array.from(header.parentNode.children).indexOf(header);
                const currentOrder = header.dataset.sortOrder || 'asc';
                const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

                // Clear other sort indicators
                table.querySelectorAll('th[data-sort]').forEach(th => {
                    th.classList.remove('sort-asc', 'sort-desc');
                    delete th.dataset.sortOrder;
                });

                // Set new sort order
                header.dataset.sortOrder = newOrder;
                header.classList.add(`sort-${newOrder}`);

                // Sort rows
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

                    return newOrder === 'asc' ? comparison : -comparison;
                });

                // Reorder rows in DOM
                rows.forEach(row => tbody.appendChild(row));
            },

            // Initialize confirmation modal
            initConfirmationModal: function() {
                const modal = document.getElementById('confirmation-modal');
                const confirmBtn = document.getElementById('confirm-action');
                const message = document.getElementById('confirmation-message');
                let confirmCallback = null;

                // Handle confirmation buttons
                document.addEventListener('click', function(e) {
                    const confirmElement = e.target.closest('[data-confirm]');
                    if (confirmElement) {
                        e.preventDefault();
                        
                        const confirmMessage = confirmElement.dataset.confirm || 'Are you sure?';
                        const action = confirmElement.href || confirmElement.dataset.action;
                        
                        message.textContent = confirmMessage;
                        AdminPanel.showModal(modal);
                        
                        confirmCallback = function() {
                            if (confirmElement.tagName === 'A') {
                                window.location.href = action;
                            } else if (confirmElement.tagName === 'FORM') {
                                confirmElement.submit();
                            } else if (action) {
                                // Handle custom actions
                                eval(action);
                            }
                            AdminPanel.hideModal(modal);
                        };
                    }
                });

                confirmBtn.addEventListener('click', function() {
                    if (confirmCallback) {
                        confirmCallback();
                        confirmCallback = null;
                    }
                });
            },

            // Show modal
            showModal: function(modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                setTimeout(() => modal.classList.add('show'), 10);
            },

            // Hide modal
            hideModal: function(modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }, 300);
            },

            // Initialize form validation
            initFormValidation: function() {
                const forms = document.querySelectorAll('.needs-validation');
                forms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        if (!form.checkValidity()) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    });
                });
            },

            // Initialize file uploads
            initFileUploads: function() {
                const fileInputs = document.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        AdminPanel.handleFileUpload(this);
                    });
                });
            },

            // Handle file upload preview
            handleFileUpload: function(input) {
                const file = input.files[0];
                if (file) {
                    const preview = input.nextElementSibling;
                    if (preview && preview.classList.contains('file-preview')) {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            preview.innerHTML = `<div class="file-info"><i class="fas fa-file"></i> ${file.name}</div>`;
                        }
                    }
                }
            },

            // Initialize tooltips
            initTooltips: function() {
                const tooltipElements = document.querySelectorAll('[data-tooltip]');
                tooltipElements.forEach(element => {
                    element.addEventListener('mouseenter', function() {
                        AdminPanel.showTooltip(this);
                    });
                    element.addEventListener('mouseleave', function() {
                        AdminPanel.hideTooltip();
                    });
                });
            },

            // Show tooltip
            showTooltip: function(element) {
                const text = element.dataset.tooltip;
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = text;
                tooltip.id = 'active-tooltip';
                
                document.body.appendChild(tooltip);
                
                const rect = element.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                
                setTimeout(() => tooltip.classList.add('show'), 10);
            },

            // Hide tooltip
            hideTooltip: function() {
                const tooltip = document.getElementById('active-tooltip');
                if (tooltip) {
                    tooltip.classList.remove('show');
                    setTimeout(() => tooltip.remove(), 300);
                }
            },

            // Show toast notification
            showToast: function(message, type = 'info', duration = 5000) {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = `
                    <div class="toast-content">
                        <i class="fas fa-${this.getToastIcon(type)}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="toast-close">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                container.appendChild(toast);
                
                // Show toast
                setTimeout(() => toast.classList.add('show'), 100);
                
                // Auto hide
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
                
                // Close button
                toast.querySelector('.toast-close').addEventListener('click', function() {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                });
            },

            // Get toast icon based on type
            getToastIcon: function(type) {
                const icons = {
                    success: 'check-circle',
                    error: 'exclamation-circle',
                    warning: 'exclamation-triangle',
                    info: 'info-circle'
                };
                return icons[type] || 'info-circle';
            },

            // Show/hide loading overlay
            showLoading: function() {
                document.getElementById('loading-overlay').style.display = 'flex';
            },

            hideLoading: function() {
                document.getElementById('loading-overlay').style.display = 'none';
            },

            // AJAX helper
            ajax: function(url, options = {}) {
                AdminPanel.showLoading();
                
                const defaults = {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                };
                
                const config = Object.assign(defaults, options);
                
                return fetch(url, config)
                    .then(response => {
                        AdminPanel.hideLoading();
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .catch(error => {
                        AdminPanel.hideLoading();
                        AdminPanel.showToast('An error occurred: ' + error.message, 'error');
                        throw error;
                    });
            }
        };

        // Initialize admin panel
        document.addEventListener('DOMContentLoaded', function() {
            AdminPanel.init();
        });

        // Global admin functions
        window.AdminPanel = AdminPanel;
    </script>

    <style>
        /* Additional Admin Styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: var(--shadow-lg);
        }

        .loading-spinner .spinner {
            width: 40px;
            height: 40px;
            margin: 0 auto 1rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            opacity: 1;
        }

        .modal-dialog {
            position: relative;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            transform: translateY(-50px);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-dialog {
            transform: translateY(0);
        }

        .modal-content {
            background: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--secondary-beige);
        }

        .modal-title {
            margin: 0;
            color: var(--navy-blue);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--medium-gray);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
            transition: all var(--transition-fast);
        }

        .modal-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: var(--navy-blue);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .toast-container {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 1060;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .toast {
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            padding: 1rem;
            min-width: 300px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .toast-success {
            border-left: 4px solid var(--success);
        }

        .toast-error {
            border-left: 4px solid var(--danger);
        }

        .toast-warning {
            border-left: 4px solid var(--warning);
        }

        .toast-info {
            border-left: 4px solid var(--info);
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--medium-gray);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all var(--transition-fast);
        }

        .toast-close:hover {
            background: var(--light-gray);
            color: var(--navy-blue);
        }

        .tooltip {
            position: absolute;
            background: var(--navy-blue);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 1070;
            opacity: 0;
            transform: translateY(5px);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: var(--navy-blue);
        }

        .tooltip.show {
            opacity: 1;
            transform: translateY(0);
        }

        .file-preview {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--light-gray);
            border-radius: 8px;
            text-align: center;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
            color: var(--medium-gray);
        }

        /* Table sorting indicators */
        .sort-asc::after {
            content: ' ↑';
            color: var(--primary-green);
        }

        .sort-desc::after {
            content: ' ↓';
            color: var(--primary-green);
        }

        /* Form validation styles */
        .was-validated .form-control:valid {
            border-color: var(--success);
        }

        .was-validated .form-control:invalid {
            border-color: var(--danger);
        }

        .valid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: var(--success);
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: var(--danger);
        }

        .was-validated .form-control:valid ~ .valid-feedback,
        .was-validated .form-control:invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</body>
</html>