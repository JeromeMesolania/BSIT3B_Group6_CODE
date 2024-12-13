<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

$enrolledCoursesCollection = $db->enrolledCourses;  // Access the 'enrolledCourses' collection
$enrolledCourses = $enrolledCoursesCollection->find();  // Fetch all enrolled courses
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include the sidebar -->
    <div class="col-md-9 col-lg-10 ml-md-auto px-0 ms-md-auto">
        <!-- Main Content -->
        <main class="p-4 min-vh-100">
            <h1>Sales</h1>
            <?php foreach ($enrolledCourses as $course): 
                $coursePrice = isset($course['price']) ? $course['price'] : 0;
                $adminCommission = $coursePrice * 0.10; // 10% for admin commission
                $instructorRevenue = $coursePrice * 0.90; // 90% for the instructor
            ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Course ID: <?= htmlspecialchars($course['courseId']) ?></h5>
                        <p class="card-text">Amount: ₱<?= number_format($coursePrice, 2) ?></p>
                        <p class="card-text">Admin Commission (10%): ₱<?= number_format($adminCommission, 2) ?></p>
                        <p class="card-text">Instructor Revenue (90%): ₱<?= number_format($instructorRevenue, 2) ?></p>
                        <p class="card-text">Date: <?= htmlspecialchars($course['enrollmentDate']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
    </div>
    <script src="admin.js"></script>
</body>
</html>
