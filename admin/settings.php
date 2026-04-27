<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();
$pageTitle = 'Settings — Admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $fields = [
        'site_name','site_tagline','currency','currency_symbol',
        'tax_rate','vat_rate',
        'bkash_number','nagad_number',
        'ssl_merchant_id','ssl_merchant_pass','ssl_store_id',
        'bank_account','bank_name','bank_routing',
        'download_limit','download_expiry_days',
        'smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from',
        'footer_text','dark_mode_default','maintenance_mode',
    ];
    foreach ($fields as $key) {
        $val = sanitize($_POST[$key] ?? '');
        Database::execute(
            "INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?",
            [$key, $val, $val]
        );
    }

    // Logo upload
    if (!empty($_FILES['logo']['name'])) {
        $res = uploadFile($_FILES['logo'], ROOT_PATH . '/assets/images', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($res['success']) {
            $logoVal = 'assets/images/' . $res['filename'];
            Database::execute(
                "INSERT INTO settings (setting_key,setting_value) VALUES ('logo',?) ON DUPLICATE KEY UPDATE setting_value=?",
                [$logoVal, $logoVal]
            );
        } else {
            flash('danger', 'Logo upload failed: ' . $res['error']);
        }
    }

    flash('success', 'Settings saved successfully!');
    redirect(APP_URL . '/admin/settings.php');
}

$s = getAllSettings();
include __DIR__ . '/partials/header.php';
?>

<div class="admin-page-header">
  <h1>Site Settings</h1>
</div>

