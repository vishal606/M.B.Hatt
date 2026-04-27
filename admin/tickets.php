<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();
$pageTitle = 'Support Tickets — Admin';

// View & reply
if (isset($_GET['view'])) {
    $ticketId = (int)$_GET['view'];
    $ticket   = Database::fetch("SELECT * FROM tickets WHERE id=?", [$ticketId]);
    if (!$ticket) { flash('danger','Not found.'); redirect(APP_URL.'/admin/tickets.php'); }
    $messages = Database::fetchAll("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY created_at", [$ticketId]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        if (isset($_POST['reply'])) {
            $msg = sanitize($_POST['message'] ?? '');
            if (strlen($msg) >= 2) {
                Database::insert("INSERT INTO ticket_messages (ticket_id,sender,message) VALUES (?,?,?)", [$ticketId,'admin',$msg]);
                Database::execute("UPDATE tickets SET status='in_progress' WHERE id=? AND status='open'", [$ticketId]);
                flash('success','Reply sent.'); redirect(APP_URL.'/admin/tickets.php?view='.$ticketId);
            }
        }
        if (isset($_POST['close'])) {
            Database::execute("UPDATE tickets SET status='closed' WHERE id=?", [$ticketId]);
            flash('success','Ticket closed.'); redirect(APP_URL.'/admin/tickets.php');
        }
    }

    include __DIR__ . '/partials/header.php';
    ?>
    <div class="admin-page-header">
      <div>
        <h1 style="font-size:1.3rem"><?= e($ticket['subject']) ?></h1>
        <span class="badge <?= $ticket['status']==='closed'?'badge-danger':($ticket['status']==='in_progress'?'badge-info':'badge-success') ?>"><?= strtoupper(str_replace('_',' ',$ticket['status'])) ?></span>
        <span class="text-small text-muted" style="margin-left:.5rem">From: <?= e($ticket['name']) ?> (<?= e($ticket['email']) ?>)</span>
      </div>
      <a href="<?= APP_URL ?>/admin/tickets.php" class="btn btn-secondary">← Back</a>
    </div>

    <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem">
      <?php foreach ($messages as $msg): ?>
      <div style="display:flex;<?= $msg['sender']==='user'?'':'flex-direction:row-reverse' ?>">
        <div style="max-width:75%;background:<?= $msg['sender']==='user'?'var(--surface2)':'var(--purple)' ?>;color:<?= $msg['sender']==='user'?'var(--text)':'#fff' ?>;border-radius:var(--radius);padding:1rem">
          <div style="font-size:.75rem;opacity:.7;margin-bottom:.35rem"><?= $msg['sender']==='user'?e($ticket['name']):'Admin' ?> · <?= timeAgo($msg['created_at']) ?></div>
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
          <label class="form-label">Admin Reply</label>
          <textarea name="message" class="form-control" rows="3" required placeholder="Type your reply..."></textarea>
        </div>
        <div style="display:flex;gap:.75rem">
          <button type="submit" name="reply" class="btn btn-primary">Send Reply</button>
          <button type="submit" name="close" class="btn btn-danger" onclick="return confirm('Close this ticket?')">Close Ticket</button>
        </div>
      </form>
    </div>
    <?php else: ?>
    <div class="alert alert-info">This ticket is closed.</div>
    <?php endif; ?>
    <?php
    include __DIR__ . '/partials/footer.php';
    exit;
}

$status = sanitize($_GET['status'] ?? '');
$where  = ['1=1']; $params = [];
if ($status) { $where[]="status=?"; $params[]=$status; }
$tickets = Database::fetchAll("SELECT * FROM tickets WHERE " . implode(' AND ',$where) . " ORDER BY created_at DESC", $params);

include __DIR__ . '/partials/header.php';
?>

<div class="admin-page-header">
  <h1>Support Tickets <span class="badge badge-purple"><?= count($tickets) ?></span></h1>
</div>

<div class="admin-filters">
  <form method="GET" style="display:flex;gap:.5rem">
    <select name="status" class="form-control" onchange="this.form.submit()">
      <option value="">All Status</option>
      <?php foreach (['open','in_progress','closed'] as $s): ?>
      <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select>
    <a href="<?= APP_URL ?>/admin/tickets.php" class="btn btn-secondary">Reset</a>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Name</th><th>Subject</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
        <tr>
          <td>#<?= $t['id'] ?></td>
          <td>
            <div style="font-weight:600"><?= e($t['name']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted)"><?= e($t['email']) ?></div>
          </td>
          <td><?= e($t['subject']) ?></td>
          <td><span class="badge <?= $t['status']==='closed'?'badge-danger':($t['status']==='in_progress'?'badge-info':'badge-success') ?>"><?= strtoupper(str_replace('_',' ',$t['status'])) ?></span></td>
          <td style="font-size:.85rem"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
          <td><a href="?view=<?= $t['id'] ?>" class="btn btn-secondary btn-sm">Manage</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($tickets)): ?><tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">No tickets found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
