<?php
session_start();
require '../connection/db_connection.php';  

use MongoDB\BSON\ObjectId;

$senderId = $_SESSION['user_id']; 
$receiverId = $_POST['receiver_id']; 
$messageText = $_POST['message']; 

$message = [
    'senderId' => new MongoDB\BSON\ObjectId($senderId),
    'receiverId' => new MongoDB\BSON\ObjectId($receiverId),
    'message' => $messageText,
    'timestamp' => new MongoDB\BSON\UTCDateTime(),
    'read' => false
];

$collection = (new MongoDB\Client)->CODE->messages;
$result = $collection->insertOne($message);

echo json_encode(['success' => $result->getInsertedCount() > 0]);

