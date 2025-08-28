// User Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const searchInput = document.querySelector('.search-input');
    const sortSelect = document.querySelector('.sort-select');
    const userTable = document.querySelector('.user-table tbody');
    const manageLinks = document.querySelectorAll('.action-link.manage');
    const removeLinks = document.querySelectorAll('.action-link.remove');

    // Store original table data for filtering/sorting
    let originalData = [];
    const rows = Array.from(userTable.querySelectorAll('tr'));
    
    // Extract and store table data
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        originalData.push({
            element: row,
            number: cells[0].textContent.trim(),
            regNumber: cells[1].textContent.trim(),
            name: cells[2].textContent.trim(),
            role: cells[3].textContent.trim()
        });
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        originalData.forEach(item => {
            const matchesSearch = 
                item.name.toLowerCase().includes(searchTerm) ||
                item.regNumber.toLowerCase().includes(searchTerm) ||
                item.role.toLowerCase().includes(searchTerm);
            
            if (matchesSearch) {
                item.element.style.display = '';
            } else {
                item.element.style.display = 'none';
            }
        });
        
        updateTableStripes();
    });

    // Sort functionality
    sortSelect.addEventListener('change', function() {
        const sortBy = this.value;
        let sortedData = [...originalData];
        
        switch(sortBy) {
            case 'name':
                sortedData.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'role':
                sortedData.sort((a, b) => a.role.localeCompare(b.role));
                break;
            case 'date':
                // Sort by registration number as proxy for date
                sortedData.sort((a, b) => a.regNumber.localeCompare(b.regNumber));
                break;
            case 'relevancy':
            default:
                // Keep original order
                break;
        }
        
        // Clear table and re-append sorted rows
        userTable.innerHTML = '';
        sortedData.forEach(item => {
            userTable.appendChild(item.element);
        });
        
        updateTableStripes();
    });

    // Update table row striping after filtering/sorting
    function updateTableStripes() {
        const visibleRows = Array.from(userTable.querySelectorAll('tr')).filter(row => 
            row.style.display !== 'none'
        );
        
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = index % 2 === 0 ? '#fafbfc' : '';
        });
    }

    // Manage user functionality
    manageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            const userName = row.querySelector('td:nth-child(3)').textContent;
            const regNumber = row.querySelector('td:nth-child(2)').textContent;
            
            showModal('Manage User', `
                <div class="modal-content">
                    <h3>Managing: ${userName}</h3>
                    <p><strong>Registration Number:</strong> ${regNumber}</p>
                    <div class="modal-actions">
                        <button class="btn btn-primary">Edit Profile</button>
                        <button class="btn btn-secondary">View Details</button>
                        <button class="btn btn-warning">Change Role</button>
                    </div>
                </div>
            `);
        });
    });

    // Remove user functionality
    removeLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            const userName = row.querySelector('td:nth-child(3)').textContent;
            
            if (confirm(`Are you sure you want to remove ${userName}? This action cannot be undone.`)) {
                // Add fade out animation
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '0';
                
                setTimeout(() => {
                    row.remove();
                    updateTableStripes();
                    showNotification('User removed successfully', 'success');
                }, 300);
            }
        });
    });

    // Modal functionality
    function showModal(title, content) {
        // Remove existing modal if any
        const existingModal = document.querySelector('.modal-overlay');
        if (existingModal) {
            existingModal.remove();
        }

        // Create modal
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        modalOverlay.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h2>${title}</h2>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        `;

        document.body.appendChild(modalOverlay);

        // Add modal styles if not already added
        if (!document.querySelector('#modal-styles')) {
            const modalStyles = document.createElement('style');
            modalStyles.id = 'modal-styles';
            modalStyles.textContent = `
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                }
                .modal {
                    background: white;
                    border-radius: 8px;
                    width: 90%;
                    max-width: 500px;
                    max-height: 80vh;
                    overflow-y: auto;
                }
                .modal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 20px;
                    border-bottom: 1px solid #eee;
                }
                .modal-header h2 {
                    margin: 0;
                    font-size: 18px;
                }
                .modal-close {
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    color: #666;
                }
                .modal-close:hover {
                    color: #333;
                }
                .modal-body {
                    padding: 20px;
                }
                .modal-actions {
                    display: flex;
                    gap: 10px;
                    margin-top: 20px;
                    flex-wrap: wrap;
                }
                .btn {
                    padding: 10px 16px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 14px;
                    transition: background-color 0.3s;
                }
                .btn-primary {
                    background-color: #3498db;
                    color: white;
                }
                .btn-primary:hover {
                    background-color: #2980b9;
                }
                .btn-secondary {
                    background-color: #95a5a6;
                    color: white;
                }
                .btn-secondary:hover {
                    background-color: #7f8c8d;
                }
                .btn-warning {
                    background-color: #f39c12;
                    color: white;
                }
                .btn-warning:hover {
                    background-color: #e67e22;
                }
            `;
            document.head.appendChild(modalStyles);
        }

        // Close modal functionality
        const closeBtn = modalOverlay.querySelector('.modal-close');
        closeBtn.addEventListener('click', () => {
            modalOverlay.remove();
        });

        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                modalOverlay.remove();
            }
        });
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        // Add notification styles if not already added
        if (!document.querySelector('#notification-styles')) {
            const notificationStyles = document.createElement('style');
            notificationStyles.id = 'notification-styles';
            notificationStyles.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 4px;
                    color: white;
                    font-weight: 500;
                    z-index: 1001;
                    animation: slideIn 0.3s ease;
                }
                .notification-success {
                    background-color: #27ae60;
                }
                .notification-error {
                    background-color: #e74c3c;
                }
                .notification-info {
                    background-color: #3498db;
                }
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(notificationStyles);
        }

        document.body.appendChild(notification);

        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 3000);
    }

    // Add smooth scrolling for navigation
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
            }
        });
    });

    // Add loading states for actions
    function showLoading(button) {
        const originalText = button.textContent;
        button.textContent = 'Loading...';
        button.disabled = true;
        
        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
        }, 1000);
    }

    // Initialize table striping
    updateTableStripes();
    
    console.log('User Management system initialized');
});