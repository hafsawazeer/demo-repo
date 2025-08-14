// DOM Elements
const form = document.querySelector('.checkout-forms');
const emailInput = document.querySelector('input[type="email"]');
const continueBtn = document.querySelector('.continue-btn');
const saveInfoCheckbox = document.querySelector('#save-info');
const cartCount = document.querySelector('.cart-count');
const notificationCount = document.querySelector('.notification-count');

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    initializeCounters();
    setupFormValidation();
    setupInteractiveElements();
    loadSavedInformation();
});

// Initialize counters
function initializeCounters() {
    // Set cart count based on items in checkout
    cartCount.textContent = '2';
    notificationCount.textContent = '1';
}

// Form validation
function setupFormValidation() {
    const formInputs = document.querySelectorAll('.form-input');
    
    formInputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearErrors);
    });
    
    continueBtn.addEventListener('click', handleFormSubmit);
}

function validateField(event) {
    const field = event.target;
    const value = field.value.trim();
    
    // Remove existing error styling
    field.classList.remove('error');
    removeErrorMessage(field);
    
    // Email validation
    if (field.type === 'email' && value) {
        if (!isValidEmail(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Phone number validation
    if (field.placeholder.toLowerCase().includes('phone') && value) {
        if (!isValidPhone(value)) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
    }
    
    return true;
}

function clearErrors(event) {
    const field = event.target;
    field.classList.remove('error');
    removeErrorMessage(field);
}

function showFieldError(field, message) {
    field.classList.add('error');
    
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    // Insert error message after the field
    field.parentNode.insertBefore(errorDiv, field.nextSibling);
}

function removeErrorMessage(field) {
    const errorMessage = field.parentNode.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
}

// Handle form submission
function handleFormSubmit(event) {
    event.preventDefault();
    
    const formInputs = document.querySelectorAll('.form-input');
    let isValid = true;
    
    // Validate all fields
    formInputs.forEach(input => {
        if (!validateField({ target: input })) {
            isValid = false;
        }
    });
    
    // Check required fields
    const requiredFields = [
        document.querySelector('input[type="email"]'),
        document.querySelector('input[placeholder="First Name"]'),
        document.querySelector('input[placeholder="Last Name"]'),
        document.querySelector('input[placeholder="Address"]'),
        document.querySelector('input[placeholder="City"]')
    ];
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });
    
    if (isValid) {
        // Save information if checkbox is checked
        if (saveInfoCheckbox.checked) {
            saveFormInformation();
        }
        
        // Show success message
        showSuccessMessage();
        
        // Simulate form submission
        setTimeout(() => {
            window.location.href = '#shipping'; // Redirect to shipping page
        }, 2000);
    } else {
        // Scroll to first error
        const firstError = document.querySelector('.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

// Save form information to localStorage
function saveFormInformation() {
    const formData = {
        email: document.querySelector('input[type="email"]').value,
        firstName: document.querySelector('input[placeholder="First Name"]').value,
        lastName: document.querySelector('input[placeholder="Last Name"]').value,
        address: document.querySelector('input[placeholder="Address"]').value,
        apartment: document.querySelector('input[placeholder*="Apartment"]').value,
        city: document.querySelector('input[placeholder="City"]').value,
        postalCode: document.querySelector('input[placeholder*="Postal"]').value,
        phone: document.querySelector('input[placeholder="Phone number"]').value
    };
    
    localStorage.setItem('fitverseCheckoutData', JSON.stringify(formData));
}

// Load saved information from localStorage
function loadSavedInformation() {
    const savedData = localStorage.getItem('fitverseCheckoutData');
    if (savedData) {
        const data = JSON.parse(savedData);
        
        document.querySelector('input[type="email"]').value = data.email || '';
        document.querySelector('input[placeholder="First Name"]').value = data.firstName || '';
        document.querySelector('input[placeholder="Last Name"]').value = data.lastName || '';
        document.querySelector('input[placeholder="Address"]').value = data.address || '';
        document.querySelector('input[placeholder*="Apartment"]').value = data.apartment || '';
        document.querySelector('input[placeholder="City"]').value = data.city || '';
        document.querySelector('input[placeholder*="Postal"]').value = data.postalCode || '';
        document.querySelector('input[placeholder="Phone number"]').value = data.phone || '';
        
        saveInfoCheckbox.checked = true;
    }
}

// Setup interactive elements
function setupInteractiveElements() {
    // Cart icon click
    const cartIcon = document.querySelector('.cart-icon');
    cartIcon.addEventListener('click', () => {
        alert('Cart functionality would open here');
    });
    
    // Notifications click
    const notifications = document.querySelector('.notifications');
    notifications.addEventListener('click', () => {
        alert('Notifications panel would open here');
    });
    
    // Profile click
    const profile = document.querySelector('.profile');
    profile.addEventListener('click', () => {
        alert('Profile menu would open here');
    });
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const searchTerm = e.target.value.trim();
            if (searchTerm) {
                alert(`Searching for: ${searchTerm}`);
                // Implement actual search functionality here
            }
        }
    });
    
    // Navigation links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const linkText = link.textContent;
            alert(`Navigating to ${linkText} page`);
        });
    });
    
    // Footer links
    const footerLinks = document.querySelectorAll('.footer-link');
    footerLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const linkText = link.textContent;
            alert(`Opening ${linkText} page`);
        });
    });
}

// Show success message
function showSuccessMessage() {
    // Create success overlay
    const overlay = document.createElement('div');
    overlay.className = 'success-overlay';
    overlay.innerHTML = `
        <div class="success-message">
            <div class="success-icon">✓</div>
            <h3>Information Saved!</h3>
            <p>Redirecting to shipping options...</p>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    // Remove overlay after animation
    setTimeout(() => {
        overlay.remove();
    }, 2000);
}

// Add dynamic styles for error states and success overlay
const dynamicStyles = document.createElement('style');
dynamicStyles.textContent = `
    .form-input.error {
        border-color: #e74c3c !important;
        background-color: #fdf2f2 !important;
    }
    
    .error-message {
        color: #e74c3c;
        font-size: 12px;
        margin-top: -10px;
        margin-bottom: 10px;
        padding-left: 5px;
    }
    
    .success-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        animation: fadeIn 0.3s ease-in;
    }
    
    .success-message {
        background-color: white;
        padding: 40px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease-out;
    }
    
    .success-icon {
        width: 60px;
        height: 60px;
        background-color: #CB8B2B;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        font-weight: bold;
        margin: 0 auto 20px;
    }
    
    .success-message h3 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .success-message p {
        color: #666;
        margin: 0;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .form-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(203, 139, 43, 0.2);
    }
    
    .continue-btn:active {
        transform: translateY(1px);
    }
    
    .nav-link:active,
    .cart-icon:active,
    .notifications:active,
    .profile:active {
        transform: scale(0.95);
    }
`;

document.head.appendChild(dynamicStyles);

// Add smooth scrolling for better UX
document.documentElement.style.scrollBehavior = 'smooth';