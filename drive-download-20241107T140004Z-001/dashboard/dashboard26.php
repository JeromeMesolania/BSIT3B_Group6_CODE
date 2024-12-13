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
$role = $_SESSION['role'] ?? 'student';

$userId = $_SESSION['user_id'];
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$coursesCollection = $database->selectCollection('courses');
$cartCollection = $database->selectCollection('cart');
$usersCollection = $database->selectCollection('users');

$cartCursor = $cartCollection->find(['userId' => $userId])->toArray();

try {
    // Query the user collection based on the user ID and role
    $user = $usersCollection->findOne([
        '_id' => new ObjectId($userId),
        'role' => $role
    ]);

    if ($user) {
        $firstname = htmlspecialchars($user['firstname']);
        $lastname = htmlspecialchars($user['lastname']);
        $userEmail = htmlspecialchars($user['email']);
    } else {
        $firstname = 'Unknown';
        $lastname = 'User';
        $userEmail = 'Unknown Email';
    }
} catch (Exception $e) {
    $firstname = 'Error';
    $lastname = 'Fetching User';
    $userEmail = 'Error Fetching Email';
}
$coursesCursor = $coursesCollection->aggregate([
    [
        '$match' => ['status' => 'accepted']
    ],
    
    [
        '$addFields' => ['instructorId' => ['$toObjectId' => '$instructorId']]
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
            'instructorName' => 1
            ]
    ]
    
])->toArray();

$courseDetails = [];

// Check if cart items exist
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
                        // Log error or handle invalid ObjectId gracefully
                        error_log("Invalid ObjectId: " . $courseId);
                    }
                }
            }
        }
    }
}

$cartItems = [];
foreach ($cartCursor as $cartItem) {
    if (isset($cartItem['courses']) && is_array($cartItem['courses'])) {
        foreach ($cartItem['courses'] as $courseId) {
            $course = $coursesCollection->findOne(['_id' => new ObjectId($courseId)]);
            if ($course) {
                $cartItems[] = [
                    'title' => $course['title'],
                    'price' => $course['price'],
                    'instructor' => $course['instructorName']
                ];
            }
        }
    }
}

// Fetch highest-rated courses
$highestRatedCourses = $coursesCollection->aggregate([
    ['$match' => ['status' => 'accepted']],
    ['$sort' => ['rating' => -1]], // Sort by rating descending
    ['$limit' => 3] // Get the top 3 highest-rated courses
])->toArray();

// Fetch lowest-rated courses
$lowestRatedCourses = $coursesCollection->aggregate([
    ['$match' => ['status' => 'accepted']],
    ['$sort' => ['rating' => 1]], // Sort by rating ascending
    ['$limit' => 3] // Get the top 3 lowest-rated courses
])->toArray();

$shortestCourses = $coursesCollection->aggregate([
    [
        '$match' => ['status' => 'accepted']
    ],
    [
        '$sort' => ['duration' => 1] // Sort by duration (shortest to longest)
    ],
    [
        '$limit' => 5 // Limit to 5 courses (you can adjust this number)
    ]
])->toArray();

$studentName = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Platform</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        .footer {
    background-color: #000;
    color: #fff;
    padding: 40px 20px;
    font-family: Arial, sans-serif;
}

.footer-sections {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    max-width: 1000px;
    margin: 0 auto;
}

.footer-column {
    margin-bottom: 20px;
    flex: 1;
    min-width: 200px;
}

.footer-column h3 {
    font-size: 1.1em;
    margin-bottom: 15px;
    font-weight: bold;
}

.footer-column ul {
    list-style: none;
    padding: 0;
}

.footer-column ul li {
    margin-bottom: 10px;
}

.footer-column ul li a {
    color: #fff;
    text-decoration: none;
    opacity: 0.8;
    transition: opacity 0.3s;
}

.footer-column ul li a:hover {
    opacity: 1;
}

.footer-bottom {
    text-align: center;
    margin-top: 40px;
    border-top: 1px solid #444;
    padding-top: 20px;
}

.footer-bottom p {
    margin: 10px 0;
    font-size: 0.9em;
}

.footer-bottom a {
    color: #fff;
    text-decoration: none;
    opacity: 0.8;
    transition: opacity 0.3s;
}

.footer-bottom a:hover {
    opacity: 1;
}

    </style>
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
            <a href="../dashboard/community.php">Community</a>
        </div>
        <div style="font-weight: bold" class="navbar-item11">
            <a href="../dashboard/dashboard26.php">Home</a>
        </div>
        <div class="navbar-item1">
            <form action="search.php" method="GET">
                <input style="border-color: black" type="text" name="query" placeholder="Search for anything" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
            </form>
        </div>

        <div style="font-weight: bold" style="font-weight: bold" class="navbar-item2">
            <a href="../dashboard/mylearning.php">My Learning</a>
        </div>

<div class="navbar-item3">
    <a href="#" class="user-link">
        <img src="user.png" alt="User" class="user-icon">
    </a>
    <div style="font-weight: bold" class="dropdown-menu">
        <div class="user-info">
            <img src="user.png" alt="User" class="user-avatar">
            <p class="user-name"><?php echo $firstname . ' ' . $lastname; ?></p>
            <p class="user-email"><?php echo $userEmail; ?></p>
        </div>
        <ul class="menu-list">
            <li><a href="mylearning.php">My learning</a></li>
            <li><a href="cart.php">My cart</a></li>
            <hr>
            <li><a href="notifications.php">Notifications</a></li>
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
    <a href="cart.php" class="cart-link">
        <img src="../dashboard/shopping-cart.png" alt="Shopping Cart" class="user-icon">
    </a>
