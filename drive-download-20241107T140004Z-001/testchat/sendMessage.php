<?php
session_start();
require_once '../connection/db_connection.php';  // Include DB connection

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

    use MongoDB\BSON\UTCDateTime;

$senderId = $_SESSION['user_id'];  // Get sender's ID from session
$receiverId = $data->receiverId;   // Receiver ID from POST data
$message = $data->message;

if (empty($senderId) || empty($receiverId) || empty($message)) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit();
}

// Insert message into MongoDB
$collection = $db->messages;
$messageData = [
    'senderId' => new MongoDB\BSON\ObjectId($senderId),
    'receiverId' => new MongoDB\BSON\ObjectId($receiverId),
    'message' => $message,
    'timestamp' => new MongoDB\BSON\UTCDateTime(),
    'read' => false
];
$insertResult = $collection->insertOne($messageData);

if ($insertResult->getInsertedCount() > 0) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to send message"]);
}
