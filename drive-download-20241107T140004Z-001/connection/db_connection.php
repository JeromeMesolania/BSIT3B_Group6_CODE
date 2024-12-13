<?php
require '../vendor/autoload.php'; 

function connectMongoDB() {
    // MongoDB connection logic
    $client = new MongoDB\Client("mongodb://localhost:27017");
    return $client->selectDatabase('CODE'); 
}

// Ensure $db is accessible globally
$db = connectMongoDB();
?>
