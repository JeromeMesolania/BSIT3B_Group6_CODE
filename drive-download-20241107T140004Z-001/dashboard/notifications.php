<?php
session_start();

// Redirect to error page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

// Load MongoDB PHP library (Composer autoload)
require '../vendor/autoload.php';
include '../navbar/navbar.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

// Database connection
$client = new Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$messagesCollection = $database->selectCollection('messages');

// Fetch logged-in user ID
$userId = $_SESSION['user_id'];

// Fetch messages where the current user is the receiver
$messages = $messagesCollection->find([
    'receiverId' => $userId
], [
    'sort' => ['timestamp' => -1] // Sort by timestamp, most recent first
])->toArray();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Notifications</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        .notifications-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .notification {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }
        .notification:last-child {
            border-bottom: none;
        }
        .notification p {
            margin: 5px 0;
        }
        .notification .timestamp {
            font-size: 0.9em;
            color: #7f8c8d;
        }
        .unread {
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1 style="margin-left: 40%" >Your Notifications</h1>

<div class="notifications-container">
    <?php if (empty($messages)): ?>
        <p style="margin-left: 37%">You have no notifications.</p>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="notification <?php echo !$message['read'] ? 'unread' : ''; ?>">
                <p><strong>From:</strong> <?php echo htmlspecialchars($message['senderId']); ?></p>
                <p><strong>Message:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
                <p class="timestamp"><?php echo date('F j, Y, g:i a', $message['timestamp']->toDateTime()->getTimestamp()); ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
