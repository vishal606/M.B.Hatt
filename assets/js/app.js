/* MBHaat.com — Main JS */

// ── Dark Mode ─────────────────────────────────────────────────
(function() {
  const stored = localStorage.getItem('dark_mode') || document.cookie.match(/dark_mode=(\d)/)?.[1];
  if (stored === '1') document.body.classList.add('dark');
})();

function toggleDarkMode() {
  const isDark = document.body.classList.toggle('dark');
  localStorage.setItem('dark_mode', isDark ? '1' : '0');
  document.cookie = `dark_mode=${isDark ? 1 : 0};path=/;max-age=31536000`;
  const btn = document.querySelector('.dark-toggle');
  if (btn) btn.textContent = isDark ? '☀️' : '🌙';
}

// ── Toast Notifications ───────────────────────────────────────
function showToast(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = { success: '✅', danger: '❌', warning: '⚠️', info: 'ℹ️' };
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `<span>${icons[type] || icons.info}</span><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3500);
}

// ── Cart: Add to Cart (AJAX) ──────────────────────────────────
function addToCart(productId, btn) {
  if (!btn) return;
  btn.disabled = true;
  const orig = btn.innerHTML;
  btn.innerHTML = '<span class="spinner"></span>';

  fetch(APP_URL + '/cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `action=add&product_id=${productId}&csrf_token=${CSRF_TOKEN}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message || 'Added to cart!', 'success');
      const badge = document.querySelector('.cart-badge');
      if (badge) badge.textContent = data.cart_count;
      btn.innerHTML = '✓ Added';
      btn.classList.add('btn-accent');
    } else {
      showToast(data.message || 'Error adding to cart.', 'danger');
      btn.innerHTML = orig;
      btn.disabled = false;
    }
  })
  .catch(() => { showToast('Network error.', 'danger'); btn.innerHTML = orig; btn.disabled = false; });
}

// ── Cart: Remove ──────────────────────────────────────────────
function removeFromCart(productId) {
  if (!confirm('Remove this item from cart?')) return;
  fetch(APP_URL + '/cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `action=remove&product_id=${productId}&csrf_token=${CSRF_TOKEN}`
  })
  .then(r => r.json())
  .then(data => { if (data.success) location.reload(); else showToast('Error.', 'danger'); });
}

// ── Payment method selector ───────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', () => {
      document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
      option.classList.add('selected');
      option.querySelector('input[type=radio]').checked = true;
    });
  });

  // Coupon apply
  const couponBtn = document.getElementById('apply-coupon');
  if (couponBtn) {
    couponBtn.addEventListener('click', applyCoupon);
  }

  // Image preview for file inputs
  document.querySelectorAll('[data-preview]').forEach(input => {
    input.addEventListener('change', function() {
      const target = document.getElementById(this.dataset.preview);
      if (!target || !this.files[0]) return;
      const reader = new FileReader();
      reader.onload = e => { target.src = e.target.result; target.style.display = 'block'; };
      reader.readAsDataURL(this.files[0]);
    });
  });

  // Auto-dismiss flash messages
  setTimeout(() => {
    document.querySelectorAll('.flash-message').forEach(el => {
      el.style.transition = 'opacity 0.5s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    });
  }, 4000);

  // Hero Slider
  const heroSlides = document.querySelectorAll('.hero-slide');
  console.log('Hero slides found:', heroSlides.length);
  if (heroSlides.length > 1) {
    let heroIndex = 0;
    setInterval(() => {
      console.log('Changing slide from index:', heroIndex);
      heroSlides[heroIndex].classList.remove('active');
      heroIndex = (heroIndex + 1) % heroSlides.length;
      heroSlides[heroIndex].classList.add('active');
      console.log('New active slide index:', heroIndex);
    }, 5000);
  }
});

// ── Coupon ────────────────────────────────────────────────────
function applyCoupon() {
  const code = document.getElementById('coupon-code')?.value.trim();
  if (!code) return;
  const resultEl = document.getElementById('coupon-result');
  fetch(APP_URL + '/checkout.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `action=apply_coupon&code=${encodeURIComponent(code)}&csrf_token=${CSRF_TOKEN}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      if (resultEl) resultEl.innerHTML = `<span class="badge badge-success">✓ ${data.message}</span>`;
      document.getElementById('discount-row').style.display = '';
      document.getElementById('discount-amount').textContent = data.discount_formatted;
      document.getElementById('total-amount').textContent = data.total_formatted;
      document.getElementById('coupon-hidden').value = code;
    } else {
      if (resultEl) resultEl.innerHTML = `<span class="badge badge-danger">✗ ${data.message}</span>`;
    }
  });
}

// ── Product Screenshots Slider ────────────────────────────────
let currentSlide = 0;
function changeSlide(dir) {
  const slides = document.querySelectorAll('.product-slide');
  if (!slides.length) return;
  slides[currentSlide].classList.remove('active');
  currentSlide = (currentSlide + dir + slides.length) % slides.length;
  slides[currentSlide].classList.add('active');
}

// ── Confirm delete ────────────────────────────────────────────
function confirmDelete(msg, form) {
  if (confirm(msg || 'Are you sure you want to delete this?')) {
    if (form) document.getElementById(form)?.submit();
    return true;
  }
  return false;
}

// ── Admin: toggle product status ──────────────────────────────
function toggleStatus(id, type) {
  fetch(APP_URL + '/admin/ajax.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `action=toggle_status&id=${id}&type=${type}&csrf_token=${CSRF_TOKEN}`
  })
  .then(r => r.json())
  .then(data => { if (data.success) location.reload(); else showToast('Error.', 'danger'); });
}
