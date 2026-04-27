<?php
require_once __DIR__ . '/src/init.php';
requireLogin();

$userId = $_SESSION['user_id'];
$user = currentUser();

// Handle coupon AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'apply_coupon') {
    header('Content-Type: application/json');
    verifyCsrf();
    $code = sanitize($_POST['code'] ?? '');
    $subtotal = cartTotal();

    $coupon = Database::fetch(
        "SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) AND (usage_limit IS NULL OR used_count < usage_limit)",
        [strtoupper($code)]
    );

    if (!$coupon) { echo json_encode(['success'=>false,'message'=>'Invalid or expired coupon.']); exit; }
    if ($subtotal < $coupon['min_order']) {
        echo json_encode(['success'=>false,'message'=>"Minimum order " . formatPrice($coupon['min_order']) . " required."]); exit;
    }

    $discount = $coupon['type'] === 'percentage' ? round($subtotal * $coupon['value'] / 100, 2) : min($coupon['value'], $subtotal);
    $total = $subtotal - $discount;
    echo json_encode([
        'success' => true, 'message' => "Coupon applied! You save " . formatPrice($discount),
        'discount' => $discount, 'discount_formatted' => formatPrice($discount),
        'total' => $total, 'total_formatted' => formatPrice($total)
    ]);
    exit;
}

// Handle checkout POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    verifyCsrf();

    $paymentMethod = sanitize($_POST['payment_method'] ?? '');
    $transactionId = sanitize($_POST['transaction_id'] ?? '');
    $couponCode    = strtoupper(sanitize($_POST['coupon_code'] ?? ''));

    $allowed = ['bkash','nagad','ssl','bank','visa','mastercard'];
    if (!in_array($paymentMethod, $allowed)) { flash('danger','Invalid payment method.'); redirect(APP_URL . '/checkout.php'); }

    $items = cartItems();
    if (empty($items)) { flash('danger','Cart is empty.'); redirect(APP_URL . '/cart.php'); }

    $subtotal = cartTotal();
    $discount = 0;
    $couponId = null;

    if ($couponCode) {
        $coupon = Database::fetch(
            "SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) AND (usage_limit IS NULL OR used_count < usage_limit)",
            [$couponCode]
        );
        if ($coupon && $subtotal >= $coupon['min_order']) {
            $discount = $coupon['type'] === 'percentage' ? round($subtotal * $coupon['value'] / 100, 2) : min($coupon['value'], $subtotal);
            $couponId = $coupon['id'];
        }
    }

    $taxRate = (float)getSetting('tax_rate', '0');
    $tax = round(($subtotal - $discount) * $taxRate / 100, 2);
    $total = $subtotal - $discount + $tax;

    $orderNumber = generateOrderNumber();
    $expiryDays  = (int)getSetting('download_expiry_days', '30');
    $dlLimit     = (int)getSetting('download_limit', '5');

    // Create order
    $orderId = Database::insert(
        "INSERT INTO orders (user_id,order_number,subtotal,discount,tax,total,coupon_id,payment_method,transaction_id,payment_status,status) VALUES (?,?,?,?,?,?,?,?,?,'paid','completed')",
        [$userId, $orderNumber, $subtotal, $discount, $tax, $total, $couponId, $paymentMethod, $transactionId]
    );

    // Create order items with download tokens
    foreach ($items as $item) {
        $token = generateToken();
        $expiry = date('Y-m-d H:i:s', strtotime("+$expiryDays days"));
        Database::insert(
            "INSERT INTO order_items (order_id,product_id,price,download_token,download_limit,download_expiry) VALUES (?,?,?,?,?,?)",
            [$orderId, $item['product_id'], $item['price'], $token, $dlLimit, $expiry]
        );
        Database::execute("UPDATE products SET downloads = downloads + 1 WHERE id = ?", [$item['product_id']]);
    }

    // Update coupon usage
    if ($couponId) Database::execute("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?", [$couponId]);

    // Clear cart
    Database::execute("DELETE FROM cart_items WHERE user_id = ?", [$userId]);

    flash('success', "Order #$orderNumber placed successfully! Your downloads are ready.");
    redirect(APP_URL . '/orders.php?order=' . $orderId);
}

// Buy Now single product
$buyNow = (int)($_GET['buy_now'] ?? 0);
if ($buyNow) {
    $bp = Database::fetch("SELECT * FROM products WHERE id = ? AND status='active'", [$buyNow]);
    if ($bp) {
        Database::insert("INSERT IGNORE INTO cart_items (user_id, product_id) VALUES (?,?)", [$userId, $buyNow]);
    }
}

$items = cartItems();
$subtotal = cartTotal();
if (empty($items)) { flash('warning','Add products to cart first.'); redirect(APP_URL . '/products.php'); }

$taxRate = (float)getSetting('tax_rate', '0');
$tax = round($subtotal * $taxRate / 100, 2);

