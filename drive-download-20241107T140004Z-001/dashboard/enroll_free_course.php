<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];
$courseId = json_decode(file_get_contents('php://input'), true)['courseId'] ?? null;

if (!$courseId) {
    echo json_encode(['success' => false, 'message' => 'Course ID missing']);
    exit();
}

try {
    $objectId = new MongoDB\BSON\ObjectId($courseId);
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('CODE');
    $enrolledCoursesCollection = $database->selectCollection('enrolledCourses');

    // Check if already enrolled
    $existingEnrollment = $enrolledCoursesCollection->findOne([
        'userId' => $userId,
        'courseId' => $objectId
    ]);

    if ($existingEnrollment) {
        echo json_encode(['success' => false, 'message' => 'Already enrolled in this course']);
        exit();
    }

    // Insert enrollment
    $enrolledCoursesCollection->insertOne([
        'userId' => $userId,
        'courseId' => $objectId,
        'enrolledAt' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode(['success' => true, 'message' => 'Enrolled successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
