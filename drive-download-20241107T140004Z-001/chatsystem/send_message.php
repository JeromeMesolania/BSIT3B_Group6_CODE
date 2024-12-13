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

$userId = $_SESSION['user_id'];

// Initialize MongoDB client and select the database
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');  // Select the CODE database

// Get the instructorId from the URL
$instructorId = isset($_GET['instructorId']) ? $_GET['instructorId'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($instructorId)) {
    $message = isset($_POST['message']) ? $_POST['message'] : '';

    if (!empty($message)) {
        $messagesCollection = $database->selectCollection('messages');

        // Insert the message into the collection
        $messagesCollection->insertOne([
            'senderId' => new ObjectId($userId), // Student ID
            'receiverId' => new ObjectId($instructorId), // Instructor ID
            'message' => $message,
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'read' => false // Message is unread initially
        ]);

        echo "Message sent successfully!";
    } else {
        echo "Message cannot be empty.";
    }
} else {
    echo "Instructor ID is missing or invalid.";
}
?>
