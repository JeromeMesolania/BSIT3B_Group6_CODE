<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

// Connect to MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$usersCollection = $database->selectCollection('students');

// Get user details
$userId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
$user = $usersCollection->findOne(['_id' => $userId]);

// Return shopping cart details
$cart = isset($user['shoppingCart']) ? $user['shoppingCart'] : [];
echo json_encode($cart);
?>
