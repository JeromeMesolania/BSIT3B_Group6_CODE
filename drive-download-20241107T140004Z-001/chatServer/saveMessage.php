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
$message = $_POST['message'];
$timestamp = new MongoDB\BSON\UTCDateTime();

$collection->insertOne([
    'sender_id' => $senderId,
    'receiver_id' => $receiverId,
    'message' => $message,
    'timestamp' => $timestamp
]);

echo json_encode(['status' => 'success', 'message' => 'Message sent successfully.']);
?>
