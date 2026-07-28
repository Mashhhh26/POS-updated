<?php
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['is_senior']) && $_POST['is_senior'] == 1) {
        $_SESSION['is_senior'] = 1;
    } else {
        $_SESSION['is_senior'] = 0;
    }
}

redirect('../pages/cart.php');
?>