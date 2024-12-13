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
$courseId = isset($_POST['courseId']) ? trim($_POST['courseId']) : null;

if (!preg_match('/^[a-f\d]{24}$/i', $courseId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Course ID format']);
    exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$myCoursesCollection = $database->selectCollection('myCourses');

try {
    $objectId = new MongoDB\BSON\ObjectId($courseId);

    // Check if the course is already enrolled by the user
    $existingEnrollment = $myCoursesCollection->findOne(['userId' => $userId, 'courseId' => $objectId]);
    
    if ($existingEnrollment) {
        echo json_encode(['success' => false, 'message' => 'Course already enrolled.']);
    } else {
        // Add the course to the user's myCourses collection
        $myCoursesCollection->insertOne([
            'userId' => $userId,
            'courseId' => $objectId,
            'enrollmentDate' => new MongoDB\BSON\UTCDateTime(),
            'status' => 'enrolled'
        ]);
        echo json_encode(['success' => true, 'message' => 'Course enrolled successfully.']);
    }
} catch (Exception $e) {
    error_log("MongoDB Insert Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while enrolling.']);
}
?>
