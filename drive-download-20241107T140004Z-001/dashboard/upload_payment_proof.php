<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    die("User not logged in");
}

$userId = $_SESSION['user_id'];
$courseId = isset($_POST['courseId']) ? trim($_POST['courseId']) : null;

if (!$courseId || !preg_match('/^[a-f\d]{24}$/i', $courseId)) {
    http_response_code(400); // Bad request
    die("Invalid Course ID");
}

// Validate and handle the uploaded file
if (!isset($_FILES['paymentProof']) || $_FILES['paymentProof']['error'] != UPLOAD_ERR_OK) {
    http_response_code(400);
    die("Error in file upload");
}

// Check file type (accept only images)
$fileType = mime_content_type($_FILES['paymentProof']['tmp_name']);
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    die("Invalid file type. Only JPG and PNG files are allowed.");
}

// Move uploaded file to a destination folder
$uploadsDir = '../uploads/payment_proofs/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}
$fileName = basename($_FILES['paymentProof']['name']);
$targetFile = $uploadsDir . time() . '_' . $fileName;

if (!move_uploaded_file($_FILES['paymentProof']['tmp_name'], $targetFile)) {
    http_response_code(500);
    die("Failed to save file.");
}

// Initialize MongoDB client and insert into 'payments' collection
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$paymentsCollection = $database->selectCollection('payments');

$paymentData = [
    'userId' => $userId,
    'courseId' => new MongoDB\BSON\ObjectId($courseId),
    'proofPath' => $targetFile,
    'timestamp' => new MongoDB\BSON\UTCDateTime(),
    'status' => 'pending' // Default status
];

try {
    $insertResult = $paymentsCollection->insertOne($paymentData);
    echo json_encode(['success' => true, 'message' => 'Payment proof uploaded successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
