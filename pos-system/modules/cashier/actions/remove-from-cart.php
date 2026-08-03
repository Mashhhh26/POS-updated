<?php
require_once '../../../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../../pages/login.php');
    exit();
}

$key = $_GET['key'] ?? null;

if ($key !== null && isset($_SESSION['cart'][$key])) {
    unset($_SESSION['cart'][$key]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

redirect('../index.php');
?>