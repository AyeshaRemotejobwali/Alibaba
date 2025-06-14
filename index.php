<?php
session_start();
require 'db.php';
$stmt = $pdo->query("SELECT p.*, u.company_name FROM products p JOIN users u ON p.supplier_id = u.user_id ORDER BY p.created_at DESC LIMIT 6");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'supplier' ORDER BY rating DESC LIMIT 4");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alibaba Clone - Homepage</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; }
        .navbar { background: #ff6200; padding: 15px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin: 0 15px; font-size: 16px; }
        .navbar a:hover { text-decoration: underline; }
        .hero { background: url('https://via.placeholder.com/1200x400') center/cover; padding: 50px; text-align: center; color: white; }
        .hero h1 { font-size: 48px; margin-bottom: 20px; }
        .hero input { padding: 10px; width: 50%; border: none; border-radius: 5px; font-size: 16px; }
        .section { padding: 40px; }
        .section h2 { font-size: 28px; margin-bottom: 20px; text-align: center; }
        .products, .suppliers { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .product, .supplier { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .product img, .supplier img { width: 100%; height: 150px; object-fit: cover; border-radius: 5px; }
        .product h3, .supplier h3 { font-size: 20px; margin: 10px 0; }
        .product p, .supplier p { color: #555; }
        .btn { background: #ff6200; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #e55a00; }
        @media (max-width: 768px) { .hero h1 { font-size: 32px; } .hero input { width: 80%; } }
    </style>
</head>
<body>
    <div class="navbar">
        <div>Alibaba Clone</div>
        <div>
            <a href="#" onclick="navigate('signup.php')">Sign Up</a>
            <a href="#" onclick="navigate('login.php')">Login</a>
            <a href="#" onclick="navigate('products.php')">Products</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="navigate('profile.php')">Profile</a>
                <a href="#" onclick="navigate('logout.php')">Logout</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero">
        <h1>Discover Global Wholesale Products</h1>
        <input type="text" placeholder="Search for products..." id="search" onkeydown="if(event.key === 'Enter') searchProducts()">
    </div>
    <div class="section">
        <h2>Trending Products</h2>
        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <img src="<?php echo htmlspecialchars($product['image'] ?: 'https://via.placeholder.com/150'); ?>" alt="Product">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Price: $<?php echo number_format($product['price'], 2); ?> | MOQ: <?php echo $product['moq']; ?></p>
                    <p>Supplier: <?php echo htmlspecialchars($product['company_name']); ?></p>
                    <button class="btn" onclick="navigate('quotation.php?product_id=<?php echo $product['product_id']; ?>')">Request Quote</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="section">
        <h2>Featured Suppliers</h2>
        <div class="suppliers">
            <?php foreach ($suppliers as $supplier): ?>
                <div class="supplier">
                    <img src="https://via.placeholder.com/150" alt="Supplier">
                    <h3><?php echo htmlspecialchars($supplier['company_name']); ?></h3>
                    <p>Rating: <?php echo number_format($supplier['rating'], 1); ?> / 5</p>
                    <button class="btn" onclick="navigate('messages.php?receiver_id=<?php echo $supplier['user_id']; ?>')">Contact</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function navigate(url) {
            window.location.href = url;
        }
        function searchProducts() {
            const query = document.getElementById('search').value;
            navigate('products.php?search=' + encodeURIComponent(query));
        }
    </script>
</body>
</html>
