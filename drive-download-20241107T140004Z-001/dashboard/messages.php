<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

require '../vendor/autoload.php';
require '../navbar/navbar.php';

use MongoDB\BSON\ObjectId;

$userId = $_SESSION['user_id'];
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$messagesCollection = $database->selectCollection('messages');
$usersCollection = $database->selectCollection('users');

// Fetch messages where the logged-in student is the receiver
$messagesCursor = $messagesCollection->find(['receiverId' => new ObjectId($userId)])->toArray();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Messages</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="messages.css">
</head>
<body>

<h1>Your Messages</h1>

<?php if (!empty($messagesCursor)): ?>
    <ul class="message-list">
        <?php foreach ($messagesCursor as $message): ?>
            <?php
            // Fetch the sender's details
            $sender = $usersCollection->findOne(['_id' => $message['senderId']]);
            $senderName = $sender ? htmlspecialchars($sender['firstname'] . ' ' . $sender['lastname']) : 'Unknown Sender';
            ?>
            <li class="message-item">
                <div class="message-content">
                    <p><strong>From:</strong> <?php echo $senderName; ?></p>
                    <p><strong>Message:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
                    <p><strong>Sent At:</strong> <?php echo date('Y-m-d H:i:s', $message['timestamp']->toDateTime()->getTimestamp()); ?></p>
                </div>
                
                <form action="reply.php" method="POST" class="reply-form">
                    <input type="hidden" name="receiverId" value="<?php echo htmlspecialchars($message['senderId']); ?>">
                    <textarea name="replyMessage" placeholder="Write your reply..." required></textarea><br>
                    <button type="submit" class="reply-button">Send Reply</button>
                    <button type="submit" class="reply-button" onclick="window.location.href='message_instructor.php'">Messages</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="no-messages">No messages to display.</p>
<?php endif; ?>

</body>
</html>
