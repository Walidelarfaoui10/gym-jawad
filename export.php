<?php
require 'config.php';
require_login();
$agency_id = $_SESSION['agency_id'];

$stm = $pdo->prepare('SELECT * FROM members WHERE agency_id=?');
$stm->execute([$agency_id]);
$members = $stm->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="members.csv"');

$out = fopen('php://output','w');
fputcsv($out, ['id','full_name','phone','registration_date','last_payment_date','membership_type','duration_months','price','notes']);
foreach($members as $m) {
    fputcsv($out, [$m['id'],$m['full_name'],$m['phone'],$m['registration_date'],$m['last_payment_date'],$m['membership_type'],$m['membership_duration_months'],$m['membership_price'],$m['notes']]);
}
fclose($out);
exit;
?>