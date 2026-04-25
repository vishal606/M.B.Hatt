<?php
$pageTitle = 'Checkout';
require_once '../includes/config.php';
requireAuth();
include '../includes/header.php';

// Get cart items
$cart = getCart();
$cartItems = [];
$subtotal = 0;

// Handle buy now
if (isset($_GET['buy_now'])) {
    $buyNowId = intval($_GET['buy_now']);
    $stmt = $pdo->prepare("SELECT p.* FROM products p WHERE p.id = ? AND p.status = 'active'");
    $stmt->execute([$buyNowId]);
    $product = $stmt->fetch();
    
    if ($product) {
        $cartItems = [[
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['sale_price'] ?? $product['price'],
            'quantity' => 1,
            'total' => $product['sale_price'] ?? $product['price']
        ]];
        $subtotal = $product['sale_price'] ?? $product['price'];
    }
} elseif (!empty($cart)) {
    foreach ($cart as $productId => $quantity) {
        $stmt = $pdo->prepare("SELECT p.* FROM products p WHERE p.id = ? AND p.status = 'active'");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if ($product) {
            $cartItems[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['sale_price'] ?? $product['price'],
                'quantity' => $quantity,
                'total' => ($product['sale_price'] ?? $product['price']) * $quantity
            ];
            $subtotal += ($product['sale_price'] ?? $product['price']) * $quantity;
        }
    }
}

if (empty($cartItems)) {
    $_SESSION['flash_message'] = "Your cart is empty";
    $_SESSION['flash_type'] = "warning";
    redirect(APP_URL . '/pages/products.php');
}

// Calculate totals
$taxRate = floatval(getSettings('tax_enabled') && getSettings('tax_percentage') ? getSettings('tax_percentage') : 0);
$gstRate = floatval(getSettings('gst_enabled') && getSettings('gst_percentage') ? getSettings('gst_percentage') : 0);
$taxAmount = $subtotal * ($taxRate / 100);
$gstAmount = $subtotal * ($gstRate / 100);
$total = $subtotal + $taxAmount + $gstAmount;

// Get payment gateways
$gatewayStmt = $pdo->query("SELECT * FROM payment_gateways WHERE is_active = 1 ORDER BY sort_order");
$gateways = $gatewayStmt->fetchAll();

