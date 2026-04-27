<?php
require_once __DIR__ . '/src/init.php';

$user   = currentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name    = sanitize($_POST['name'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (strlen($name) < 2)    $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($subject) < 3) $errors[] = 'Please enter a subject.';
    if (strlen($message) < 10) $errors[] = 'Message is too short.';

    if (!$errors) {
        $ticketId = Database::insert(
            "INSERT INTO tickets (user_id, name, email, subject) VALUES (?,?,?,?)",
            [isLoggedIn() ? $_SESSION['user_id'] : null, $name, $email, $subject]
        );
        Database::insert(
            "INSERT INTO ticket_messages (ticket_id, sender, message) VALUES (?,?,?)",
            [$ticketId, 'user', $message]
        );
        flash('success', 'Your message has been sent! We\'ll get back to you soon. Ticket #' . $ticketId);
        redirect(APP_URL . '/contact.php');
    }
}

$pageTitle = 'Contact Support — ' . getSetting('site_name', 'MBHaat.com');
include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container">
  <div style="text-align:center;padding:2.5rem 0 2rem">
    <h1>Contact Support</h1>
    <p class="text-muted">We're here to help. Send us a message!</p>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;max-width:900px;margin:0 auto">
    <!-- Form -->
    <div class="card card-body">
      <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div>
      <?php endif; ?>

      <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label">Your Name</label>
          <input type="text" name="name" class="form-control" required value="<?= e($user['name'] ?? $_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required value="<?= e($user['email'] ?? $_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Subject</label>
          <input type="text" name="subject" class="form-control" required value="<?= e($_POST['subject'] ?? '') ?>" placeholder="e.g. Download issue, Payment problem...">
        </div>
        <div class="form-group">
          <label class="form-label">Message</label>
          <textarea name="message" class="form-control" rows="5" required placeholder="Describe your issue in detail..."><?= e($_POST['message'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Send Message 📨</button>
      </form>
    </div>

    <!-- Contact Info -->
    <div style="display:flex;flex-direction:column;gap:1.5rem">
      <?php
      $infos = [
        ['icon'=>'⚡','title'=>'Quick Response','desc'=>'We typically respond within 24 hours on business days.'],
        ['icon'=>'🔒','title'=>'Download Issues','desc'=>'If your download link has expired or hit its limit, mention your order number.'],
        ['icon'=>'💳','title'=>'Payment Problems','desc'=>'For payment issues, include your transaction ID and payment method.'],
        ['icon'=>'📋','title'=>'View My Tickets','desc'=>'<a href="' . APP_URL . '/tickets.php">Click here</a> to see all your support tickets.'],
      ];
      foreach ($infos as $info):
      ?>
      <div class="card card-body" style="display:flex;gap:1rem;align-items:flex-start">
        <span style="font-size:1.5rem;flex-shrink:0"><?= $info['icon'] ?></span>
        <div>
          <div style="font-weight:700;margin-bottom:.25rem"><?= $info['title'] ?></div>
          <div style="font-size:.85rem;color:var(--text-muted)"><?= $info['desc'] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
