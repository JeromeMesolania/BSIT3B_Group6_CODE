<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

require '../vendor/autoload.php'; 
include '../navbar/navbar.php';

$coursesCollection = $database->selectCollection('courses'); 

// Fetch the category from query parameter
$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';

// Fetch courses based on the category
$cursor = $coursesCollection->find(['category' => $category])->toArray();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category; ?> Videos</title>
    <link rel="stylesheet" href="dashboard5.css">
</head>
<body>
   
    <h1>Category: <?php echo htmlspecialchars($category); ?></h1>

    <div class="videos">
        <?php
        if (count($cursor) > 0) {
            foreach ($cursor as $course) {
                // Display video thumbnail with title and instructor's name
                echo '<div class="video-item">';
                echo '<a href="watch.php?videoId=' . urlencode($course['_id']) . '">';
                echo '<img src="' . htmlspecialchars($course['thumbnailUrl']) . '" alt="' . htmlspecialchars($course['title']) . ' Thumbnail" width="300" height="170">';
                echo '</a>';
                echo '<p>' . htmlspecialchars($course['title']) . '</p>';
                echo '<p>Instructor: ' . htmlspecialchars($course['instructorName'] ?? 'Unknown') . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No videos available in this category.</p>';
        }
        ?>
    </div>
</body>
</html>
