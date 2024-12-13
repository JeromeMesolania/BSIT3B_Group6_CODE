<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

// Debugging: Log session user ID
error_log("Current Session User ID: " . $_SESSION['user_id']);

require '../vendor/autoload.php';
require '../connection/db_connection.php';

use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderId = $_SESSION['user_id'];
    $receiverId = $_POST['receiverId']; 
    $replyMessage = trim($_POST['replyMessage']);

    if (!$receiverId || !preg_match('/^[a-f\d]{24}$/i', $receiverId)) {
        echo "Error: Invalid receiver ID.";
        exit();
    }

    if (empty($replyMessage)) {
        echo "Error: Message cannot be empty.";
        exit();
    }

    // Debugging: Log message data before inserting
    error_log("Sending message from User ID: $senderId to Receiver ID: $receiverId");

    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('CODE');
    $messagesCollection = $database->selectCollection('messages');

    $messageData = [
        'senderId' => new ObjectId($senderId),
        'receiverId' => new ObjectId($receiverId),
        'message' => $replyMessage,
        'timestamp' => new UTCDateTime()
    ];

    $messagesCollection->insertOne($messageData);

    // Redirect back to messages
    header("Location: messages.php");
    exit();
}

?>
