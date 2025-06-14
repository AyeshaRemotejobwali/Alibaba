<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = $_POST['company_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $stmt = $pdo->prepare("UPDATE users SET company_name = ?, address = ?, phone = ? WHERE user_id = ?");
    $stmt->execute([$company_name, $address, $phone, $user_id]);
    echo '<script>window.location.href = "profile.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Alibaba Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; }
        .navbar { background: #ff6200; padding: 15px; color: white; text-align: center; }
        .navbar a { color: white; text-decoration: none; margin: 0 15px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        h2 { text-align: center; margin-bottom: 20px; color: #ff6200; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #ff6200; color: white; padding: 10px; width: 100%; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #e55a00; }
        @media (max-width: 600px) { .container { padding: 20px; margin: 20px; } }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="#" onclick="navigate('index.php')">Home</a>
        <a href="#" onclick="navigate('products.php')">Products</a>
        <a href="#" onclick="navigate('logout.php')">Logout</a>
    </div>
    <div class="container">
        <h2>Manage Profile</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($user['company_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            <button type="submit">Update Profile</button>
        </form>
    </div>
    <script>
        function navigate(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
