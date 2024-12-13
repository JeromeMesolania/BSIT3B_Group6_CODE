<?php
require '../connection/db_connection.php';
session_start();

$userId = $_SESSION['user_id'];
$courseId = $_POST['courseId'];

$user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);
$enrolledCourse = array_filter($user['enrolledCourses'], function($course) use ($courseId) {
    return $course['courseId'] === $courseId;
});

if (!$enrolledCourse || $enrolledCourse[0]['assessmentStatus'] !== "unlocked") {
    echo json_encode(["success" => false, "message" => "Assessment is locked. Complete all videos first."]);
    exit();
}

$course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($courseId)]);
echo json_encode(["success" => true, "assessment" => $course['assessments']]);
