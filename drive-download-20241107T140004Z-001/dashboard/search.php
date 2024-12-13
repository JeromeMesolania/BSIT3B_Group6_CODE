<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require '../vendor/autoload.php';
require '../connection/db_connection.php';

use MongoDB\BSON\ObjectId;

$userId = $_SESSION['user_id'];
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$coursesCollection = $database->selectCollection('courses');
$cartCollection = $database->selectCollection('cart');

$cartCursor = $cartCollection->find(['userId' => $userId])->toArray();

// Get the search query from the GET request
$searchQuery = isset($_GET['query']) ? $_GET['query'] : '';

// MongoDB query to search for courses or instructors based on the search query
$searchFilter = [];
if (!empty($searchQuery)) {
    $searchFilter = [
        '$or' => [
            ['title' => new MongoDB\BSON\Regex($searchQuery, 'i')],
            ['category' => new MongoDB\BSON\Regex($searchQuery, 'i')],
            ['instructorName' => new MongoDB\BSON\Regex($searchQuery, 'i')] // Search for instructor name as well
        ]
    ];
}

$coursesCursor = $coursesCollection->aggregate([
    [
        '$match' => array_merge(['status' => 'accepted'], $searchFilter)
    ],
    [
        '$addFields' => [
            'instructorId' => [
                '$cond' => [
                    'if' => ['$eq' => [ ['$type' => '$instructorId'], 'string']],
                    'then' => ['$toObjectId' => '$instructorId'],
                    'else' => '$instructorId'
                ]
            ]
        ]
    ],
    [
        '$lookup' => [
            'from' => 'users',
            'localField' => 'instructorId',
            'foreignField' => '_id',
            'as' => 'instructor'
        ]
    ],
    [
        '$unwind' => '$instructor'
    ],
    [
        '$project' => [
            'title' => 1,
            'description' => 1,
            'price' => 1,
            'videoUrl' => 1,
            'thumbnailUrl' => 1,
           'instructorName' => [
    '$concat' => ['$instructor.firstname', ' ', '$instructor.lastname']],
            'category' => 1,
            'instructorProfile' => '$instructor.profilePicture'
        ]
    ]
])->toArray();

$courseDetails = [];
if (!empty($cartCursor)) {
    foreach ($cartCursor as $cartItem) {
        if (isset($cartItem['courses']) && is_array($cartItem['courses'])) {
            foreach ($cartItem['courses'] as $courseId) {
                if (!empty($courseId) && is_string($courseId)) {
                    try {
                        $course = $coursesCollection->findOne(['_id' => new ObjectId($courseId)]);
                        if ($course) {
                            $courseDetails[] = $course;
                        }
                    } catch (Exception $e) {
                        error_log("Invalid ObjectId: " . $courseId);
                    }
                }
            }
        }
    }
}

$studentName = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        .message-button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 16px;
}

.message-button:hover {
    background-color: #45a049;
}

    </style>
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
            <a href="../dashboard/community.php">Community</a>
        </div>
        <div class="navbar-item11">
            <a href="../dashboard/dashboard26.php">Home</a>
        </div>
        <div class="navbar-item1">
            <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="Search for anything" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
            </form>
        </div>
        <div class="navbar-item2">
            <a href="../dashboard/mylearning.php">My Learning</a>
        </div>
        <div class="navbar-item3">
            <a href="#" class="user-icon-link">
                <img src="user.png" alt="User" class="user-icon">
            </a>
            <div class="logout-dropdown">
                <a href="../logout/logout.php">Logout</a>
            </div>
        </div>
        <div class="navbar-item4">
            <a href="#" class="cart-link">
                <img src="../dashboard/shopping-cart.png" alt="Shopping Cart" class="user-icon">
            </a>
            <a href="#" class="cart-text"></a>
        </div>
        <div class="navbar-item5">
            <a href="#" class="notification-link">
                <img src="../dashboard/bell1.png" alt="Notifications" class="user-icon">
                <span class="notification-text"></span>
            </a>
        </div>
        
    </nav>
    <div class="navbar-item6">
        <a href="message_instructor.php"><img src="../dashboard/messenger.png" alt="User" class="user-icon"></a>
        <div class="dropdown" id="dropdown">
            <p id="message1">Message from Alice</p>
        </div>
    </div>
</nav>

<div class="category-links">
    <a href="category.php?category=Web%20Development">Web Development</a>
    <a href="category.php?category=IT%20&%20Software">IT & Software</a>
    <a href="category.php?category=UI/UX%20Design">UI/UX Design</a>
    <a href="category.php?category=CyberSecurity">CyberSecurity</a>
    <a href="category.php?category=Cloud%20Computing">Cloud Computing</a>
    <a href="category.php?category=Internet%20of%20Things%20(IoT)">Internet of Things (IoT)</a>
</div>

<h1>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h1>

<div class="carousel-container">
    <div class="carousel-items">
        <?php if (empty($coursesCursor)): ?>
            <p>No courses found matching your search.</p>
        <?php else: ?>
            <?php foreach ($coursesCursor as $course): ?>
                <div class="instructor-card">
                    <div class="carousel-item">
                        <a href="coursedetail.php?id=<?php echo htmlspecialchars($course['_id']); ?>" class="course-card">
                            <div class="thumbnail" style="background-image: url('<?php echo htmlspecialchars($course['thumbnailUrl']); ?>');"></div>
                            <div class="course-info">
                                <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                                <p><strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructorName']); ?></p>
                                <p>Price: ₱<?php echo htmlspecialchars($course['price']); ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<footer class="footer">
    <p>Teach the world online</p>
    <p>Create an online video course, reach students across the globe, and earn money.</p>
    <p>Top companies choose Code Business to build in-demand career skills.</p>
</footer>

<script>
    document.getElementById('searchInput').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        // Handle the search query (you can add your search functionality here)
        let searchQuery = event.target.value;
        // Redirect to a search results page or filter courses based on the query
        window.location.href = `searchResults.php?query=${encodeURIComponent(searchQuery)}`;
    }
});

</script>
</body>
</html>
