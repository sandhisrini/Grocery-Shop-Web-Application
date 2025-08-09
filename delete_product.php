<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = (int)$_GET['id'];

// Delete product from DB
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

header("Location: admin_dashboard.php");
exit;
