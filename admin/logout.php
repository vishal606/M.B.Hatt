<?php
require_once __DIR__ . '/../src/init.php';
unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
redirect(APP_URL . '/admin/login.php');
