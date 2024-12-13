<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

session_start();
if (isset($_SESSION['user_id'])) {
    $instructorId = $_SESSION['user_id'];
} else {
    die("User is not logged in. Please log in to access the dashboard.");
}

$enrolledCoursesCollection = $db->enrolledCourses;
$coursesCollection = $db->courses;

// Variables for total revenue and total enrollments
$totalRevenue = 0;
$totalEnrollments = 0;

// Fetch all enrolled courses
$enrolledCourses = $enrolledCoursesCollection->find();

foreach ($enrolledCourses as $enrollment) {
    $courseId = $enrollment['courseId'];
    $coursePrice = isset($enrollment['price']) ? $enrollment['price'] : 0;

    // Find the course to check the instructorId
    $course = $coursesCollection->findOne(['_id' => $courseId]);

    if ($course && $course['instructorId'] == $instructorId) {
        $instructorRevenue = $coursePrice * 0.90; // 90% for the instructor
        $totalRevenue += $instructorRevenue;
        $totalEnrollments++;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Udemy Style Sidebar</title>
    <link rel="stylesheet" href="perf_overview.css">
</head>
<div class="sidebar" id="sidebar">
    <a href="instructordashboard.php" class="sidebar-item">
        <img src="haha.png" alt="Code" class="icon">
        <span class="text">Code</span>
    </a>
    <a href="instructordashboard.php" class="sidebar-item">
        <img src="learning.png" alt="Courses" class="icon">
        <span class="text">Courses</span>
    </a>
    <a href="communication.php" class="sidebar-item">
        <img src="speech-bubble.png" alt="Communication" class="icon">
        <span class="text">Communication</span>
    </a>
    <a href="performance.php" class="sidebar-item">
        <img src="speedometer.png" alt="Performance" class="icon">
        <span class="text">Performance</span>
    </a>
</div>

<!-- Left navigation section -->
<div class="left-navigation">
    <a href="perf_overview.php" class="nav-item">Overview</a>
    <a href="perf_students.php" class="nav-item">Students</a>
    <a href="perf_reviews.php" class="nav-item">Reviews</a>
</div>
<div class="main-content">
  
    <a href="your-link.html" class="notification-container">
        <span class="icon">🔔</span>
        <span class="profile">J</span>
    </a>

    <!-- Main Content -->
    <div class="main-content">
        <div class="main-header">
            <h1>Overview</h1>
            <p>Get top insights about your performance</p>
        </div>

        <!-- Cards Section -->
        <div class="cards">
    <div class="card">
        <h5>Total Revenue</h5>
        <h3>₱<?= number_format($totalRevenue, 2) ?></h3>
        <p>₱<?= number_format($totalRevenue, 2) ?> this month</p>
    </div>
    <div class="card">
        <h5>Total Enrollments</h5>
        <h3><?= $totalEnrollments ?></h3>
        <p><?= $totalEnrollments ?> this month</p>
    </div>
    <div class="card">
        <h5>Instructor Rating</h5>
        <h3>0.00</h3>
        <p>0 ratings this month</p>
    </div>
</div>

        <!-- Data Section -->
        <div class="data-section">
            <div class="data-header">
                <h5>No data to display</h5>
                <select>
                    <option>Last 12 months</option>
                    <option>Last 6 months</option>
                    <option>Last 3 months</option>
                </select>
            </div>
            <div class="data-placeholder">
                <p>No data to display</p>
            </div>
        </div>
    </div>
</body>
</html>

