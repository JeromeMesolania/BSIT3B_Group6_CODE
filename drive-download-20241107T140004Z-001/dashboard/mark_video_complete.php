<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$progressCollection = $database->selectCollection('progress');

$data = json_decode(file_get_contents('php://input'), true);
$courseId = $data['courseId'] ?? null;

if ($courseId) {
    $progressCollection->updateOne(
        ['userId' => $userId, 'courseId' => new MongoDB\BSON\ObjectID($courseId)],
        ['$set' => ['assessmentUnlocked' => true]],
        ['upsert' => true]
    );

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Course ID missing']);
}
?>