// Get user details
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([getUserId()]);
$user = $userStmt->fetch();

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $billingName = sanitizeInput($_POST['billing_name'] ?? $user['name']);
    $billingEmail = sanitizeInput($_POST['billing_email'] ?? $user['email']);
    $billingPhone = sanitizeInput($_POST['billing_phone'] ?? $user['phone'] ?? '');
    $billingAddress = sanitizeInput($_POST['billing_address'] ?? '');
    $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');
    $couponCode = sanitizeInput($_POST['coupon_code'] ?? '');
    
    // Validate
    if (empty($paymentMethod)) {
        $error = "Please select a payment method";
    } else {
        // Check coupon
        $discount = 0;
        if (!empty($couponCode)) {
            $couponStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND (end_date IS NULL OR end_date >= CURDATE())");
            $couponStmt->execute([$couponCode]);
            $coupon = $couponStmt->fetch();
            
            if ($coupon && ($coupon['usage_limit'] === null || $coupon['usage_count'] < $coupon['usage_limit'])) {
                if ($coupon['type'] === 'percentage') {
                    $discount = $subtotal * ($coupon['value'] / 100);
                    if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
                        $discount = $coupon['max_discount'];
                    }
                } else {
                    $discount = $coupon['value'];
                }
                
                // Update coupon usage
                $pdo->prepare("UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?")->execute([$coupon['id']]);
            }
        }
        
        $finalTotal = max(0, $total - $discount);
        $orderNumber = 'MBH' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        
        // Create order
        $orderStmt = $pdo->prepare("INSERT INTO orders 
            (order_number, user_id, coupon_id, coupon_discount, subtotal, tax_amount, total_amount, 
             payment_method, billing_name, billing_email, billing_phone, billing_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $orderStmt->execute([
            $orderNumber, getUserId(), $coupon['id'] ?? null, $discount, $subtotal, 
            $taxAmount + $gstAmount, $finalTotal, $paymentMethod,
            $billingName, $billingEmail, $billingPhone, $billingAddress
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Create order items
        foreach ($cartItems as $item) {
            $downloadToken = generateToken(32);
            $downloadExpires = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            $itemStmt = $pdo->prepare("INSERT INTO order_items 
                (order_id, product_id, product_name, product_price, quantity, download_token, download_expires) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $itemStmt->execute([
                $orderId, $item['id'], $item['name'], $item['price'], 
                $item['quantity'], $downloadToken, $downloadExpires
            ]);
        }
        
        // Clear cart
        clearCart();
        
        // Redirect to payment processing
        if (in_array($paymentMethod, ['bkash', 'nagad', 'sslcommerz'])) {
            redirect(APP_URL . '/payment/process.php?order_id=' . $orderId . '&method=' . $paymentMethod);
        } else {
            // For manual methods, mark as pending
            redirect(APP_URL . '/pages/order-success.php?order_id=' . $orderNumber);
        }
    }
}
?>

<div class="container py-4">
    <h2 class="fw-bold text-brand-purple mb-4">
        <i class="fas fa-credit-card me-2"></i>Checkout
    </h2>
    
    <div class="row">
        <!-- Checkout Form -->
        <div class="col-lg-8">
            <div class="checkout-section mb-4">
                <form method="POST" action="">
                    <!-- Billing Information -->
                    <h5 class="fw-bold text-brand-purple mb-3">
                        <i class="fas fa-user me-2"></i>Billing Information
                    </h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="billing_name" class="form-control" value="<?php echo $user['name']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="billing_email" class="form-control" value="<?php echo $user['email']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="billing_phone" class="form-control" value="<?php echo $user['phone'] ?? ''; ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="billing_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <h5 class="fw-bold text-brand-purple mb-3">
                        <i class="fas fa-wallet me-2"></i>Payment Method
                    </h5>
                    
                    <div class="row g-3 mb-4">
                        <?php foreach ($gateways as $gateway): ?>
                        <div class="col-md-6">
                            <label class="payment-method w-100 <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === $gateway['code'] ? 'active' : ''; ?>">
                                <input type="radio" name="payment_method" value="<?php echo $gateway['code']; ?>" 
                                       class="form-check-input d-none" required
                                       <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === $gateway['code'] ? 'checked' : ''; ?>>
                                <i class="fas fa-<?php 
                                    echo $gateway['code'] === 'bkash' ? 'mobile-alt' : 
                                         ($gateway['code'] === 'nagad' ? 'money-bill-wave' : 
                                         ($gateway['code'] === 'sslcommerz' ? 'credit-card' : 'university')); ?>"></i>
                                <div>
                                    <span class="fw-bold d-block"><?php echo $gateway['name']; ?></span>
                                    <small class="text-muted">
                                        <?php echo $gateway['code'] === 'bkash' ? 'Pay with bKash' : 
                                                 ($gateway['code'] === 'nagad' ? 'Pay with Nagad' : 'Secure online payment'); ?>
                                    </small>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Coupon -->
                    <div class="mb-4">
                        <label class="form-label">Coupon Code (Optional)</label>
                        <div class="input-group">
                            <input type="text" name="coupon_code" class="form-control" placeholder="Enter coupon code">
                            <button type="button" class="btn btn-brand-blue" onclick="validateCoupon()">Apply</button>
                        </div>
                        <small id="couponStatus" class="form-text"></small>
                    </div>
                    
                    <button type="submit" class="btn btn-brand-purple btn-lg w-100">
                        <i class="fas fa-lock me-2"></i>Complete Order - <?php echo formatPrice($total); ?>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="fw-bold text-brand-purple mb-4">Order Summary</h5>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <span class="d-block"><?php echo substr($item['name'], 0, 30); ?><?php echo strlen($item['name']) > 30 ? '...' : ''; ?></span>
                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                        </div>
                        <span><?php echo formatPrice($item['total']); ?></span>
                    </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    
                    <?php if ($taxRate > 0 || $gstRate > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax & GST</span>
                        <span><?php echo formatPrice($taxAmount + $gstAmount); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div id="discountRow" class="d-flex justify-content-between mb-2 d-none">
                        <span class="text-muted">Discount</span>
                        <span class="text-success" id="discountAmount">-<?php echo formatPrice(0); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5 fw-bold text-brand-purple">Total</span>
                        <span class="h5 fw-bold text-brand-blue" id="finalTotal"><?php echo formatPrice($total); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateCoupon() {
    const code = document.querySelector('input[name="coupon_code"]').value;
    const statusEl = document.getElementById('couponStatus');
    
    if (!code) {
        statusEl.textContent = 'Please enter a coupon code';
        statusEl.className = 'form-text text-danger';
        return;
    }
    
    fetch('ajax/coupon.php', {
        method: 'POST',
        body: new URLSearchParams({ code: code, total: <?php echo $subtotal; ?> })
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            statusEl.textContent = 'Coupon applied! You saved ' + data.discount_formatted;
            statusEl.className = 'form-text text-success';
            document.getElementById('discountRow').classList.remove('d-none');
            document.getElementById('discountAmount').textContent = '-' + data.discount_formatted;
            document.getElementById('finalTotal').textContent = data.new_total_formatted;
        } else {
            statusEl.textContent = data.message || 'Invalid coupon code';
            statusEl.className = 'form-text text-danger';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
