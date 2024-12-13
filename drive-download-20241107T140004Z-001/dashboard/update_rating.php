<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $courseId = $data['courseId'] ?? null;
    $rating = $data['rating'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    if ($courseId && $rating && $userId) {
        // Validate rating
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Invalid rating']);
            exit();
        }

        $client = new MongoDB\Client("mongodb://localhost:27017");
        $database = $client->selectDatabase('CODE');
        $enrolledCollection = $database->selectCollection('enrolledCourses');

        // Update the rating for the user in the enrolled courses collection
        $result = $enrolledCollection->updateOne(
            ['userId' => new MongoDB\BSON\ObjectID($userId), 'courseId' => new MongoDB\BSON\ObjectId($courseId)],
            ['$set' => ['rating' => $rating]]
        );

        if ($result->getModifiedCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update rating']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
