<?php
// This script checks due & overdue members and prints a list.
// You can set this file to be called by a cron job or call manually.
require 'config.php';
require_login();
$agency_id = $_SESSION['agency_id'];
$today = date('Y-m-d');
$soon = date('Y-m-d', strtotime('+3 days'));

$stmt = $pdo->prepare('SELECT * FROM members WHERE agency_id=?');
$stmt->execute([$agency_id]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

$due = ['due_soon'=>[],'overdue'=>[]];
foreach ($members as $m) {
    $last = $m['last_payment_date'] ?: $m['registration_date'];
    $next = date('Y-m-d', strtotime($last . ' + ' . intval($m['membership_duration_months']) . ' months'));
    if ($next < $today) $due['overdue'][] = [$m['full_name'],$m['phone'],$next];
    elseif ($next <= $soon) $due['due_soon'][] = [$m['full_name'],$m['phone'],$next];
}

header('Content-Type: application/json');
echo json_encode($due, JSON_PRETTY_PRINT);
?>