<?php
require_once __DIR__ . '/src/init.php';
requireLogin();

$userId = $_SESSION['user_id'];
$pageTitle = 'My Tickets — ' . getSetting('site_name', 'MBHaat.com');

// View single ticket
if (isset($_GET['id'])) {
    $ticketId = (int)$_GET['id'];
    $ticket   = Database::fetch("SELECT * FROM tickets WHERE id=? AND user_id=?", [$ticketId, $userId]);
    if (!$ticket) { flash('danger','Ticket not found.'); redirect(APP_URL.'/tickets.php'); }
    $messages = Database::fetchAll("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY created_at", [$ticketId]);

    // Post reply
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $msg = sanitize($_POST['message'] ?? '');
        if (strlen($msg) >= 2) {
            Database::insert("INSERT INTO ticket_messages (ticket_id, sender, message) VALUES (?,?,?)", [$ticketId,'user',$msg]);
            flash('success','Reply sent.');
            redirect(APP_URL.'/tickets.php?id='.$ticketId);
        }
    }

    include __DIR__ . '/src/views/layouts/header.php';
    ?>
    <div class="page-body"><div class="container container-sm">
      <div style="padding:1.5rem 0 1rem;display:flex;align-items:center;gap:1rem">
        <a href="<?= APP_URL ?>/tickets.php" class="btn btn-secondary btn-sm">← Back</a>
        <div>
          <h2 style="font-size:1.25rem"><?= e($ticket['subject']) ?></h2>
          <span class="badge <?= $ticket['status']==='closed'?'badge-danger':($ticket['status']==='in_progress'?'badge-info':'badge-success') ?>"><?= strtoupper(str_replace('_',' ',$ticket['status'])) ?></span>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem">
        <?php foreach ($messages as $msg): ?>
        <div style="display:flex;<?= $msg['sender']==='admin'?'':'flex-direction:row-reverse' ?>">
          <div style="max-width:80%;background:<?= $msg['sender']==='admin'?'var(--surface2)':'var(--purple)' ?>;color:<?= $msg['sender']==='admin'?'var(--text)':'#fff' ?>;border-radius:var(--radius);padding:1rem;font-size:.9rem;line-height:1.7">
            <div style="font-size:.75rem;opacity:.7;margin-bottom:.35rem"><?= $msg['sender']==='admin'?'Support Team':'You' ?> · <?= timeAgo($msg['created_at']) ?></div>
            <?= nl2br(e($msg['message'])) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if ($ticket['status'] !== 'closed'): ?>
      <div class="card card-body">
        <form method="POST">
          <?= csrfField() ?>
          <div class="form-group">
            <label class="form-label">Add a Reply</label>
            <textarea name="message" class="form-control" rows="3" required placeholder="Type your message..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Send Reply</button>
        </form>
      </div>
      <?php else: ?>
      <div class="alert alert-info">This ticket is closed. <a href="<?= APP_URL ?>/contact.php">Open a new ticket</a> if needed.</div>
      <?php endif; ?>
    </div></div>
    <?php
    include __DIR__ . '/src/views/layouts/footer.php';
    exit;
}

$tickets = Database::fetchAll("SELECT * FROM tickets WHERE user_id=? ORDER BY created_at DESC", [$userId]);
include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body"><div class="container">
  <div style="padding:1.5rem 0 1rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
    <div>
      <h1>My Support Tickets</h1>
      <p class="text-muted">Track your support requests</p>
    </div>
    <a href="<?= APP_URL ?>/contact.php" class="btn btn-primary">+ New Ticket</a>
  </div>

  <?php if (empty($tickets)): ?>
  <div class="card card-body text-center" style="padding:3rem">
    <div style="font-size:3rem;margin-bottom:1rem">🎧</div>
    <h3>No tickets yet</h3>
    <p class="text-muted mt-1">Need help? Submit a support request.</p>
    <a href="<?= APP_URL ?>/contact.php" class="btn btn-primary mt-3">Contact Support</a>
  </div>
  <?php else: ?>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Subject</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($tickets as $t): ?>
          <tr>
            <td style="font-weight:600">#<?= $t['id'] ?></td>
            <td><?= e($t['subject']) ?></td>
            <td><span class="badge <?= $t['status']==='closed'?'badge-danger':($t['status']==='in_progress'?'badge-info':'badge-success') ?>"><?= strtoupper(str_replace('_',' ',$t['status'])) ?></span></td>
            <td><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
            <td><a href="?id=<?= $t['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div></div>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
