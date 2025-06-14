<?php
// Enable error reporting and logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/signup_errors.log');
error_reporting(E_ALL);

require 'db.php';

$error = '';
$success = '';
$debug = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debug[] = "POST request received.";
    
    try {
        // Sanitize and validate inputs
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $user_type = $_POST['user_type'] ?? '';
        $company_name = trim($_POST['company_name'] ?? '');
        $address = trim($_POST['address'] ?? '') ?: null;
        $phone = trim($_POST['phone'] ?? '') ?: null;

        $debug[] = "Inputs: username=$username, email=$email, user_type=$user_type, company_name=$company_name";

        // Validate required fields
        if (empty($username) || empty($email) || empty($password) || empty($company_name)) {
            throw new Exception("All required fields (username, email, password, company name) must be filled.");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Validate user_type
        if (!in_array($user_type, ['buyer', 'supplier'])) {
            throw new Exception("Invalid user type selected.");
        }

        // Validate username length
        if (strlen($username) > 50) {
            throw new Exception("Username must be 50 characters or less.");
        }

        // Hash password
        $password_hashed = password_hash($password, PASSWORD_BCRYPT);
        $debug[] = "Password hashed successfully.";

        // Prepare and execute query
        $query = "INSERT INTO users (username, email, password_hash, user_type, company_name, address, phone) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $debug[] = "Query prepared: $query";
        
        $stmt->execute([$username, $email, $password_hashed, $user_type, $company_name, $address, $phone]);
        $debug[] = "Query executed successfully.";

        // Set success message
        $success = "Signup successful! Redirecting to login...";

        // Redirect to login
        header('Location: login.php');
        echo '<script>navigate("login.php");</script>';
        exit;
    } catch (PDOException $e) {
        $debug[] = "PDOException: " . $e->getMessage();
        error_log("Signup PDO error: " . $e->getMessage());
        
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            if (strpos($e->getMessage(), 'username') !== false) {
                $error = "Username already exists. Choose a different username.";
            } elseif (strpos($e->getMessage(), 'email') !== false) {
                $error = "Email already exists. Use a different email.";
            } else {
                $error = "Duplicate entry error. Please try again.";
            }
        } elseif (strpos($e->getMessage(), '42S22') !== false) {
            $error = "Database schema error: " . htmlspecialchars($e->getMessage());
        } else {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    } catch (Exception $e) {
        $debug[] = "Exception: " . $e->getMessage();
        error_log("Signup error: " . $e->getMessage());
        $error = "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Alibaba Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 20px; color: #ff6200; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #ff6200; color: white; padding: 10px; width: 100%; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #e55a00; }
        .error, .success { color: red; text-align: center; margin-bottom: 15px; font-size: 14px; }
        .success { color: green; }
        .link { text-align: center; margin-top: 15px; }
        .link a { color: #ff6200; text-decoration: none; }
        .link a:hover { text-decoration: underline; }
        .debug { background: #fff3cd; padding: 10px; margin-top: 20px; border-radius: 5px; font-size: 12px; }
        @media (max-width: 600px) { .form-container { padding: 20px; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Sign Up</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select id="user_type" name="user_type" required>
                    <option value="buyer" <?php echo ($user_type ?? '') === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                    <option value="supplier" <?php echo ($user_type ?? '') === 'supplier' ? 'selected' : ''; ?>>Supplier</option>
                </select>
            </div>
            <div class="form-group">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company_name ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <div class="link">
            <a href="#" onclick="navigate('login.php')">Already have an account? Login</a>
        </div>
        <?php if (!empty($debug)): ?>
            <div class="debug">
                <h4>Debug Info:</h4>
                <pre><?php echo htmlspecialchars(implode("\n", $debug)); ?></pre>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function navigate(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
