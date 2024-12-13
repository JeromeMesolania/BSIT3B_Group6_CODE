<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

$userId = $_SESSION['user_id'];

$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$paymentsCollection = $database->selectCollection('payments');
$coursesCollection = $database->selectCollection('courses');
$enrolledCoursesCollection = $database->selectCollection('enrolledCourses');

// Process payment approval or decline
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['student_id'];
    $action = $_POST['action']; // 'approve' or 'decline'
    $payment = $paymentsCollection->findOne(['userId' => $studentId, 'status' => 'pending']);

    if ($payment) {
        if ($action == 'approve') {
            // Update payment status
            $paymentsCollection->updateOne(
                ['_id' => $payment['_id']],
                ['$set' => ['status' => 'approved']]
            );

            // Add the approved course to the enrolledCourses collection
            $enrolledCoursesCollection->insertOne([
                'userId' => $studentId,
                'courseId' => $payment['courseId'],
                'enrollmentDate' => new MongoDB\BSON\UTCDateTime()
            ]);
        } elseif ($action == 'decline') {
            // Update payment status to declined
            $paymentsCollection->updateOne(
                ['_id' => $payment['_id']],
                ['$set' => ['status' => 'declined']]
            );
        }
    }
}
?>
