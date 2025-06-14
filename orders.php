<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'buyer') {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $stmt = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_price = $product['price'] * $quantity;
    $stmt = $pdo->prepare("INSERT INTO orders (buyer_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $product_id, $quantity, $total_price]);
    echo '<script>window.location.href = "orders.php";</script>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    $order_id = $_POST['order_id'];
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'completed' WHERE order_id = ? AND buyer_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    echo '<script>window.location.href = "orders.php";</script>';
    exit;
}
$stmt = $pdo->prepare("SELECT o.*, p.name, u.company_name FROM orders o JOIN products p ON o.product_id = p.product_id JOIN users u ON p.supplier_id = u.user_id WHERE o.buyer_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Alibaba Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; }
        .navbar { background: #ff6200; padding: 15px; color: white; text-align: center; }
        .navbar a { color: white; text-decoration: none; margin: 0 15px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        h2 { text-align: center; margin-bottom: 20px; color: #ff6200; }
        .order { padding: 15px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .order p { margin: 5px 0; }
        .btn { background: #ff6200; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #e55a00; }
        .form-group { margin-bottom: 15px; }
        input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        @media (max-width: 600px) { .container { padding: 20px; margin: 20px; } }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="#" onclick="navigate('index.php')">Home</a>
        <a href="#" onclick="navigate('products.php')">Products</a>
        <a href="#" onclick="navigate('profile.php')">Profile</a>
        <a href="#" onclick="navigate('logout.php')">Logout</a>
    </div>
    <div class="container">
        <h2>Your Orders</h2>
        <?php foreach ($orders as $order): ?>
            <div class="order">
                <p><strong>Product:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
                <p><strong>Supplier:</strong> <?php echo htmlspecialchars($order['company_name']); ?></p>
                <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                <p><strong>Total Price:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
                <?php if ($order['payment_status'] === 'pending'): ?>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <button type="submit" name="pay" class="btn">Pay Now (Dummy)</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        function navigate(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
