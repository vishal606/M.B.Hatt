<?php
$pageTitle = 'Shopping Cart';
require_once '../includes/config.php';
include '../includes/header.php';

$cart = getCart();
$cartItems = [];
$subtotal = 0;

if (!empty($cart)) {
    foreach ($cart as $productId => $quantity) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.id = ? AND p.status = 'active'");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if ($product) {
            $product['quantity'] = $quantity;
            $product['total'] = ($product['sale_price'] ?? $product['price']) * $quantity;
            $cartItems[] = $product;
            $subtotal += $product['total'];
        }
    }
}

// Calculate totals
$taxRate = floatval(getSettings('tax_percentage') ?? 0);
$gstRate = floatval(getSettings('gst_percentage') ?? 0);
$taxAmount = $subtotal * ($taxRate / 100);
$gstAmount = $subtotal * ($gstRate / 100);
$total = $subtotal + $taxAmount + $gstAmount;
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">Shopping Cart</li>
        </ol>
    </nav>
    
    <h2 class="fw-bold text-brand-purple mb-4">
        <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
        <span class="badge bg-brand-blue text-brand-purple"><?php echo count($cartItems); ?> items</span>
    </h2>
    
    <?php if (empty($cartItems)): ?>
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
        <h3 class="text-muted">Your cart is empty</h3>
        <p class="text-muted mb-4">Looks like you haven't added any products to your cart yet.</p>
        <a href="products.php" class="btn btn-brand-blue btn-lg">
            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
        </a>
    </div>
    <?php else: ?>
    
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <?php foreach ($cartItems as $item): ?>
            <div class="cart-item mb-3">
                <div class="row align-items-center">
                    <div class="col-3 col-md-2">
                        <img src="<?php echo $item['thumbnail'] ? APP_URL . '/' . $item['thumbnail'] : 'https://via.placeholder.com/100?text=No+Image'; ?>" 
                             alt="<?php echo $item['name']; ?>" class="cart-item-image">
                    </div>
                    <div class="col-9 col-md-4">
                        <h5 class="fw-bold text-brand-purple mb-1" style="font-size: 1rem;">
                            <a href="product.php?slug=<?php echo $item['slug']; ?>" class="text-decoration-none text-brand-purple">
                                <?php echo substr($item['name'], 0, 40); ?><?php echo strlen($item['name']) > 40 ? '...' : ''; ?>
                            </a>
                        </h5>
                        <small class="text-muted"><?php echo $item['category_name'] ?? 'Uncategorized'; ?></small>
                    </div>
                    <div class="col-12 col-md-3 mt-3 mt-md-0">
                        <div class="quantity-selector justify-content-center justify-content-md-start">
                            <button class="quantity-btn minus" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo max(1, $item['quantity'] - 1); ?>)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" value="<?php echo $item['quantity']; ?>" class="form-control text-center" style="width: 50px;" readonly>
                            <button class="quantity-btn plus" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-8 col-md-2 mt-3 mt-md-0 text-md-end">
                        <span class="fw-bold text-brand-blue"><?php echo formatPrice($item['total']); ?></span>
                        <?php if ($item['quantity'] > 1): ?>
                        <small class="text-muted d-block"><?php echo formatPrice($item['sale_price'] ?? $item['price']); ?> each</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-4 col-md-1 mt-3 mt-md-0 text-end">
                        <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="btn btn-link text-danger p-0">
                            <i class="fas fa-trash-alt fa-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="products.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
                <button onclick="clearCart()" class="btn btn-outline-danger">
                    <i class="fas fa-trash me-2"></i>Clear Cart
                </button>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="fw-bold text-brand-purple mb-4">Order Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold"><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    
                    <?php if ($taxRate > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax (<?php echo $taxRate; ?>%)</span>
                        <span><?php echo formatPrice($taxAmount); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($gstRate > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">GST (<?php echo $gstRate; ?>%)</span>
                        <span><?php echo formatPrice($gstAmount); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Coupon Code -->
                    <div class="mt-3 mb-3">
                        <div class="input-group">
                            <input type="text" id="couponCode" class="form-control" placeholder="Enter coupon code">
                            <button class="btn btn-brand-blue" type="button" onclick="applyCoupon()">Apply</button>
                        </div>
                        <small id="couponMessage" class="form-text"></small>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5 fw-bold text-brand-purple">Total</span>
                        <span class="h5 fw-bold text-brand-blue" id="cartTotal"><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-brand-purple w-100 btn-lg">
                        <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                    </a>
                    
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="fas fa-lock me-1"></i>Secure SSL Encrypted Checkout
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<script>
function clearCart() {
    if (confirm('Are you sure you want to clear your cart?')) {
        fetch('ajax/cart.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'clear' })
        })
        .then(() => location.reload());
    }
}

function applyCoupon() {
    const code = document.getElementById('couponCode').value;
    const messageEl = document.getElementById('couponMessage');
    
    if (!code) {
        messageEl.textContent = 'Please enter a coupon code';
        messageEl.className = 'form-text text-danger';
        return;
    }
    
    fetch('ajax/coupon.php', {
        method: 'POST',
        body: new URLSearchParams({ code: code, total: <?php echo $subtotal; ?> })
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            messageEl.textContent = 'Coupon applied! Discount: ' + data.discount;
            messageEl.className = 'form-text text-success';
            // Update total with discount
        } else {
            messageEl.textContent = data.message || 'Invalid coupon code';
            messageEl.className = 'form-text text-danger';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
