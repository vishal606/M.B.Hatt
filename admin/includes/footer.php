        </div>
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.umd.min.js"></script>
<script src="<?php echo APP_URL; ?>/admin/assets/js/admin.js"></script>

<script>
// Auto-hide alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new mdb.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Confirm delete
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

// Toggle sidebar on mobile
document.querySelector('.sidebar-toggle')?.addEventListener('click', function() {
    document.querySelector('.admin-sidebar').classList.toggle('show');
});
</script>

</body>
</html>
