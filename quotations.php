<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/quotation_errors.log');
error_reporting(E_ALL);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$debug = [];

try {
    // Fetch products for the form
    $stmt = $pdo->query("SELECT product_id, name FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug[] = "Fetched " . count($products) . " products.";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $debug[] = "POST request received.";
        
        $buyer_id = $_SESSION['user_id'];
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $offered_price = (float)($_POST['offered_price'] ?? 0);

        $debug[] = "Inputs: buyer_id=$buyer_id, product_id=$product_id, quantity=$quantity, offered_price=$offered_price";

        // Validate inputs
        if ($product_id <= 0) {
            throw new Exception("Please select a valid product.");
        }
        if ($quantity <= 0) {
            throw new Exception("Quantity must be greater than 0.");
        }
        if ($offered_price < 0) {
            throw new Exception("Offered price cannot be negative.");
        }

        // Verify product exists
        $stmt = $pdo->prepare("SELECT product_id FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Selected product does not exist.");
        }

        // Verify buyer is valid
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND user_type = 'buyer'");
        $stmt->execute([$buyer_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Invalid buyer account.");
        }

        // Insert quotation
        $query = "INSERT INTO quotations (buyer_id, product_id, quantity, offered_price) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$buyer_id, $product_id, $quantity, $offered_price]);
        $debug[] = "Query executed: $query";

        $success = "Quotation submitted successfully!";
    }
} catch (PDOException $e) {
    $debug[] = "PDOException: " . $e->getMessage();
    error_log("Quotation error: " . $e->getMessage());
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $error = "Quotation already exists for this product.";
    } elseif (strpos($e->getMessage(), '1146') !== false) {
        $error = "Quotations table missing. Please contact administrator.";
    } else {
        $error = "Database error: " . htmlspecialchars($e->getMessage());
    }
} catch (Exception $e) {
    $debug[] = "Exception: " . $e->getMessage();
    error_log("Quotation error: " . $e->getMessage());
    $error = "Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Quotation - Alibaba Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; }
        .navbar { background: #ff6200; padding: 15px; color: white; text-align: center; }
        .navbar a { color: white; text-decoration: none; margin: 0 15px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }
        .form-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; color: #ff6200; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #ff6200; color: white; padding: 10px; width: 100%; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #e55a00; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
        .success { color: green; text-align: center; margin-bottom: 15px; }
        .debug { background: #fff3cd; padding: 10px; margin-top: 20px; border-radius: 5px; font-size: 12px; }
        @media (max-width: 600px) { .form-container { padding: 15px; } }
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
        <div class="form-container">
            <h2>Request Quotation</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="product_id">Product</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Select a product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="offered_price">Offered Price ($)</label>
                    <input type="number" id="offered_price" name="offered_price" step="0.01" min="0" required>
                </div>
                <button type="submit">Submit Quotation</button>
            </form>
            <?php if (!empty($debug)): ?>
                <div class="debug">
                    <h4>Debug Info:</h4>
                    <pre><?php echo htmlspecialchars(implode("\n", $debug)); ?></pre>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function navigate(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
