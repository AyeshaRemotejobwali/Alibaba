<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'supplier') {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $moq = $_POST['moq'];
    $discount = $_POST['discount'];
    $category_id = $_POST['category_id'];
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }
    $stmt = $pdo->prepare("INSERT INTO products (supplier_id, category_id, name, description, price, moq, discount, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $category_id, $name, $description, $price, $moq, $discount, $image]);
    echo '<script>window.location.href = "profile.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Alibaba Clone</title>
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
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #ff6200; color: white; padding: 10px; width: 100%; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #e55a00; }
        @media (max-width: 600px) { .container { padding: 20px; margin: 20px; } }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="#" onclick="navigate('index.php')">Home</a>
        <a href="#" onclick="navigate('profile.php')">Profile</a>
        <a href="#" onclick="navigate('logout.php')">Logout</a>
    </div>
    <div class="container">
        <h2>Add Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="moq">Minimum Order Quantity</label>
                <input type="number" id="moq" name="moq" required>
            </div>
            <div class="form-group">
                <label for="discount">Discount (%)</label>
                <input type="number" id="discount" name="discount" step="0.01">
            </div>
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            <button type="submit">Add Product</button>
        </form>
    </div>
    <script>
        function navigate(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
