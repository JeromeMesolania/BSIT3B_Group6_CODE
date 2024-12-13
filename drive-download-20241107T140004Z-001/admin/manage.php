<?php
require '../connection/db_connection.php';
require '../vendor/autoload.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard/error404.html");
    exit();
}

use MongoDB\Client as MongoDBClient;

// MongoDB connection
$mongoClient = new MongoDBClient("mongodb://localhost:27017");
$db = $mongoClient->CODE;
$coursesCollection = $db->courses;

// Handle admin actions (accept, decline, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['courseId'])) {
    $courseId = new MongoDB\BSON\ObjectId($_POST['courseId']);
    $action = $_POST['action'];

    if ($action === 'accept') {
        $coursesCollection->updateOne(['_id' => $courseId], ['$set' => ['status' => 'accepted']]);
    } elseif ($action === 'decline') {
        $coursesCollection->updateOne(['_id' => $courseId], ['$set' => ['status' => 'declined']]);
    } elseif ($action === 'delete') {
        $coursesCollection->deleteOne(['_id' => $courseId]);
    }

    header("Location: manage.php"); // Refresh the page after the action
    exit();
}

// Fetch all courses
$courses = $coursesCollection->find()->toArray();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <!-- Include the sidebar -->
    <?php include 'sidebar.php'; ?>
    <div class="col-md-9 col-lg-10 ml-md-auto px-0 ms-md-auto">
        <main class="p-4 min-vh-100">
            <h1>Manage Courses</h1>
            <p>Below is the list of courses submitted by instructors:</p>

            <!-- Courses Table -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Instructor</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['category']); ?></td>
                                <td><?php echo htmlspecialchars($course['instructorName'] ?? 'Unknown'); ?></td>
                                <td><?php echo ucfirst($course['status'] ?? 'pending'); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="courseId" value="<?php echo (string)$course['_id']; ?>">
                                        <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">Accept</button>
                                        <button type="submit" name="action" value="decline" class="btn btn-warning btn-sm">Decline</button>
                                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No courses found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
