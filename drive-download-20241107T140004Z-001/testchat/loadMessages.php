<?php
session_start();
require_once '../connection/db_connection.php';  // Include DB connection

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

 use MongoDB\BSON\ObjectId;

$senderId = $_SESSION['user_id'];  // Get sender's ID from session
$receiverId = isset($_GET['receiver_id']) ? $_GET['receiver_id'] : null;  // Get receiver's ID from URL

if (!$receiverId) {
    echo json_encode(["success" => false, "message" => "Receiver ID is required"]);
    exit();
}

// Fetch messages between sender and receiver
$collection = $db->messages;
$messages = $collection->find([
    '$or' => [
        ['senderId' => new MongoDB\BSON\ObjectId($senderId), 'receiverId' => new MongoDB\BSON\ObjectId($receiverId)],
        ['senderId' => new MongoDB\BSON\ObjectId($receiverId), 'receiverId' => new MongoDB\BSON\ObjectId($senderId)]
    ]
]);

// Convert MongoDB cursor to array
$messagesArray = iterator_to_array($messages);

// Return messages as JSON
echo json_encode(["messages" => $messagesArray]);
