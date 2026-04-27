<?php
require_once __DIR__ . '/src/init.php';

$faqs = Database::fetchAll("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order,id");
$pageTitle = 'FAQ — ' . getSetting('site_name', 'MBHaat.com');
include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container container-sm">
  <div style="text-align:center;padding:2.5rem 0 2rem">
    <h1>Frequently Asked Questions</h1>
    <p class="text-muted">Find quick answers to the most common questions</p>
  </div>

  <div style="display:flex;flex-direction:column;gap:.75rem">
    <?php foreach ($faqs as $i => $faq): ?>
    <div class="card" style="overflow:visible">
      <button onclick="toggleFaq(<?= $i ?>)" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;padding:1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;font-family:var(--font-body)">
        <span style="font-weight:700;font-size:1rem;color:var(--text)"><?= e($faq['question']) ?></span>
        <span id="faq-icon-<?= $i ?>" style="font-size:1.2rem;flex-shrink:0;color:var(--purple);transition:transform 0.25s">+</span>
      </button>
      <div id="faq-body-<?= $i ?>" style="display:none;padding:0 1.25rem 1.25rem;color:var(--text-muted);line-height:1.8;font-size:.9rem">
        <?= nl2br(e($faq['answer'])) ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card card-body text-center mt-4">
    <p style="margin-bottom:1rem">Still have questions? We're happy to help!</p>
    <a href="<?= APP_URL ?>/contact.php" class="btn btn-primary">Contact Support</a>
  </div>
</div>
</div>

<script>
function toggleFaq(i) {
  const body = document.getElementById('faq-body-' + i);
  const icon = document.getElementById('faq-icon-' + i);
  const open = body.style.display !== 'none';
  body.style.display = open ? 'none' : 'block';
  icon.textContent   = open ? '+' : '−';
  icon.style.transform = open ? 'rotate(0deg)' : 'rotate(45deg)';
}
</script>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
