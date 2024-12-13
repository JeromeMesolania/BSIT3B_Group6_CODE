<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

// Include MongoDB connection
require '../vendor/autoload.php';
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->CODE;
$coursesCollection = $database->courses; // The 'courses' collection

// Debug POST data
error_log("POST Data: " . print_r($_POST, true));

// Check if the courseId is set in the POST request
if (isset($_POST['courseId']) && !empty($_POST['courseId'])) {
    $courseId = $_POST['courseId'];

    // Validate the ObjectId format
    if (preg_match('/^[a-f\d]{24}$/i', $courseId)) {
        try {
            // Retrieve the course details from the 'courses' collection
            $course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($courseId)]);

            // Debug course query result
            error_log("Course Query Result: " . print_r($course, true));

            if ($course && isset($course['instructorId'])) {
                $instructorId = $course['instructorId'];

                // Redirect to the instructor message page with the instructorId
                header("Location: message_instructor.php?instructorId=" . urlencode($instructorId));
                exit();
            } else {
                // Course or instructor not found
                error_log("Instructor not found for Course ID: " . $courseId);
                header("Location: error404.html");
                exit();
            }
        } catch (Exception $e) {
            // Handle MongoDB exception
            error_log("MongoDB Error: " . $e->getMessage());
            header("Location: error404.html");
            exit();
        }
    } else {
        // Invalid ObjectId format
        error_log("Invalid ObjectId: " . htmlspecialchars($courseId));
        header("Location: error404.html");
        exit();
    }
} else {
    // Missing or empty courseId
    error_log("Course ID is missing or empty.");
    header("Location: error404.html");
    exit();
}
?>
