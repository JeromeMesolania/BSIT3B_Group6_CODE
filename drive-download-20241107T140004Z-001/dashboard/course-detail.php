<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to error page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

// Fetch course details based on the ID passed in the URL
$courseId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;

if (!$courseId) {
    header("Location: error404.html");
    exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$coursesCollection = $database->selectCollection('courses');

$course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($courseId)]);

if (!$course) {
    header("Location: error404.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="stylesheet" href="course-detail.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-logo">
            <img src="haha.png" alt="Code Logo">
        </div>
        <div class="navbar-logo">
            <img src="code logo.png" alt="Code Logo">
        </div>
        <div class="navbar-item12">
            <a href="#">Community</a>
        </div>
        <div class="navbar-item11">
            <a href="#">Home</a>
        </div>
        <div class="navbar-item1">
            <input type="text" placeholder="Search for anything">
        </div>
        <div class="navbar-item2">
            <a href="#">My Learning</a>
        </div>
        <div class="navbar-item3">
            <a href="#" class="user-link">
                <img src="user.png" alt="User" class="user-icon">
            </a>
            <a href="#" class="logout-button">Logout</a>
        </div>
        <div class="navbar-item4">
            <a href="#" class="cart-link">
                <img src="shopping-cart.png" alt="Shopping Cart" class="user-icon">
            </a>
            <a href="#" class="cart-text">Cart</a>
        </div>
        <div class="navbar-item5">
            <a href="#" class="notification-link">
                <img src="bell1.png" alt="Notifications" class="user-icon">
                <span class="notification-text">Notifications</span>
            </a>
        </div>
    </nav>

    <div class="navbar-item6">
        <a href="#"><img src="messenger.png" alt="User" class="user-icon"></a>
        <div class="dropdown" id="dropdown">
            <p id="message1">Message from Alice</p>
        </div>
    </div>

    <div class="chatbox" id="chatbox">
        <div class="chatbox-header">Chat with Jerome</div>
        <div class="chatbox-body" id="chatboxBody">
            <p>Jerome: Hello! How are you?</p>
        </div>
        <div class="chatbox-footer">
            <input type="text" id="chatInput" placeholder="Type a message...">
            <button id="sendButton">Send</button>
        </div>
    </div>

    <!-- Main Course Details Section -->
    <section class="course-details">
        <div class="course-info">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="course-description">
                <?php echo htmlspecialchars($course['description']); ?>
            </p>
            <p class="course-meta">
                <span class="rating">4.4 ★★★★★ (150 ratings)</span>
                <span class="students">52,729 students</span>
            </p>
            <p class="course-creator">
                Created by: <a href="#"><?php echo htmlspecialchars($course['instructorName']); ?></a>
            </p>
            <p class="course-updated">
                Last updated: 5/2023
            </p>
            <div class="breadcrumb">
                <a href="index.html">Development</a> &gt;
                <a href="category.html?category=Mobile%20App%20Development">Mobile App Development</a>
            </div>
        </div>

        <!-- Pricing and Preview Section -->
        <div class="course-preview">
            <div class="video-thumbnail" style="background-image: url('<?php echo htmlspecialchars($course['thumbnail']); ?>');">
                <span class="play-icon">&#9658; Preview this course</span>
            </div>
            <div class="pricing">
                <p class="price">₱<?php echo htmlspecialchars($course['price']); ?></p>
                <button class="add-to-cart">Add to cart</button>
                <button class="buy-now">Buy now</button>
                <p class="guarantee">30-Day Money-Back Guarantee</p>
            </div>
            <div class="course-includes">
                <h3>This course includes:</h3>
                <ul>
                    <li>8 hours on-demand video</li>
                    <li>1 article</li>
                    <li>4 downloadable resources</li>
                    <li>Access on mobile and TV</li>
                    <li>Full lifetime access</li>
                    <li>Certificate of completion</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- What You'll Learn Section -->
    <section class="what-you-learn">
        <h2>What you'll learn</h2>
        <ul>
            <li>Make many real working apps that work properly, look great, and are up to date with best practice in 2023</li>
            <li>Switch careers and get a job as an Android Developer</li>
            <li>You will be able to develop modern Android apps</li>
            <li>Go from a complete beginner to a real Android App Developer</li>
            <li>Make beautiful, professional Android apps</li>
        </ul>
    </section>
</body>
</html>
