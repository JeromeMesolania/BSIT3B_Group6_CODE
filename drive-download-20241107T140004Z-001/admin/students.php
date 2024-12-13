<?php
require '../vendor/autoload.php'; 
require '../connection/db_connection.php';

// Optional: Handle logout if needed
if (isset($_GET['logout'])) {
    session_start();
    session_unset(); 
    session_destroy(); 
    header("Location: ../test/index.php");
    exit();
}

$collection = $db->users;  // Access the 'users' collection
$students = $collection->find(['role' => 'student']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include the sidebar -->
    <div class="col-md-9 col-lg-10 ml-md-auto px-0 ms-md-auto">
        <!-- Main Content -->
        <main class="p-4 min-vh-100">
            <h1>Students</h1>
            <?php foreach ($students as $student): ?>
                <div class="card mb-3">
                    <img src="<?= $student['profilePicture'] ?>" class="card-img-top" alt="Profile Picture" style="width: 100px; height: 100px;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></h5>
                        <p class="card-text">Email: <?= htmlspecialchars($student['email']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
    </div>
    <script src="admin.js"></script>
</body>
</html>
