<?php
require 'config.php';
require_login();
$agency_id = $_SESSION['agency_id'];

$id = $_POST['id'] ?? null;
$data = [
    'full_name'=>$_POST['full_name'],
    'phone'=>$_POST['phone'],
    'registration_date'=>$_POST['registration_date'],
    'last_payment_date'=>$_POST['last_payment_date'],
    'membership_type'=>$_POST['membership_type'],
    'membership_duration_months'=>intval($_POST['membership_duration_months']),
    'membership_price'=>floatval($_POST['membership_price']),
    'notes'=>$_POST['notes'] ?? ''
];

if ($id) {
    $stmt = $pdo->prepare('UPDATE members SET full_name=?,phone=?,registration_date=?,last_payment_date=?,membership_type=?,membership_duration_months=?,membership_price=?,notes=? WHERE id=? AND agency_id=?');
    $stmt->execute([$data['full_name'],$data['phone'],$data['registration_date'],$data['last_payment_date'],$data['membership_type'],$data['membership_duration_months'],$data['membership_price'],$data['notes'],$id,$agency_id]);
} else {
    $stmt = $pdo->prepare('INSERT INTO members (agency_id,full_name,phone,registration_date,last_payment_date,membership_type,membership_duration_months,membership_price,notes) VALUES (?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$agency_id,$data['full_name'],$data['phone'],$data['registration_date'],$data['last_payment_date'],$data['membership_type'],$data['membership_duration_months'],$data['membership_price'],$data['notes']]);
}
header('Location: members.php');
exit;
?>