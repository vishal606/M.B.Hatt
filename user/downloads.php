<?php
$pageTitle = 'My Downloads';
require_once '../includes/config.php';
requireAuth();
include '../includes/header.php';

$userId = getUserId();

// Get all downloadable items
$stmt = $pdo->prepare("SELECT oi.*, o.order_number, o.created_at as order_date, o.order_status 
                      FROM order_items oi 
                      JOIN orders o ON oi.order_id = o.id 
                      WHERE o.user_id = ? AND o.order_status = 'completed'
                      ORDER BY o.created_at DESC");
$stmt->execute([$userId]);
$downloads = $stmt->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold text-brand-purple mb-4">
        <i class="fas fa-download me-2"></i>My Downloads
    </h3>
    
    <?php if (empty($downloads)): ?>
    <div class="text-center py-5">
        <i class="fas fa-download fa-4x text-muted mb-4"></i>
        <h4 class="text-muted">No downloads available</h4>
        <p class="text-muted mb-4">Your purchased products will appear here once payment is confirmed.</p>
        <a href="../pages/products.php" class="btn btn-brand-blue btn-lg">
            <i class="fas fa-shopping-bag me-2"></i>Browse Products
        </a>
    </div>
    <?php else: ?>
    
    <div class="row g-4">
        <?php foreach ($downloads as $item): 
            $expired = strtotime($item['download_expires']) < time();
            $remaining = $item['download_limit'] > 0 ? ($item['download_limit'] - $item['download_count']) : 'Unlimited';
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-brand-blue d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-file-archive text-white"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-brand-purple mb-0"><?php echo substr($item['product_name'], 0, 30); ?><?php echo strlen($item['product_name']) > 30 ? '...' : ''; ?></h6>
                            <small class="text-muted">Order: <?php echo $item['order_number']; ?></small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Purchased:</span>
                            <span><?php echo date('M d, Y', strtotime($item['order_date'])); ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Downloads:</span>
                            <span><?php echo $item['download_count']; ?> / <?php echo $remaining; ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Expires:</span>
                            <span><?php echo $item['download_expires'] ? date('M d, Y', strtotime($item['download_expires'])) : 'Never'; ?></span>
                        </div>
                    </div>
                    
                    <?php if (!$expired && ($item['download_limit'] === -1 || $item['download_count'] < $item['download_limit'])): ?>
                    <a href="download.php?token=<?php echo $item['download_token']; ?>" class="btn btn-brand-blue w-100">
                        <i class="fas fa-download me-2"></i>Download Now
                    </a>
                    <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>
                        <i class="fas fa-lock me-2"></i>Download Expired
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
