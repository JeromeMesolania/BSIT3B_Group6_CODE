<?php
require '../vendor/autoload.php'; // Include MongoDB library

$client = new MongoDB\Client("mongodb://localhost:27017"); // Connect to MongoDB
$collection = $client->CODE->cart; // Access 'cart' collection in 'CODE' database

// Fetch all courses in the cart
$cursor = $collection->find();

// Return the data as JSON
$cartData = [];
foreach ($cursor as $document) {
    $cartData[] = $document;
}
header('Content-Type: application/json');
$cartItems = [ 
    ["title" => "Course 1", "price" => "$10"], 
    ["title" => "Course 2", "price" => "$20"]
]; 

echo json_encode($cartData);
?>
