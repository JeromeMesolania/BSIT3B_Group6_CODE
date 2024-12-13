<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user_id']; // Assuming user ID is stored in session

// Get raw POST data
$inputData = json_decode(file_get_contents('php://input'), true);

if (isset($inputData['courseId'])) {
    $courseId = $inputData['courseId'];
} else {
    echo json_encode(['success' => false, 'message' => 'Course ID is missing']);
    exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$cartCollection = $database->selectCollection('cart');

// Find the user's cart or create a new one
$cart = $cartCollection->findOne(['userId' => $userId]);

if ($cart) {
    // If cart exists, update it
    $updateResult = $cartCollection->updateOne(
        ['userId' => $userId],
        ['$addToSet' => ['courses' => $courseId]] // Adds courseId to the courses array
    );
} else {
    // If no cart exists for the user, create a new one
    $insertResult = $cartCollection->insertOne([
        'userId' => $userId,
        'courses' => [$courseId] // Add courseId to the courses array
    ]);
}

echo json_encode(['success' => true, 'message' => 'Course added to cart']);
?>
