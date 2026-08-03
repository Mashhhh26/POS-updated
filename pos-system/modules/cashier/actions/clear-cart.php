<?php
require_once '../../../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../../pages/login.php');
    exit();
}

$_SESSION['cart'] = [];
redirect('../index.php');
?>