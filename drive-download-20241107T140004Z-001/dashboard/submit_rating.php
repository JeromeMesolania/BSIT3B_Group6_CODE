<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the JSON payload
    $data = json_decode(file_get_contents('php://input'), true);
    $courseId = $data['courseId'];
    $rating = $data['rating'];
    $userId = $_SESSION['user_id']; // Get the user ID from the session

    // Check if the course exists in the database
    $coursesCollection = $database->selectCollection('courses');
    $course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($courseId)]);

    if ($course) {
        // Update the rating for the course and the user
        $enrolledCollection = $database->selectCollection('enrolledCourses');
        $enrolledCollection->updateOne(
            ['userId' => $userId, 'courseId' => new MongoDB\BSON\ObjectId($courseId)],
            ['$set' => ['rating' => $rating]]
        );

        // Return success response
        echo json_encode(['message' => 'Rating submitted successfully!']);
    } else {
        // Course not found, return error response
        echo json_encode(['message' => 'Course not found.']);
    }
}
?>
