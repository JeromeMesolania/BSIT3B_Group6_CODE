<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$orderId = $data['orderId'] ?? null;
$courseId = $data['courseId'] ?? null;

if (!$orderId || !$courseId) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
    exit();
}

// Verify PayPal payment using PayPal's API (simplified for this example)
// You should implement full verification with PayPal's REST API

$userId = $_SESSION['user_id'];

// Insert enrollment record into the database
try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('CODE');
    $enrollments = $database->selectCollection('enrolledCourses');

    $enrollments->insertOne([
        'userId' => $userId,
        'courseId' => new MongoDB\BSON\ObjectId($courseId),
        'paymentMethod' => 'PayPal',
        'orderId' => $orderId,
        'timestamp' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error processing PayPal payment: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
