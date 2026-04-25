<?php
$pageTitle = 'FAQ';
require_once '../includes/config.php';
include '../includes/header.php';

// Get FAQs
$faqStmt = $pdo->query("SELECT * FROM faq WHERE status = 'active' ORDER BY sort_order, category");
$faqs = $faqStmt->fetchAll();

// Group by category
$groupedFaqs = [];
foreach ($faqs as $faq) {
    $category = $faq['category'] ?? 'General';
    if (!isset($groupedFaqs[$category])) {
        $groupedFaqs[$category] = [];
    }
    $groupedFaqs[$category][] = $faq;
}
?>

<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold text-brand-purple">Frequently Asked Questions</h1>
        <p class="text-muted">Find answers to common questions about MBHaat.com</p>
    </div>
    
    <!-- Search -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" id="faqSearch" class="form-control border-start-0" placeholder="Search for answers...">
            </div>
        </div>
    </div>
    
    <!-- FAQ Categories -->
    <div class="row">
        <?php foreach ($groupedFaqs as $category => $items): ?>
        <div class="col-lg-6 mb-4 faq-category" data-category="<?php echo strtolower($category); ?>">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-brand-purple mb-0">
                        <i class="fas fa-folder me-2 text-brand-blue"></i><?php echo $category; ?>
                    </h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="accordion" id="faqAccordion<?php echo preg_replace('/[^a-zA-Z]/', '', $category); ?>">
                        <?php foreach ($items as $index => $faq): ?>
                        <div class="accordion-item border-0 faq-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" 
                                        type="button" data-mdb-collapse-init
                                        data-mdb-target="#collapse<?php echo $faq['id']; ?>">
                                    <?php echo $faq['question']; ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $faq['id']; ?>" 
                                 class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                 data-mdb-parent="#faqAccordion<?php echo preg_replace('/[^a-zA-Z]/', '', $category); ?>">
                                <div class="accordion-body text-muted">
                                    <?php echo nl2br($faq['answer']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Still Have Questions -->
    <div class="text-center mt-5 py-5" style="background: linear-gradient(135deg, #F7F6E5 0%, #76D2DB 100%); border-radius: 20px;">
        <h3 class="fw-bold text-brand-purple mb-3">Still have questions?</h3>
        <p class="text-muted mb-4">Can't find the answer you're looking for? Please contact our support team.</p>
        <a href="contact.php" class="btn btn-brand-purple">
            <i class="fas fa-envelope me-2"></i>Contact Support
        </a>
    </div>
</div>

<script>
document.getElementById('faqSearch').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const items = document.querySelectorAll('.faq-item');
    const categories = document.querySelectorAll('.faq-category');
    
    items.forEach(item => {
        const question = item.querySelector('.accordion-button').textContent.toLowerCase();
        const answer = item.querySelector('.accordion-body').textContent.toLowerCase();
        
        if (question.includes(query) || answer.includes(query)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide categories based on visible items
    categories.forEach(cat => {
        const visibleItems = cat.querySelectorAll('.faq-item[style="display: block;"], .faq-item:not([style])');
        cat.style.display = visibleItems.length > 0 ? 'block' : 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
