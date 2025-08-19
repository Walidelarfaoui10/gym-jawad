<?php
require 'config.php';
require_login();
$agency_id = $_SESSION['agency_id'];
$id = $_GET['id'];
$pdo->prepare('DELETE FROM members WHERE id=? AND agency_id=?')->execute([$id,$agency_id]);
header('Location: members.php');
exit;
?>