<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

$userId = $_SESSION['user_id'];
$courseId = $_POST['courseId'] ?? null;

if ($courseId) {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('CODE');
    $cartCollection = $database->selectCollection('cart');

    // Check if the course is already in the user's cart
    $existingCartItem = $cartCollection->findOne([
        'userId' => $userId,
        'courseId' => $courseId
    ]);

    if ($existingCartItem) {
        echo json_encode(['success' => false, 'message' => 'Course already in your cart']);
    } else {
        // Add the course to the user's cart
        $cartCollection->insertOne([
            'userId' => $userId,
            'courseId' => $courseId,
            'addedAt' => new MongoDB\BSON\UTCDateTime(),
        ]);
        echo json_encode(['success' => true, 'message' => 'Course added to cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Course ID is missing']);
}
?>
