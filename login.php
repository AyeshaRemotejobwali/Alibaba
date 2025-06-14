<?php
// Enable error reporting and logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/login_errors.log');
error_reporting(E_ALL);

session_start();
require 'db.php';

$error = '';
$success = '';
$debug = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debug[] = "POST request received.";
    
    try {
        // Sanitize and validate inputs
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $debug[] = "Inputs: email=$email";

        // Validate inputs
        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required.");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Query user by email
        $query = "SELECT user_id, username, password_hash, user_type FROM users WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $debug[] = "Query executed: $query";
        $debug[] = "User found: " . ($user ? 'Yes' : 'No');

        if (!$user) {
            throw new Exception("Invalid email or password.");
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $debug[] = "Password verification failed.";
            throw new Exception("Invalid email or password.");
        }

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        $debug[] = "Session set: user_id={$user['user_id']}, username={$user['username']}";

        // Set success message
        $success = "Login successful! Redirecting...";

        // Redirect to index
        header('Location: index.php');
        echo '<script>navigate("index.php");</script>';
        exit;
    } catch (PDOException $e) {
        $debug[] = "PDOException: " . $e->getMessage();
        error_log("Login PDO error: " . $e->getMessage());
        if (strpos($e->getMessage(), '42S22') !== false) {
            $error = "Database schema error: " . htmlspecialchars($e->getMessage());
        } else {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    } catch (Exception $e) {
        $debug[] = "Exception: " . $e->getMessage();
        error_log("Login error: " . $e->getMessage());
        $error = "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alibaba Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 20px; color: #ff6200; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
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
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="link">
            <a href="#" onclick="navigate('signup.php')">Don’t have an account? Sign Up</a>
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
