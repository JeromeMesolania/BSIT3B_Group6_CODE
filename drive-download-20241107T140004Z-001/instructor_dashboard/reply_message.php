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

// Get the instructor's ID from the session
$instructorId = $_SESSION['user_id'];
$receiverId = $_POST['receiverId'];
$senderId = $_SESSION['user_id'];

// Check if form data is set
if (isset($_POST['receiverId'], $_POST['replyMessage'])) {
    $receiverId = $_POST['receiverId'];
    $replyMessage = trim($_POST['replyMessage']);

    if (empty($replyMessage)) {
        echo "Error: Message cannot be empty.";
        exit();
    }

    try {
        $client = new MongoDB\Client("mongodb://localhost:27017");
        $database = $client->selectDatabase('CODE');
        $messagesCollection = $database->selectCollection('messages');

        $messagesCollection->insertOne([
            'senderId' => new ObjectId($instructorId),
            'receiverId' => new ObjectId($receiverId),
            'message' => $replyMessage,
            'timestamp' => new MongoDB\BSON\UTCDateTime()
        ]);

        header("Location: commMessage.php");
        exit();
    } catch (Exception $e) {
        echo "Error sending reply: " . $e->getMessage();
    }
} else {
    echo "Invalid form submission.";
    exit();
}

?>
