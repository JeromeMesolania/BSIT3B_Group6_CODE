<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$usersCollection = $database->selectCollection('users');  // Define the collection
$enrolledCollection = $database->selectCollection('enrolledCourses');
$coursesCollection = $database->selectCollection('courses');
$progressCollection = $database->selectCollection('progress');
$user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectID($userId)]);


if ($user) {
    $firstname = $user['firstname'] ?? 'Unknown';
    $lastname = $user['lastname'] ?? 'User';
    $userEmail = $user['email'] ?? 'No email';
} else {
    $firstname = 'Unknown';
    $lastname = 'User';
    $userEmail = 'No email';
}

// Fetch all enrolled courses for the user
$enrolledCourses = $enrolledCollection->find(['userId' => $userId]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Platform</title>
    <link rel="stylesheet" href="mylearning2.css">
</head>
<body>


    <nav style="border-color: black" class="navbar">
        <div class="navbar-logo">
            <img src="haha.png" alt="Code Logo">
        </div>
        <div class="navbar-logo">
            <img src="code logo.png" alt="Code Logo">
        </div>
    
      
        <div style="font-weight: bold" class="navbar-item12">
            <a href="community.php">Community</a>
        </div>
        <div style="font-weight: bold" class="navbar-item11">
            <a href="dashboard26.php">Home</a>
        </div>
        <div class="navbar-item1">
            <input style="border-color:black" type="text" placeholder="Search for anything">
        </div>
        <div style="font-weight: bold" class="navbar-item2">
            <a href=" mylearning.php">My Learning</a>
        </div>
        <div class="navbar-item3">
    <a href="#" class="user-link">
        <img src="user.png" alt="User" class="user-icon">
    </a>
    <div class="dropdown-menu">
        <div class="user-info">
            <img src="user.png" alt="User" class="user-avatar">
            <p class="user-name"><?php echo $firstname . ' ' . $lastname; ?></p>
            <p class="user-email"><?php echo $userEmail; ?></p>
        </div>
        <ul style="font-weight: bold" class="menu-list">
            <li><a href="mylearning.php">My learning</a></li>
            <li><a href="cart.php">My cart</a></li>
            <hr>
            <li><a href="#">Notifications</a></li>
            <li><a href="messages.php">Messages</a></li>
            <hr>
            <li><a href="editAccount.php">Account settings</a></li>
            <li><a href="#">Subscriptions</a></li>
            <li><a href="#">Purchase history</a></li>
            <li class="logout"><a href="../logout/logout.php">Logout</a></li>
        </ul>
    </div>
</div>   
        <div class="navbar-item4">
            <a href="cart.php"><img src="shopping-cart.png" alt="User" class="user-icon"></a>
        </div>
        <div class="navbar-item5">
            <a href="notifications.php"><img src="bell1.png" alt="User" class="user-icon"></a>
        </div>
        <div class="navbar-item6">
            <a href="message_instructor.php" onclick="openChat()"><img src="messenger.png" alt="User" class="user-icon"></a>
        </div>
    </nav>    


    <div style="font-weight: bold" class="category-links">
    <a href="category.php?category=Web%20Development">Web Development</a>
    <a href="category.php?category=IT%20&%20Software">IT & Software</a>
    <a href="category.php?category=UI/UX%20Design">UI/UX Design</a>
    <a href="category.php?category=CyberSecurity">CyberSecurity</a>
    <a href="category.php?category=Cloud%20Computing">Cloud Computing</a>
    <a href="category.php?category=Internet%20of%20Things%20(IoT)">Internet of Things (IoT)</a>
</div>

<div class="learning-header">
    <div class="learning-title">My Learning</div>
    <div class="learning-links">
        <a href="#">All Courses</a>
        <a href="#">My Lists</a>
        <a href="#">Wishlist</a>
        <a href="#">Archived</a>
        <a href="#">Learning tools</a>
    </div>
</div>
<div class="schedule-learning">
    <div class="schedule-icon">🕒</div>
    <div class="schedule-content">
        <h2>Schedule learning time</h2>
        <p>Learning a little each day adds up. Research shows that students who make learning a habit are more likely to reach their goals. Set time aside to learn and get reminders using your learning schedule.</p>
        <div class="schedule-buttons">
            <button class="get-started">Get Started</button>
            <button class="dismiss">Dismiss</button>
        </div>
    </div>
</div>

</div>

<div class="course-list">
    <?php foreach ($enrolledCourses as $enrollment): ?>
        <?php 
        $course = $coursesCollection->findOne(['_id' => $enrollment['courseId']]);
        $progress = $progressCollection->findOne(['userId' => $userId, 'courseId' => $enrollment['courseId']]);
        $assessmentUnlocked = $progress['assessmentUnlocked'] ?? false;
        ?>

        <?php if ($course): ?>
            <div class="course-item" id="course-<?php echo $course['_id']; ?>" data-course-id="<?php echo $course['_id']; ?>">
                <h2><?php echo htmlspecialchars($course['title']); ?></h2>
                <p><?php echo htmlspecialchars($course['description']); ?></p>
                <div class="course-video">
                    <?php if (isset($course['videoUrl']) && !empty($course['videoUrl'])): ?>
                        <video id="courseVideo" src="<?php echo htmlspecialchars($course['videoUrl']); ?>" controls loop autoplay></video>
                    <?php else: ?>
                        <p>No video available for this course.</p>
                    <?php endif; ?>
                </div>

                <button id="assessmentBtn" <?php echo $assessmentUnlocked ? '' : 'disabled'; ?>>
                    <?php echo $assessmentUnlocked ? 'Take Assessment' : 'Assessment Locked'; ?>
                </button>

                <!-- Rating Stars as Buttons -->
                <div class="rating" data-course-id="<?php echo $course['_id']; ?>">
                    <?php 
                    $userRating = null;
                    if (isset($course['rating'])) {
                        foreach ($course['rating'] as $rating) {
                            if ($rating['userId'] === $userId) {
                                $userRating = $rating['rating'];
                                break;
                            }
                        }
                    }
                    ?>
                    <span class="star" data-rating="1">&#9733;</span>
                    <span class="star" data-rating="2">&#9733;</span>
                    <span class="star" data-rating="3">&#9733;</span>
                    <span class="star" data-rating="4">&#9733;</span>
                    <span class="star" data-rating="5">&#9733;</span>
                    <input type="hidden" id="course-rating-<?php echo $course['_id']; ?>" value="<?php echo $userRating ?? ''; ?>">
                </div>

                <a href="coursedetail.php?id=<?php echo $course['_id']; ?>">Go to Course</a>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>



<button id="finishVideoBtn" style="display:none;" onclick="markVideoAsFinished()">Mark as Finished</button>
<button <?php echo $assessmentUnlocked ? '' : 'disabled'; ?>>
<?php echo $assessmentUnlocked ? 'Take Assessment' : 'Assessment Locked'; ?>
</button>
<script src="script.js"></script>
<script>

function openChat() {
            document.getElementById('messageBox').style.display = 'block';
        }

        function closeChat() {
            document.getElementById('messageBox').style.display = 'none';
        }

        document.getElementById("videoPlayer").addEventListener("ended", () => {
});

document.addEventListener('DOMContentLoaded', () => {
    const video = document.getElementById('courseVideo');
    const courseId = document.querySelector('.course-item').dataset.courseId;
    const videoId = video.dataset.videoId; // Make sure to add `data-video-id` to the video element

    video.addEventListener('timeupdate', () => {
        const progress = (video.currentTime / video.duration) * 100;

        // Send progress update every 10% or when the video ends
        if (progress % 10 < 1 || video.ended) {
            updateVideoProgress(courseId, videoId, progress);
        }
    });
});

function updateVideoProgress(courseId, videoId, progress) {
    fetch('update_video_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ courseId, videoId, progress })
    });
}

</script>
</body>
</html>