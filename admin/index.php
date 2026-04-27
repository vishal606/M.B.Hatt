<?php
require_once __DIR__ . '/../src/init.php';

if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/admin/login.php');
} else {
    redirect(APP_URL . '/admin/dashboard.php');
}
