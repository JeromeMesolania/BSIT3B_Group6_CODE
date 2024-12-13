<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Udemy Style Sidebar</title>
    <link rel="stylesheet" href="perf_reviews.css">
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
     <!-- Header Section -->
     <div class="header3">
        <h1>Reviews</h1>
        <div class="dropdown">All courses &#x25BC;</div>
    </div>

    <!-- Filter Section -->
    <div class="filters">
        <label><input type="checkbox"> Not answered</label>
        <label><input type="checkbox"> Has a comment</label>
        <span>Rating:</span>
        <select>
            <option>All</option>
            <option>5 stars</option>
            <option>4 stars</option>
            <option>3 stars</option>
            <option>2 stars</option>
            <option>1 star</option>
        </select>
        <span>Sort by:</span>
        <select>
            <option>Newest first</option>
            <option>Oldest first</option>
            <option>Highest rated</option>
            <option>Lowest rated</option>
        </select>
        <button class="export-btn">Export to CSV...</button>
    </div>

    <!-- Notice Section -->
    <div class="notice">
        <div class="icon"></div>
        <span>It can take up to 48 hours for approved student ratings to show on your course landing page.</span>
    </div>

    <!-- No Reviews Message -->
    <div class="no-reviews">
        No reviews found
    </div>
</body>
</html>