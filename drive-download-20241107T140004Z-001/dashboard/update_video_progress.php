<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'] ?? null;
$courseId = $data['courseId'] ?? null;
$videoId = $data['videoId'] ?? null;
$progress = $data['progress'] ?? null;

if (!$userId || !$courseId || !$videoId || $progress === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$progressCollection = (new MongoDB\Client)->CODE->courseProgress;

$progressCollection->updateOne(
    ['userId' => $userId, 'courseId' => new MongoDB\BSON\ObjectID($courseId)],
    ['$set' => ['progress' => $progress]]
);

echo json_encode(['status' => 'success', 'message' => 'Progress updated successfully']);

// Find the progress document
$filter = ['studentId' => new MongoDB\BSON\ObjectID($userId), 'courseId' => new MongoDB\BSON\ObjectID($courseId)];
$update = [
    '$set' => [
        'updatedAt' => new MongoDB\BSON\UTCDateTime(),
    ],
    '$setOnInsert' => [
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
    ],
    '$addToSet' => [
        'videoProgress' => [
            'videoId' => new MongoDB\BSON\ObjectID($videoId),
            'progress' => (float) $progress
        ]
    ]
];

// Update or insert progress
$options = ['upsert' => true];
$result = $progressCollection->updateOne($filter, $update, $options);

if ($result->getModifiedCount() || $result->getUpsertedCount()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No changes made']);
}
?>
