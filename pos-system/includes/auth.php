<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    redirect('../pages/login.php');
    exit();
}
?>