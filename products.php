<?php
session_start();
require 'db.php';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : PHP_INT_MAX;
$moq = isset($_GET['moq']) ? (int)$_GET['moq'] : 0;
$query = "SELECT p.*, u.company_name, c.name AS category_name FROM products p JOIN users u ON p.supplier_id = u.user_id JOIN categories c ON p.category_id = c.category_id WHERE 1=1";
$params = [];
if ($search) {
    $query .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}
if ($category_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
}
if ($min_price > 0) {
    $query .= " AND p.price >= ?";
    $params[] = $min_price;
}
if ($max_price < PHP_INT_MAX) {
    $query .= " AND p.price <= ?";
    $params[] = $max_price;
}
if ($moq > 0) {
    $query .= " AND p.moq <= ?";
    $params[] = $moq;
}
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Alibaba Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; }
        .navbar { background: #ff6200; padding: 15px; color: white; text-align: center; }
        .navbar a { color: white; text-decoration: none; margin: 0 15px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .filter { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .filter form { display: flex; flex-wrap: wrap; gap: 15px; }
        .filter input, .filter select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .filter button { background: #ff6200; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .filter button:hover { background: #e55a00; }
        .products { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .product { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .product img { width: 100%; height: 150px; object-fit: cover; border-radius: 5px; }
        .product h3 { font-size: 20px; margin: 10px 0; }
        .product p { color: #555; }
        .btn { background: #ff6200; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #e55a00; }
        @media (max-width: 768px) { .filter form { flex-direction: column; } .container { padding: 0 10px; } }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="#" onclick="navigate('index.php')">Home</a>
        <a href="#" onclick="navigate('profile.php')">Profile</a>
        <a href="#" onclick="navigate('logout.php')">Logout</a>
    </div>
    <div class="container">
        <div class="filter">
            <form method="GET">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="category_id">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="min_price" placeholder="Min Price" value="<?php echo $min_price ?: ''; ?>">
                <input type="number" name="max_price" placeholder="Max Price" value="<?php echo $max_price < PHP_INT_MAX ? $max_price : ''; ?>">
                <input type="number" name="moq" placeholder="Max MOQ" value="<?php echo $moq ?: ''; ?>">
                <button type="submit">Filter</button>
            </form>
        </div>
        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <img src="<?php echo htmlspecialchars($product['image'] ?: 'https://via.placeholder.com/150'); ?>" alt="Product">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
                    <p>Price: $<?php echo number_format($product['price'], 2); ?> | MOQ: <?php echo $product['moq']; ?></p>
                    <p>Supplier: <?php echo htmlspecialchars($product['company_name']); ?></p>
                    <button class="btn" onclick="navigate('quotation.php?product_id=<?php echo $product['product_id']; ?>')">Request Quote</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function navigate(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
