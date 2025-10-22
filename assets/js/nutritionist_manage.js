// Initialize Lucide icons
lucide.createIcons();

// Modal Management Functions
function openAddModal() {
    document.getElementById('addNutritionistModal').style.display = 'flex';
}

function closeAddModal() {
    document.getElementById('addNutritionistModal').style.display = 'none';
    document.querySelector('#addNutritionistModal form').reset();
}

function openEditModal(nutritionist) {
    document.getElementById('editNutritionistModal').style.display = 'flex';
    
    // Populate form fields
    document.getElementById('edit_nutritionist_id').value = nutritionist.nutritionist_id;
    document.getElementById('edit_name').value = nutritionist.name;
    document.getElementById('edit_email').value = nutritionist.email;
    document.getElementById('edit_phone').value = nutritionist.contact_no;
    document.getElementById('edit_specialization').value = nutritionist.specialization;
    document.getElementById('edit_experience').value = nutritionist.experience;
    document.getElementById('edit_certification').value = nutritionist.certification || '';
    document.getElementById('edit_qualifications').value = nutritionist.qualifications || '';
}

function closeEditModal() {
    document.getElementById('editNutritionistModal').style.display = 'none';
}

function closeViewModal() {
    const url = new URL(window.location.href);
    url.searchParams.delete('view');
    window.location.href = url.toString();
}

function confirmDelete(name) {
    return confirm(`Are you sure you want to delete ${name}? This action cannot be undone and will also delete their user account.`);
}

// Form Validation
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation for add form
    const addForm = document.querySelector('#addNutritionistModal form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const password = document.getElementById('add_password').value;
            const confirmPassword = document.getElementById('add_confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);

    // Search and sort functionality
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect && sortSelect.form) {
        sortSelect.addEventListener('change', function () {
            this.form.submit();
        });
    }

    // Search with debounce
    const searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.form) {
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchInput.form.submit();
            }, 400);
        });
    }
});

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addNutritionistModal');
    const editModal = document.getElementById('editNutritionistModal');
    const viewModal = document.getElementById('viewDetailsModal');
    
    if (event.target === addModal) closeAddModal();
    if (event.target === editModal) closeEditModal();
    if (event.target === viewModal) closeViewModal();
}

// Keyboard navigation for modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close any open modal
        const openModals = document.querySelectorAll('.modal[style*="display: block"], .modal[style*="display: flex"]');
        openModals.forEach(modal => {
            if (modal.id === 'addNutritionistModal') closeAddModal();
            if (modal.id === 'editNutritionistModal') closeEditModal();
            if (modal.id === 'viewDetailsModal') closeViewModal();
        });
    }
});