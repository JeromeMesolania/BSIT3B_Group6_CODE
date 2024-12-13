<?php
require '../vendor/autoload.php';  // MongoDB library
require '../connection/db_connection.php';

use MongoDB\BSON\ObjectId;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $messageId = $_POST['messageId'];  // ID of the message to mark as read

    // MongoDB connection
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('CODE');
    $messagesCollection = $database->selectCollection('messages');

    // Update message status to 'read'
    $result = $messagesCollection->updateOne(
        ['_id' => new ObjectId($messageId)],
        ['$set' => ['read' => true]]
    );

    if ($result->getModifiedCount() > 0) {
        echo json_encode(['success' => 'Message marked as read']);
    } else {
        echo json_encode(['error' => 'Failed to mark message as read']);
    }
}
?>
