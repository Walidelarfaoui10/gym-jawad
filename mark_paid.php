<?php
require 'config.php';
require_login();
$agency_id = $_SESSION['agency_id'];
$id = $_GET['id'] ?? null;
if ($id) {
    // get member price
    $stm = $pdo->prepare('SELECT membership_price FROM members WHERE id=? AND agency_id=?');
    $stm->execute([$id,$agency_id]);
    $m = $stm->fetch(PDO::FETCH_ASSOC);
    if ($m) {
        $amount = $m['membership_price'];
        $today = date('Y-m-d');
        // create payment record
        $ins = $pdo->prepare('INSERT INTO payments (member_id,agency_id,amount,paid_at) VALUES (?,?,?,?)');
        $ins->execute([$id,$agency_id,$amount,$today]);
        // update last_payment_date
        $up = $pdo->prepare('UPDATE members SET last_payment_date=? WHERE id=? AND agency_id=?');
        $up->execute([$today,$id,$agency_id]);
    }
}
header('Location: members.php');
exit;
?>