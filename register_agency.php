<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?: '';
    $email = $_POST['email'] ?: '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO agencies (name,email,password) VALUES (?, ?, ?)');
    $stmt->execute([$name,$email,$password]);
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Register Agency</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body class="p-4">
<div class="container" style="max-width:600px">
<h3>Register Agency</h3>
<form method="post">
<div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
<div class="mb-3"><label>Email</label><input name="email" type="email" class="form-control" required></div>
<div class="mb-3"><label>Password</label><input name="password" type="password" class="form-control" required></div>
<button class="btn btn-primary">Register</button>
<a href="login.php" class="btn btn-link">Login</a>
</form>
</div>
</body>
</html>