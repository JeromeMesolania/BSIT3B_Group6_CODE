<?php
session_start();
require '../connection/db_connection.php';

$userId = $_SESSION['user_id'];
$contactId = $_POST['contact_id']; 

$collection = (new MongoDB\Client)->CODE->messages;
$messages = $collection->find([
    '$or' => [
        ['senderId' => new MongoDB\BSON\ObjectId($userId), 'receiverId' => new MongoDB\BSON\ObjectId($contactId)],
        ['senderId' => new MongoDB\BSON\ObjectId($contactId), 'receiverId' => new MongoDB\BSON\ObjectId($userId)]
    ]
]);

$messagesArray = [];
foreach ($messages as $message) {
    $messagesArray[] = $message;
}

echo json_encode($messagesArray);

