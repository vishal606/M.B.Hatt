<?php
$pageTitle = 'Users';
include 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get users
$stmt = $pdo->query("SELECT u.*, 
    (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND order_status = 'completed') as total_orders,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.id AND order_status = 'completed') as total_spent
    FROM users u ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset");
$users = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPages = ceil($total / $perPage);

// Handle block/unblock
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'block') {
        $pdo->prepare("UPDATE users SET status = 'blocked' WHERE id = ?")->execute([$userId]);
        $_SESSION['flash_message'] = "User blocked successfully";
    } elseif ($action === 'unblock') {
        $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$userId]);
        $_SESSION['flash_message'] = "User unblocked successfully";
    }
    
    $_SESSION['flash_type'] = "success";
    redirect(APP_URL . '/admin/users.php');
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-brand-purple mb-0">Users Management</h4>
</div>

<div class="card admin-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Spent</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-brand-purple text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                                <span class="fw-bold"><?php echo $user['name']; ?></span>
                            </div>
                        </td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'info'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($user['total_orders']); ?></td>
                        <td><?php echo formatPrice($user['total_spent']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $user['status']; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['role'] !== 'admin'): ?>
                                <?php if ($user['status'] === 'active'): ?>
                                <a href="users.php?action=block&id=<?php echo $user['id']; ?>" class="action-btn delete" title="Block" onclick="return confirm('Block this user?')">
                                    <i class="fas fa-ban"></i>
                                </a>
                                <?php else: ?>
                                <a href="users.php?action=unblock&id=<?php echo $user['id']; ?>" class="action-btn view" title="Unblock" onclick="return confirm('Unblock this user?')">
                                    <i class="fas fa-check"></i>
                                </a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="user-orders.php?user_id=<?php echo $user['id']; ?>" class="action-btn view" title="View Orders">
                                <i class="fas fa-shopping-cart"></i>
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
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
