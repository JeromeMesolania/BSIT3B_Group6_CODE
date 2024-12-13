<?php
require '../vendor/autoload.php';
session_start();

use MongoDB\Client;

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

$mongoClient = new Client("mongodb://localhost:27017");
$database = $mongoClient->selectDatabase('CODE');
$collection = $database->selectCollection('messages');

$senderId = $_SESSION['user_id'];
$receiverId = $_POST['receiver_id'];

$messages = $collection->find([
    '$or' => [
        ['sender_id' => $senderId, 'receiver_id' => $receiverId],
        ['sender_id' => $receiverId, 'receiver_id' => $senderId]
    ]
]);

$chatHistory = [];
foreach ($messages as $message) {
    $chatHistory[] = [
        'sender_id' => $message['sender_id'],
        'message' => $message['message'],
        'timestamp' => $message['timestamp']->toDateTime()->format('Y-m-d H:i:s')
    ];
}

echo json_encode($chatHistory);
?>
