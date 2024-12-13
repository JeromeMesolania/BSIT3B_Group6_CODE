<?php
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

require '../vendor/autoload.php';
require '../connection/db_connection.php';

use MongoDB\BSON\ObjectId;

$instructorId = $_SESSION['user_id'];

try {
    // Initialize MongoDB client and select the database
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('CODE');

    // Retrieve all messages for the instructor (as receiver)
    $messagesCollection = $database->selectCollection('messages');
    $messagesCursor = $messagesCollection->find([
        'receiverId' => new ObjectId($instructorId)
    ]);

    // Initialize users collection for fetching student names
    $usersCollection = $database->selectCollection('users');
} catch (Exception $e) {
    echo "Error connecting to the database: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages from Students</title>
    <link rel="stylesheet" href="commMessage1.css">
</head>

<body>
    <div class="sidebar" id="sidebar">
        <a href="instructordashboard.php" class="sidebar-item">
            <img src="haha.png" alt="Code" class="icon">
            <span class="text">Code</span>
        </a>
        <a href="instructordashboard.php" class="sidebar-item">
            <img src="learning.png" alt="Courses" class="icon">
            <span class="text">Courses</span>
        </a>
        <a href="communication.php" class="sidebar-item">
            <img src="speech-bubble.png" alt="Communication" class="icon">
            <span class="text">Communication</span>
        </a>
        <a href="performance.php" class="sidebar-item">
            <img src="speedometer.png" alt="Performance" class="icon">
            <span class="text">Performance</span>
        </a>
    </div>

    <div class="left-navigation">
        <a href="communication.php" class="nav-item active">Q&A</a>
        <a href="commMessage.php" class="nav-item">Messages</a>
        <a href="commAss.php" class="nav-item">Assignments</a>
        <a href="commAnce.php" class="nav-item">Announcements</a>
    </div>

    <div class="main-content">
        <h1>Messages from Students</h1>

        <div class="messages-container">
            <?php
            $hasMessages = false;
            foreach ($messagesCursor as $message):
                $hasMessages = true;
                $student = $usersCollection->findOne(['_id' => $message['senderId']]);
                $studentName = $student ? $student['firstname'] . ' ' . $student['lastname'] : 'Unknown Student';
            ?>
                <div class="message">
                    <p><strong>From:</strong> <?php echo htmlspecialchars($studentName); ?></p>
                    <p><strong>Message:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
                    <p><strong>Sent on:</strong> <?php echo $message['timestamp']->toDateTime()->format('Y-m-d H:i:s'); ?></p>

                    <form action="reply_message.php" method="POST">
    <input type="hidden" name="receiverId" value="<?php echo htmlspecialchars($message['senderId']); ?>">
    <textarea name="replyMessage" placeholder="Type your reply..." required></textarea>
    <button type="submit">Send Reply</button>
</form>
            <?php endforeach; ?>
            <?php if (!$hasMessages): ?>
                <p>No messages found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