</div>

        <div class="navbar-item5">
            <a href="notifications.php" class="notification-link">
                <img src="../dashboard/bell1.png" alt="Notifications" class="user-icon">
                <span class="notification-text"></span>
            </a>
        </div>
        
    </nav>
    <div class="navbar-item6">
        <!-- Drift Chat integration icon (optional) -->
        <a href="message_instructor.php"><img src="../dashboard/messenger.png" alt="User" class="user-icon"></a>
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
  
<h1 class="welcome-message">Welcome, student <?php echo htmlspecialchars($studentName); ?>!</h1>

<div class="banner"> 
    <div class="banner">
        <div class="carousel">
        <img src="D11.png" alt="Banner Image 1" class="carousel-image">
        <img src="D22.png" alt="Banner Image 2" class="carousel-image">
        <img src="D33.png" alt="Banner Image 3" class="carousel-image">
        </div>
    </div>
</div>


<div class="aligned-container">
    <section class="learning-section">
        <h3>Let's start learning</h3>
        <div class="learning-card">
            <a href="" target="_blank">
                <p>Web Development</p>
            </a>
        </div>
    </section>

   <section class="carousel-section">
            <h3>What to learn</h3>
            <div class="carousel-container">
                <button class="carousel-btn" onclick="prevSlide()">&#10094;</button>
                <div class="carousel-items">
                    <?php foreach ($coursesCursor as $course): ?>
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
                    <?php endforeach; ?>
                </div>
                <button class="carousel-btn" onclick="nextSlide()">&#10095;</button>
            </div>
        </section>
</div>

<footer class="footer">
    <div class="footer-sections">
        <div class="footer-column">
            <h3>About</h3>
            <ul>
                <li><a href="#">About us</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Contact us</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Investors</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Discovery Code</h3>
            <ul>
                <li><a href="#">Get the app</a></li>
                <li><a href="#">Teach on Code</a></li>
                <li><a href="#">Plans and Pricing</a></li>
                <li><a href="#">Affiliate</a></li>
                <li><a href="#">Help and Support</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Code for Business</h3>
            <ul>
                <li><a href="#">Code Business</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Legal & Accessibility</h3>
            <ul>
                <li><a href="#">Accessibility statement</a></li>
                <li><a href="#">Privacy policy</a></li>
                <li><a href="#">Sitemap</a></li>
                <li><a href="#">Terms</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Teach the world online</p>
        <p>Create an online video course, reach students across the globe, and earn money.</p>
        <p>Top companies choose Code Business to build in-demand career skills.</p>
        <p>&copy; 2024 Code, Inc. <a href="#">Cookie settings</a></p>
        <p><a href="#">🌐 English</a></p>
    </div>
</footer>



<script src="dashboard.js"></script>
<script>
    let currentIndex = 0;
const images = document.querySelectorAll(".carousel-image");
const intervalTime = 3000; 

function showNextImage() {
    
    images[currentIndex].classList.remove("active");
    
    
    currentIndex = (currentIndex + 1) % images.length;
    
   
    images[currentIndex].classList.add("active");
}


images[currentIndex].classList.add("active");
setInterval(showNextImage, intervalTime);



function nextSlide() {
    const carousel = document.querySelector('.carousel-items');
    carousel.scrollBy({ left: 320, behavior: 'smooth' }); 
}

function prevSlide() {
    const carousel = document.querySelector('.carousel-items');
    carousel.scrollBy({ left: -320, behavior: 'smooth' });
}

const userIcon = document.querySelector('.user-icon');
    const dropdown = document.getElementById('dropdown');
    const chatbox = document.getElementById('chatbox');
    const message1 = document.getElementById('message1');
    const chatInput = document.getElementById('chatInput');
    const chatboxBody = document.getElementById('chatboxBody');
    const sendButton = document.getElementById('sendButton');

   
    userIcon.addEventListener('mouseover', () => {
      dropdown.classList.add('active');
    });

   
    userIcon.addEventListener('mouseout', () => {
      dropdown.classList.remove('active');
    });

  
    message1.addEventListener('click', () => {
      chatbox.style.display = 'flex'; 
      dropdown.classList.remove('active'); 
    });

   
    sendButton.addEventListener('click', () => {
      const message = chatInput.value.trim();
      if (message) {
        const newMessage = document.createElement('p');
        newMessage.textContent = `You: ${message}`;
        chatboxBody.appendChild(newMessage);
        chatInput.value = ''; 
        chatboxBody.scrollTop = chatboxBody.scrollHeight; 
      }
    });

   
    document.addEventListener('click', (e) => {
      if (!chatbox.contains(e.target) && !dropdown.contains(e.target) && !userIcon.contains(e.target)) {
        chatbox.style.display = 'none';
      }
    });

    function toggleCartModal() {
    const cartModal = document.getElementById('cart-modal');
    // Toggle visibility of the cart modal
    cartModal.style.display = (cartModal.style.display === "none" || cartModal.style.display === "") ? "block" : "none";
}

document.getElementById('searchInput').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        // Handle the search query (you can add your search functionality here)
        let searchQuery = event.target.value;
        // Redirect to a search results page or filter courses based on the query
        window.location.href = `searchResults.php?query=${encodeURIComponent(searchQuery)}`;
    }
});

document.getElementById('sendButton').addEventListener('click', sendMessage);

</script>
</body>
</html>