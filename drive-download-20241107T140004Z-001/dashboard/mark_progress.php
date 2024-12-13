<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$courseId = $data['courseId'] ?? null;
$videoId = $data['videoId'] ?? null;

if (!$courseId || !$videoId) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$coursesCollection = $database->selectCollection('courses');
$progressCollection = $database->selectCollection('progress');

// Check if the course exists
$course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($courseId)]);

if (!$course) {
    echo json_encode(['success' => false, 'message' => 'Course not found']);
    exit();
}

// Check if the user has progress recorded for this course
$progress = $progressCollection->findOne(['userId' => $userId, 'courseId' => new MongoDB\BSON\ObjectId($courseId)]);

if (!$progress) {
    // Create new progress record if none exists
    $progressData = [
        'userId' => $userId,
        'courseId' => new MongoDB\BSON\ObjectId($courseId),
        'completedVideos' => [],
        'assessmentUnlocked' => false,
        'totalVideos' => 1,  // Assuming there's only 1 video for simplicity
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
        'updatedAt' => new MongoDB\BSON\UTCDateTime()
    ];
    $progressCollection->insertOne($progressData);
    $progress = $progressData;
}

// Mark the video as completed
if (!in_array($videoId, $progress['completedVideos'])) {
    $progress['completedVideos'][] = $videoId; // Mark the video as completed
}

// Check if all videos are completed (adjust this for multiple videos)
if (count($progress['completedVideos']) === $progress['totalVideos']) {
    $progress['assessmentUnlocked'] = true; // Unlock assessment
}

// Update progress record in the database
$progressCollection->updateOne(
    ['_id' => $progress['_id']], // Match the progress document
    ['$set' => $progress] // Update the progress fields
);

echo json_encode([
    'success' => true,
    'message' => 'Progress updated successfully',
    'assessmentUnlocked' => $progress['assessmentUnlocked']
]);
?>