$pageTitle = 'Checkout — ' . getSetting('site_name', 'MBHaat.com');
include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container">
  <div style="padding:1.5rem 0 1rem">
    <h1>Checkout</h1>
    <p class="text-muted">Complete your purchase securely</p>
  </div>

  <form method="POST" action="">
    <?= csrfField() ?>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:2rem;align-items:start">
      <!-- Left: Payment -->
      <div style="display:flex;flex-direction:column;gap:1.5rem">

        <!-- Payment Method -->
        <div class="card">
          <div class="card-header"><h3 style="font-size:1.1rem">Payment Method</h3></div>
          <div class="card-body">
            <div class="payment-methods">
              <?php
              $methods = [
                'bkash'      => ['label'=>'bKash',       'icon'=>'📱', 'color'=>'#E2136E'],
                'nagad'      => ['label'=>'Nagad',        'icon'=>'📲', 'color'=>'#F6851B'],
                'ssl'        => ['label'=>'SSLCommerz',   'icon'=>'💳', 'color'=>'#008DC9'],
                'bank'       => ['label'=>'Bank Transfer','icon'=>'🏦', 'color'=>'#2E7D32'],
                'visa'       => ['label'=>'Visa',         'icon'=>'💳', 'color'=>'#1A1F71'],
                'mastercard' => ['label'=>'Mastercard',   'icon'=>'💳', 'color'=>'#EB001B'],
              ];
              foreach ($methods as $key => $method):
              ?>
              <label class="payment-option <?= $key === 'bkash' ? 'selected' : '' ?>">
                <input type="radio" name="payment_method" value="<?= $key ?>" <?= $key === 'bkash' ? 'checked' : '' ?>>
                <div class="payment-icon" style="font-size:1.75rem"><?= $method['icon'] ?></div>
                <div class="payment-option-name"><?= $method['label'] ?></div>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Transaction ID -->
        <div class="card">
          <div class="card-header"><h3 style="font-size:1.1rem">Payment Details</h3></div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Transaction ID / Reference</label>
              <input type="text" name="transaction_id" class="form-control" placeholder="Enter your transaction ID after payment" required>
              <div class="form-text">After making payment, enter the transaction reference number here.</div>
            </div>

            <div style="background:var(--surface2);border-radius:var(--radius-sm);padding:1rem;font-size:.85rem;color:var(--text-muted)">
              <strong style="color:var(--text)">Payment Instructions:</strong><br>
              1. Select your payment method above<br>
              2. Send payment to our account<br>
              3. Enter the transaction ID/reference<br>
              4. Submit order — access granted instantly
            </div>
          </div>
        </div>

        <!-- Coupon -->
        <div class="card">
          <div class="card-header"><h3 style="font-size:1.1rem">Coupon / Discount Code</h3></div>
          <div class="card-body">
            <input type="hidden" name="coupon_code" id="coupon-hidden" value="">
            <div style="display:flex;gap:.5rem">
              <input type="text" id="coupon-code" class="form-control" placeholder="Enter coupon code">
              <button type="button" id="apply-coupon" class="btn btn-secondary">Apply</button>
            </div>
            <div id="coupon-result" style="margin-top:.5rem"></div>
          </div>
        </div>
      </div>

      <!-- Right: Order Summary -->
      <div class="card card-body" style="position:sticky;top:80px">
        <h3 style="margin-bottom:1.25rem">Order Summary</h3>

        <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.9rem;margin-bottom:1rem">
          <?php foreach ($items as $item): ?>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:var(--text-muted);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-right:.5rem"><?= e($item['title']) ?></span>
            <span style="font-weight:600;flex-shrink:0"><?= formatPrice($item['price']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>

        <hr class="divider">

        <div style="display:flex;flex-direction:column;gap:.6rem;font-size:.9rem">
          <div style="display:flex;justify-content:space-between">
            <span class="text-muted">Subtotal</span>
            <span><?= formatPrice($subtotal) ?></span>
          </div>
          <div id="discount-row" style="display:none;justify-content:space-between;color:green">
            <span>Discount</span>
            <span>−<span id="discount-amount">৳0.00</span></span>
          </div>
          <?php if ($taxRate > 0): ?>
          <div style="display:flex;justify-content:space-between">
            <span class="text-muted">Tax (<?= $taxRate ?>%)</span>
            <span><?= formatPrice($tax) ?></span>
          </div>
          <?php endif; ?>
          <hr class="divider">
          <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem">
            <span>Total</span>
            <span style="color:var(--purple)" id="total-amount"><?= formatPrice($subtotal + $tax) ?></span>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
          🔒 Place Order
        </button>

        <div style="text-align:center;margin-top:.75rem;font-size:.75rem;color:var(--text-muted)">
          🔒 Secured by SSL encryption
        </div>
      </div>
    </div>
  </form>
</div>
</div>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
