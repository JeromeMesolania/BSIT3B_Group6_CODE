<?php
require '../vendor/autoload.php'; // MongoDB library

// Connect to MongoDB
function connectMongoDB() {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    return $client->selectDatabase('CODE'); // Connect to the 'CODE' database
}

$db = connectMongoDB(); // Get the database object

// Query the 'payments' collection for pending payments
$paymentsCollection = $db->payments; // Access the 'payments' collection
$query = ['status' => 'pending']; // Fetch only pending payments
$cursor = $paymentsCollection->find($query); // Fetch the data

// Convert the cursor to an array
$payments = iterator_to_array($cursor); // Store results in an array

// Fetch student names from the 'users' collection
$usersCollection = $db->users; // Access the 'users' collection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Udemy Style Sidebar</title>
    <link rel="stylesheet" href="perf_students1.css">
</head>

<body>
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

        <!-- Header Section -->
        <div class="header1">
            <h1>Students</h1>
            <div class="dropdown">All courses &#x25BC;</div>
        </div>

        <!-- Display pending payments table if there are any payments -->
        <div class="table-container">
            <h1>Students' Payment Proofs</h1>
            <?php if (count($payments) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Proof of Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $row): 
                        // Fetch student name using the userId
                        $student = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($row['userId'])]);
                        $studentName = isset($student['firstname']) && isset($student['lastname']) ? $student['firstname'] . ' ' . $student['lastname'] : 'Unknown Student';
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($studentName); ?></td>
                            <td>
                                <img src="../dashboard/uploads/<?php echo htmlspecialchars($row['proofPath']); ?>" alt="Proof of Payment" width="150">
                            </td>
                            <td>
                                <form method="post" action="process_payment.php">
                                    <input type="hidden" name="student_id" value="<?php echo $row['userId']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn approve-btn">Approve</button>
                                    <button type="submit" name="action" value="decline" class="btn decline-btn">Decline</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending payment proofs at the moment.</p>
        <?php endif; ?>
    </div>
    </div>
</body>
</html>
