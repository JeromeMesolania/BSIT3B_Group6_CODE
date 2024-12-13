<?php
if (isset($_GET['logout'])) {
    session_start();
    session_unset(); 
    session_destroy(); 

    // Prevent caching and ensure the user cannot go back after logout
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    header("Pragma: no-cache"); // HTTP/1.0

    header("Location: ../test/index.php");
    exit();
}

// Include database connection
require '../connection/db_connection.php';

// MongoDB connection
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->CODE;

// Fetch counts
$studentsCount = $database->users->countDocuments(['role' => 'student']);
$instructorsCount = $database->users->countDocuments(['role' => 'instructor']);
$salesCount = $database->sales->countDocuments(); // Assuming 'sales' collection holds sales data

$salesCursor = $database->sales->find([], ['projection' => ['price' => 1]]);
$totalRevenue = 0;

foreach ($salesCursor as $sale) {
    $totalRevenue += $sale['price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <!-- overlay -->
    <div id="sidebar-overlay" class="overlay w-100 vh-100 position-fixed d-none"></div>

    <!-- sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="col-md-9 col-lg-10 ml-md-auto px-0 ms-md-auto">
        <!-- top nav -->
        <nav class="w-100 d-flex px-4 py-2 mb-4 shadow-sm">
            <!-- close sidebar -->
            <button class="btn py-0 d-lg-none" id="open-sidebar">
                <span class="bi bi-list text-primary h3"></span>
            </button>
            <div class="dropdown ml-auto">
                <button class="btn py-0 d-flex align-items-center" id="logout-dropdown" data-toggle="dropdown" aria-expanded="false">
                    <span class="bi bi-person text-primary h4"></span>
                    <span class="bi bi-chevron-down ml-1 mb-2 small"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow-sm" aria-labelledby="logout-dropdown">
                    <a class="dropdown-item" href="../logout/logout.php">Logout</a>
                    <a class="dropdown-item" href="#">Settings</a>
                </div>
            </div>
        </nav>

        <!-- main content -->
        <main class="p-4 min-vh-100">
            <section class="row">
                <!-- Students Count -->
                <div class="col-md-6 col-lg-4">
                    <article class="p-4 rounded shadow-sm border-left mb-4 bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="bi bi-box h5"></span>
                            <h5 class="ml-2">Students</h5>
                        </div>
                        <h2 class="mt-3"><?php echo $studentsCount; ?></h2>
                    </article>
                </div>

                <!-- Instructors Count -->
                <div class="col-md-6 col-lg-4">
                    <article class="p-4 rounded shadow-sm border-left mb-4 bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="bi bi-person h5"></span>
                            <h5 class="ml-2">Instructors</h5>
                        </div>
                        <h2 class="mt-3"><?php echo $instructorsCount; ?></h2>
                    </article>
                </div>

                <!-- Sales Count -->
                <div class="col-md-6 col-lg-4">
                    <article class="p-4 rounded shadow-sm border-left mb-4 bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="bi bi-person-check h5"></span>
                            <h5 class="ml-2">Sales</h5>
                        </div>
                        <h2 class="mt-3"><?php echo $salesCount; ?></h2>
                    </article>
                </div>

                <div class="col-md-6 col-lg-4">
    <article class="p-4 rounded shadow-sm border-left mb-4 bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <span class="bi bi-person-check h5"></span>
            <h5 class="ml-2">Revenue</h5>
        </div>
        <h2 class="mt-3">₱<?php echo number_format($totalRevenue, 2); ?></h2>
    </article>
</div>
            </section>
        </main>
    </div>
    <script src="admin.js"></script>
</body>
</html>
