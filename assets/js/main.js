/**
 * MBHaat.com - Main JavaScript File
 */

// Cart Functions
function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('action', 'add');
    
    fetch('ajax/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cartCount);
            showToast('Product added to cart!', 'success');
        } else {
            showToast(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error adding to cart', 'error');
    });
}

function removeFromCart(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'remove');
    
    fetch('ajax/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cartCount);
            location.reload(); // Reload to update cart display
        }
    });
}

function updateQuantity(productId, quantity) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('action', 'update');
    
    fetch('ajax/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function updateCartCount(count) {
    const badges = document.querySelectorAll('#cart-count, .badge');
    badges.forEach(badge => {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    });
}

// Toast Notifications
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-mdb-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    const bsToast = new mdb.Toast(toast);
    bsToast.show();
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Product Search with Debounce
let searchTimeout;
function debounceSearch(func, wait) {
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(searchTimeout);
            func(...args);
        };
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(later, wait);
    };
}

// Image Lazy Loading
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Form Validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    return isValid;
}

// Password Strength Checker
function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/\d/)) strength++;
    if (password.match(/[^a-zA-Z\d]/)) strength++;
    return strength;
}

function updatePasswordStrength(password) {
    const strength = checkPasswordStrength(password);
    const meter = document.getElementById('password-strength');
    if (!meter) return;
    
    const colors = ['#DA4848', '#DA4848', '#F7F6E5', '#76D2DB', '#36064D'];
    const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    
    meter.style.width = `${(strength + 1) * 20}%`;
    meter.style.backgroundColor = colors[strength];
    meter.textContent = texts[strength];
}

// Copy to Clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    });
}

// Countdown Timer for Downloads
function startDownloadTimer(element, seconds) {
    let remaining = seconds;
    const interval = setInterval(() => {
        remaining--;
        element.textContent = `Download available in ${remaining}s`;
        
        if (remaining <= 0) {
            clearInterval(interval);
            element.style.display = 'none';
            document.getElementById('download-btn').disabled = false;
        }
    }, 1000);
}

// Smooth Scroll
function smoothScroll(target) {
    document.querySelector(target).scrollIntoView({
        behavior: 'smooth'
    });
}

// File Size Formatter
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize lazy loading
    lazyLoadImages();
    
    // Initialize MDB components
    document.querySelectorAll('[data-mdb-toggle="tooltip"]').forEach(el => {
        new mdb.Tooltip(el);
    });
    
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', (e) => {
            updatePasswordStrength(e.target.value);
        });
    }
    
    // Form validation
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!validateForm(form)) {
                e.preventDefault();
                showToast('Please fill in all required fields', 'error');
            }
        });
    });
    
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('active'));
            this.classList.add('active');
            
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });
    
    // Quantity buttons
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input[type="number"]');
            const currentVal = parseInt(input.value);
            
            if (this.classList.contains('minus') && currentVal > 1) {
                input.value = currentVal - 1;
            } else if (this.classList.contains('plus')) {
                input.value = currentVal + 1;
            }
        });
    });
});

// Mobile Menu Toggle
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('show');
}

// Print Order
function printOrder(orderId) {
    window.open(`print/order.php?id=${orderId}`, '_blank', 'width=800,height=600');
}

// Share Product
function shareProduct(productId, title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: window.location.href
        });
    } else {
        copyToClipboard(window.location.href);
    }
}
