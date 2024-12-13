<?php
require '../vendor/autoload.php';  // MongoDB library
require '../connection/db_connection.php';

use MongoDB\BSON\ObjectId;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = $_GET['userId'];  // The logged-in user ID

    // MongoDB connection
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('CODE');
    $messagesCollection = $database->selectCollection('messages');

    // Retrieve messages where the logged-in user is either the sender or receiver
    $messagesCursor = $messagesCollection->find([
        '$or' => [
            ['senderId' => new ObjectId($userId)],
            ['receiverId' => new ObjectId($userId)]
        ]
    ]);

    $messages = iterator_to_array($messagesCursor);

    // Convert messages to a format suitable for the frontend
    $formattedMessages = [];
    foreach ($messages as $msg) {
        $formattedMessages[] = [
            'senderId' => (string)$msg['senderId'],
            'receiverId' => (string)$msg['receiverId'],
            'message' => $msg['message'],
            'timestamp' => $msg['timestamp']->toDateTime()->format('Y-m-d H:i:s'),
            'read' => $msg['read']
        ];
    }

    echo json_encode($formattedMessages);
}
?>
