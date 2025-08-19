<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare('SELECT * FROM agencies WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $agency = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($agency && password_verify($password, $agency['password'])) {
        $_SESSION['agency_id'] = $agency['id'];
        $_SESSION['agency_name'] = $agency['name'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Login</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body class="p-4">
<div class="container" style="max-width:480px">
<h3>Agency Login</h3>
<?php if(!empty($error)): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post">
<div class="mb-3"><label>Email</label><input name="email" type="email" class="form-control" required></div>
<div class="mb-3"><label>Password</label><input name="password" type="password" class="form-control" required></div>
<button class="btn btn-primary">Login</button>
<a href="register_agency.php" class="btn btn-link">Register</a>
</form>
</div>
</body></html>