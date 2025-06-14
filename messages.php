<?php
// Enable error reporting and logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/messages_errors.log');
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
$messages = [];
$users = [];

try {
    $user_id = $_SESSION['user_id'];
    $debug[] = "User ID: $user_id";

    // Fetch all users for recipient selection
    $stmt = $pdo->query("SELECT user_id, username FROM users WHERE user_id != ?");
    $stmt->execute([$user_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug[] = "Fetched " . count($users) . " users.";

    // Fetch messages for the logged-in user
    $query = "SELECT m.message_id, m.sender_id, m.receiver_id, m.message, m.created_at, 
                     us.username AS sender_username, ur.username AS receiver_username 
              FROM messages m 
              JOIN users us ON m.sender_id = us.user_id 
              JOIN users ur ON m.receiver_id = ur.user_id 
              WHERE m.sender_id = ? OR m.receiver_id = ? 
              ORDER BY m.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug[] = "Fetched " . count($messages) . " messages.";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $debug[] = "POST request received.";
        
        $receiver_id = (int)($_POST['receiver_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        $debug[] = "Inputs: receiver_id=$receiver_id, message=$message";

        // Validate inputs
        if ($receiver_id <= 0) {
            throw new Exception("Please select a valid recipient.");
        }
        if (empty($message)) {
            throw new Exception("Message cannot be empty.");
        }

        // Verify receiver exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $stmt->execute([$receiver_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Selected recipient does not exist.");
        }

        // Insert message
        $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $receiver_id, $message]);
        $debug[] = "Query executed: $query";

        $success = "Message sent successfully!";
    }
} catch (PDOException $e) {
    $debug[] = "PDOException: " . $e->getMessage();
    error_log("Messages error: " . $e->getMessage());
    if (strpos($e->getMessage(), '1146') !== false) {
        $error = "Messages table missing. Please contact administrator.";
    } elseif (strpos($e->getMessage(), '42S22') !== false) {
        $error = "Database schema error: " . htmlspecialchars($e->getMessage());
    } else {
        $error = "Database error: " . htmlspecialchars($e->getMessage());
    }
} catch (Exception $e) {
    $debug[] = "Exception: " . $e->getMessage();
    error_log("Messages error: " . $e->getMessage());
    $error = "Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Alibaba Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; }
        .navbar { background: #ff6200; padding: 15px; color: white; text-align: center; }
        .navbar a { color: white; text-decoration: none; margin: 0 15px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .form-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2 { text-align: center; margin-bottom: 20px; color: #ff6200; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        textarea { height: 100px; resize: vertical; }
        button { background: #ff6200; color: white; padding: 10px; width: 100%; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #e55a00; }
        .messages { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .message { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .message:last-child { border-bottom: none; }
        .message p { margin: 5px 0; }
        .message .sender { font-weight: bold; color: #ff6200; }
        .message .time { font-size: 12px; color: #777; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
        .success { color: green; text-align: center; margin-bottom: 15px; }
        .debug { background: #fff3cd; padding: 10px; margin-top: 20px; border-radius: 5px; font-size: 12px; }
        @media (max-width: 600px) { .container { padding: 0 10px; } .form-container, .messages { padding: 15px; } }
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
            <h2>Send Message</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="receiver_id">Recipient</label>
                    <select id="receiver_id" name="receiver_id" required>
                        <option value="">Select a recipient</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <button type="submit">Send Message</button>
            </form>
        </div>
        <div class="messages">
            <h2>Messages</h2>
            <?php if (empty($messages)): ?>
                <p>No messages found.</p>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message">
                        <p class="sender">
                            <?php echo htmlspecialchars($message['sender_id'] == $user_id ? 'You' : $message['sender_username']); ?> 
                            to 
                            <?php echo htmlspecialchars($message['receiver_id'] == $user_id ? 'You' : $message['receiver_username']); ?>
                        </p>
                        <p><?php echo htmlspecialchars($message['message']); ?></p>
                        <p class="time"><?php echo $message['created_at']; ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
