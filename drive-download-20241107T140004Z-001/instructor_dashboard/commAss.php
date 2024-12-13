<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Udemy Style Sidebar</title>
    <link rel="stylesheet" href="commAss.css">
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

<div class="left-navigation">
    <a href="communication.php" class="nav-item active">Q&A</a>
    <a href="commMessage.php" class="nav-item">Messages</a>
    <a href="commAss.php" class="nav-item">Assignments</a>
    <a href="commAnce.php" class="nav-item">Announcements</a>
</div>

<div class="main-content">
  
    <a href="your-link.html" class="notification-container">
        <span class="text">Student</span>
        <span class="icon">🔔</span>
        <span class="profile">J</span>
    </a>
    <div class="container">
    <div class="header">
      <h1>Assignments:</h1>
      <div class="filters">
        <label for="courseFilter">Course Filter:</label>
<select id="courseFilter">
  <option value="all">All courses</option>
</select>
<label for="sharingFilter">Sharing Filter:</label>
        <select id="sharingFilter">
          <option value="all">Sharing preference: All</option>
        </select>
        <label for="feedbackFilter">Feedback Filter:</label>
        <select id="feedbackFilter">
          <option value="all">Feedback type: All</option>
        </select>
        <label for="sortFilter">Sort Filter:</label>
        <select id="sortFilter">
          <option value="newest">Sort by: Newest first:</option>
        </select>
      </div>
    </div>
    <div class="content">
      <img src="/pics/try" alt="No results illustration">
      <p>No results</p>
      <p>Try a different filter</p>
    </div>