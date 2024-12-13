<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';


$collection = $db->users;  // Access the 'users' collection
$instructors = $collection->find(['role' => 'instructor']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include the sidebar -->
    <div class="col-md-9 col-lg-10 ml-md-auto px-0 ms-md-auto">
        <!-- Main Content -->
        <main class="p-4 min-vh-100">
            <h1>Instructors</h1>
            <?php foreach ($instructors as $instructor): ?>
                <div class="card mb-3">
                    <img src="<?= $instructor['profilePicture'] ?>" class="card-img-top" alt="Profile Picture" style="width: 100px; height: 100px;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($instructor['firstname'] . ' ' . $instructor['lastname']) ?></h5>
                        <p class="card-text">Email: <?= htmlspecialchars($instructor['email']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
    </div>
    <script src="admin.js"></script>
</body>
</html>