<form method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>

  <!-- Tabs -->
  <div style="display:flex;gap:.25rem;margin-bottom:1.5rem;flex-wrap:wrap" id="settings-tabs">
    <?php
    $tabs = ['general'=>'🌐 General','payment'=>'💳 Payment','download'=>'⬇️ Downloads','email'=>'📧 Email','branding'=>'🎨 Branding'];
    $i = 0;
    foreach ($tabs as $tab => $label):
    ?>
    <button type="button" class="btn <?= $i===0?'btn-primary':'btn-secondary' ?> btn-sm" onclick="showTab('<?= $tab ?>')" id="tab-btn-<?= $tab ?>"><?= $label ?></button>
    <?php $i++; endforeach; ?>
  </div>

  <!-- General -->
  <div id="tab-general" class="settings-tab">
    <div class="card card-body mb-3">
      <h3 style="margin-bottom:1.25rem">General Settings</h3>
      <div class="grid grid-2 gap-3">
        <div class="form-group">
          <label class="form-label">Site Name</label>
          <input type="text" name="site_name" class="form-control" value="<?= e($s['site_name'] ?? 'MBHaat.com') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Tagline</label>
          <input type="text" name="site_tagline" class="form-control" value="<?= e($s['site_tagline'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Currency Code</label>
          <input type="text" name="currency" class="form-control" value="<?= e($s['currency'] ?? 'BDT') ?>" placeholder="BDT">
        </div>
        <div class="form-group">
          <label class="form-label">Currency Symbol</label>
          <input type="text" name="currency_symbol" class="form-control" value="<?= e($s['currency_symbol'] ?? '৳') ?>" placeholder="৳">
        </div>
        <div class="form-group">
          <label class="form-label">Tax Rate (%)</label>
          <input type="number" name="tax_rate" step="0.01" min="0" max="100" class="form-control" value="<?= e($s['tax_rate'] ?? '0') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">VAT Rate (%)</label>
          <input type="number" name="vat_rate" step="0.01" min="0" max="100" class="form-control" value="<?= e($s['vat_rate'] ?? '0') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Dark Mode Default</label>
          <select name="dark_mode_default" class="form-control">
            <option value="0" <?= ($s['dark_mode_default']??'0')==='0'?'selected':'' ?>>Light Mode</option>
            <option value="1" <?= ($s['dark_mode_default']??'0')==='1'?'selected':'' ?>>Dark Mode</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Maintenance Mode</label>
          <select name="maintenance_mode" class="form-control">
            <option value="0" <?= ($s['maintenance_mode']??'0')==='0'?'selected':'' ?>>Off — Site is Live</option>
            <option value="1" <?= ($s['maintenance_mode']??'0')==='1'?'selected':'' ?>>On — Under Maintenance</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment -->
  <div id="tab-payment" class="settings-tab" style="display:none">
    <div class="card card-body mb-3">
      <h3 style="margin-bottom:1.25rem">Payment Gateway Settings</h3>

      <!-- bKash -->
      <div style="background:linear-gradient(90deg,#E2136E15,transparent);border-left:3px solid #E2136E;border-radius:var(--radius-sm);padding:1rem 1.25rem;margin-bottom:1.5rem">
        <h4 style="margin-bottom:1rem;color:#E2136E">📱 bKash</h4>
        <div class="form-group">
          <label class="form-label">bKash Merchant Number</label>
          <input type="text" name="bkash_number" class="form-control" value="<?= e($s['bkash_number'] ?? '') ?>" placeholder="01XXXXXXXXX">
          <div class="form-text">Users will send payment to this number.</div>
        </div>
      </div>

      <!-- Nagad -->
      <div style="background:linear-gradient(90deg,#F6851B15,transparent);border-left:3px solid #F6851B;border-radius:var(--radius-sm);padding:1rem 1.25rem;margin-bottom:1.5rem">
        <h4 style="margin-bottom:1rem;color:#F6851B">📲 Nagad</h4>
        <div class="form-group">
          <label class="form-label">Nagad Merchant Number</label>
          <input type="text" name="nagad_number" class="form-control" value="<?= e($s['nagad_number'] ?? '') ?>" placeholder="01XXXXXXXXX">
        </div>
      </div>

      <!-- SSL Commerz -->
      <div style="background:linear-gradient(90deg,#008DC915,transparent);border-left:3px solid #008DC9;border-radius:var(--radius-sm);padding:1rem 1.25rem;margin-bottom:1.5rem">
        <h4 style="margin-bottom:1rem;color:#008DC9">💳 SSLCommerz</h4>
        <div class="grid grid-2 gap-3">
          <div class="form-group">
            <label class="form-label">Store ID</label>
            <input type="text" name="ssl_store_id" class="form-control" value="<?= e($s['ssl_store_id'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Merchant ID</label>
            <input type="text" name="ssl_merchant_id" class="form-control" value="<?= e($s['ssl_merchant_id'] ?? '') ?>">
          </div>
          <div class="form-group" style="grid-column:1/-1">
            <label class="form-label">Merchant Password</label>
            <input type="password" name="ssl_merchant_pass" class="form-control" value="<?= e($s['ssl_merchant_pass'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- Bank Transfer -->
      <div style="background:linear-gradient(90deg,#2E7D3215,transparent);border-left:3px solid #2E7D32;border-radius:var(--radius-sm);padding:1rem 1.25rem;margin-bottom:1.5rem">
        <h4 style="margin-bottom:1rem;color:#2E7D32">🏦 Bank Transfer</h4>
        <div class="grid grid-2 gap-3">
          <div class="form-group">
            <label class="form-label">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" value="<?= e($s['bank_name'] ?? '') ?>" placeholder="e.g. Dutch Bangla Bank">
          </div>
          <div class="form-group">
            <label class="form-label">Account Number</label>
            <input type="text" name="bank_account" class="form-control" value="<?= e($s['bank_account'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Routing Number</label>
            <input type="text" name="bank_routing" class="form-control" value="<?= e($s['bank_routing'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- Visa / Mastercard note -->
      <div style="background:linear-gradient(90deg,#1A1F7115,transparent);border-left:3px solid #1A1F71;border-radius:var(--radius-sm);padding:1rem 1.25rem">
        <h4 style="margin-bottom:.5rem;color:#1A1F71">💳 Visa &amp; Mastercard</h4>
        <p class="text-small text-muted">Visa and Mastercard payments are processed via SSLCommerz. Configure your SSLCommerz credentials above to enable card payments.</p>
      </div>
    </div>
  </div>

  <!-- Downloads -->
  <div id="tab-download" class="settings-tab" style="display:none">
    <div class="card card-body mb-3">
      <h3 style="margin-bottom:1.25rem">Download Settings</h3>
      <div class="grid grid-2 gap-3">
        <div class="form-group">
          <label class="form-label">Download Limit (per order item)</label>
          <input type="number" name="download_limit" min="1" class="form-control" value="<?= e($s['download_limit'] ?? '5') ?>">
          <div class="form-text">How many times a buyer can download each file.</div>
        </div>
        <div class="form-group">
          <label class="form-label">Download Expiry (days)</label>
          <input type="number" name="download_expiry_days" min="1" class="form-control" value="<?= e($s['download_expiry_days'] ?? '30') ?>">
          <div class="form-text">Days from purchase until download link expires.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Email -->
  <div id="tab-email" class="settings-tab" style="display:none">
    <div class="card card-body mb-3">
      <h3 style="margin-bottom:1.25rem">Email / SMTP Settings</h3>
      <div class="grid grid-2 gap-3">
        <div class="form-group">
          <label class="form-label">SMTP Host</label>
          <input type="text" name="smtp_host" class="form-control" value="<?= e($s['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
        </div>
        <div class="form-group">
          <label class="form-label">SMTP Port</label>
          <input type="number" name="smtp_port" class="form-control" value="<?= e($s['smtp_port'] ?? '587') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">SMTP Username</label>
          <input type="text" name="smtp_user" class="form-control" value="<?= e($s['smtp_user'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">SMTP Password</label>
          <input type="password" name="smtp_pass" class="form-control" value="<?= e($s['smtp_pass'] ?? '') ?>">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">From Email Address</label>
          <input type="email" name="smtp_from" class="form-control" value="<?= e($s['smtp_from'] ?? '') ?>" placeholder="noreply@mbhaat.com">
        </div>
      </div>
    </div>
  </div>

  <!-- Branding -->
  <div id="tab-branding" class="settings-tab" style="display:none">
    <div class="card card-body mb-3">
      <h3 style="margin-bottom:1.25rem">Branding &amp; Appearance</h3>

      <!-- Current Logo -->
      <div class="form-group">
        <label class="form-label">Site Logo</label>
        <?php if (!empty($s['logo'])): ?>
        <div style="margin-bottom:.75rem">
          <img src="<?= APP_URL ?>/<?= e($s['logo']) ?>" style="height:60px;border-radius:var(--radius-sm);border:1px solid var(--border);padding:.5rem;background:var(--surface2)">
        </div>
        <?php endif; ?>
        <input type="file" name="logo" class="form-control" accept="image/*">
        <div class="form-text">Upload a new logo to replace the current one. PNG recommended.</div>
      </div>

      <div class="form-group">
        <label class="form-label">Footer Text</label>
        <input type="text" name="footer_text" class="form-control" value="<?= e($s['footer_text'] ?? '') ?>">
      </div>
    </div>
  </div>

  <div style="margin-top:1rem">
    <button type="submit" class="btn btn-primary btn-lg">💾 Save All Settings</button>
  </div>
</form>

<script>
function showTab(tab) {
  document.querySelectorAll('.settings-tab').forEach(el => el.style.display = 'none');
  document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => { btn.classList.remove('btn-primary'); btn.classList.add('btn-secondary'); });
  document.getElementById('tab-' + tab).style.display = 'block';
  document.getElementById('tab-btn-' + tab).classList.remove('btn-secondary');
  document.getElementById('tab-btn-' + tab).classList.add('btn-primary');
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
