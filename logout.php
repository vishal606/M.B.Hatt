<?php
require_once __DIR__ . '/src/init.php';
session_destroy();
redirect(APP_URL . '/login.php');
