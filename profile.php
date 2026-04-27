<?php
require_once __DIR__ . '/src/init.php';
requireLogin();

$userId = $_SESSION['user_id'];
$user   = currentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = sanitize($_POST['action'] ?? 'profile');

    if ($action === 'profile') {
        $name  = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        if (strlen($name) < 2)  $errors[] = 'Name is too short.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (!$errors) {
            $exists = Database::fetch("SELECT id FROM users WHERE email=? AND id!=?", [$email, $userId]);
            if ($exists) $errors[] = 'Email already in use by another account.';
        }
        if (!$errors) {
            // Handle avatar upload
            $avatarPath = $user['avatar'];
            if (!empty($_FILES['avatar']['name'])) {
                $result = uploadFile($_FILES['avatar'], AVATAR_UPLOAD_PATH, ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
                if ($result['success']) $avatarPath = $result['filename'];
                else $errors[] = $result['error'];
            }
            if (!$errors) {
                Database::execute("UPDATE users SET name=?, email=?, avatar=? WHERE id=?", [$name, $email, $avatarPath, $userId]);
                $_SESSION['user_name'] = $name;
                flash('success', 'Profile updated successfully!');
                redirect(APP_URL . '/profile.php');
            }
        }
    }

    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $user['password'])) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($new !== $confirm) $errors[] = 'Passwords do not match.';
        if (!$errors) {
            $hash = password_hash($new, HASH_ALGO);
            Database::execute("UPDATE users SET password=? WHERE id=?", [$hash, $userId]);
            flash('success', 'Password changed successfully!');
            redirect(APP_URL . '/profile.php');
        }
    }
}

$user = currentUser(); // Refresh
$pageTitle = 'My Profile — ' . getSetting('site_name', 'MBHaat.com');
include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container container-sm">
  <div style="padding:1.5rem 0 1rem">
    <h1>My Profile</h1>
    <p class="text-muted">Manage your account information</p>
  </div>

  <?php if ($errors): ?>
  <div class="alert alert-danger"><?php foreach($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div>
  <?php endif; ?>

  <!-- Profile Info -->
  <div class="card mb-3">
    <div class="card-header"><h3 style="font-size:1.1rem">Personal Information</h3></div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="profile">

        <!-- Avatar -->
        <div style="display:flex;align-items:center;gap:1.5rem;margin-bottom:1.5rem">
          <div style="width:80px;height:80px;border-radius:50%;overflow:hidden;background:linear-gradient(135deg,var(--purple),var(--blue-dark));display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;font-weight:700;flex-shrink:0">
            <?php if ($user['avatar']): ?>
              <img src="<?= UPLOADS_URL ?>/avatars/<?= e($user['avatar']) ?>" id="avatar-preview" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
              <span id="avatar-initial"><?= strtoupper(substr($user['name'],0,1)) ?></span>
            <?php endif; ?>
          </div>
          <div>
            <label class="btn btn-secondary btn-sm" style="cursor:pointer">
              📷 Change Photo
              <input type="file" name="avatar" accept="image/*" style="display:none" data-preview="avatar-preview" onchange="previewAvatar(this)">
            </label>
            <div class="form-text">Max 5MB. JPG, PNG or WebP.</div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" required value="<?= e($user['name']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required value="<?= e($user['email']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>
  </div>

  <!-- Change Password -->
  <div class="card">
    <div class="card-header"><h3 style="font-size:1.1rem">Change Password</h3></div>
    <div class="card-body">
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="password">
        <div class="form-group">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" required placeholder="Minimum 6 characters">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
      </form>
    </div>
  </div>
</div>
</div>

<script>
function previewAvatar(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    let preview = document.getElementById('avatar-preview');
    const initial = document.getElementById('avatar-initial');
    if (!preview) {
      preview = document.createElement('img');
      preview.id = 'avatar-preview';
      preview.style.cssText = 'width:100%;height:100%;object-fit:cover';
      if (initial) initial.replaceWith(preview);
      else input.closest('.card-body').querySelector('[style*="border-radius:50%"]').appendChild(preview);
    }
    preview.src = e.target.result;
  };
  reader.readAsDataURL(input.files[0]);
}
</script>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
