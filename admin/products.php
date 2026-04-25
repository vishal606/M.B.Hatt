<?php
$pageTitle = 'Products';
include 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get products
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset");
$products = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalPages = ceil($total / $perPage);

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    // Get product file path to delete
    $productStmt = $pdo->prepare("SELECT file_path FROM products WHERE id = ?");
    $productStmt->execute([$deleteId]);
    $product = $productStmt->fetch();
    
    if ($product && file_exists('../' . $product['file_path'])) {
        unlink('../' . $product['file_path']);
    }
    
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$deleteId]);
    
    $_SESSION['flash_message'] = "Product deleted successfully";
    $_SESSION['flash_type'] = "success";
    redirect(APP_URL . '/admin/products.php');
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-brand-purple mb-0">Products Management</h4>
    <a href="product-edit.php" class="btn btn-brand-blue">
        <i class="fas fa-plus me-2"></i>Add Product
    </a>
</div>

<div class="card admin-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Downloads</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $product['thumbnail'] ? APP_URL . '/' . $product['thumbnail'] : 'https://via.placeholder.com/40?text=No+Img'; ?>" 
                                     alt="" class="rounded-3 me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <span class="fw-bold d-block"><?php echo substr($product['name'], 0, 30); ?><?php echo strlen($product['name']) > 30 ? '...' : ''; ?></span>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($product['created_at'])); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $product['category_name'] ?? 'Uncategorized'; ?></td>
                        <td>
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                <span class="fw-bold text-brand-blue"><?php echo formatPrice($product['sale_price']); ?></span>
                                <small class="text-muted text-decoration-line-through d-block"><?php echo formatPrice($product['price']); ?></small>
                            <?php else: ?>
                                <span class="fw-bold"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $product['status']; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($product['downloads']); ?></td>
                        <td>
                            <?php if ($product['featured']): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php else: ?>
                                <i class="far fa-star text-muted"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="../pages/product.php?slug=<?php echo $product['slug']; ?>" target="_blank" class="action-btn view" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="action-btn edit" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="products.php?delete=<?php echo $product['id']; ?>" class="action-btn delete" title="Delete" onclick="return confirmDelete()">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
        </li>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
