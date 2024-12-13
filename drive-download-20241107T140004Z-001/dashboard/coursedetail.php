<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['username'] ?? 'User';

// Validate and retrieve the course ID from the query parameters
$courseId = $_GET['id'] ?? null;

try {
    $objectId = new MongoDB\BSON\ObjectId($courseId);
} catch (Exception $e) {
    error_log("Invalid Course ID: " . $e->getMessage());
    die("Error: Invalid Course ID format");
}

// Initialize MongoDB client and collections
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$coursesCollection = $database->selectCollection('courses');
$usersCollection = $database->selectCollection('users');

// Retrieve user details
$user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);

if ($user) {
    $firstname = $user['firstname'] ?? 'Unknown';
    $lastname = $user['lastname'] ?? 'User';
    $userEmail = $user['email'] ?? 'No email';
} else {
    $firstname = 'Unknown';
    $lastname = 'User';
    $userEmail = 'No email';
}

// Retrieve course details
try {
    $course = $coursesCollection->findOne(['_id' => $objectId]);

    if (!$course) {
        error_log("Course not found with ID: " . $courseId);
        header("Location: error404.html");
        exit();
    }
} catch (Exception $e) {
    error_log("MongoDB Query Error: " . $e->getMessage());
    header("Location: error404.html");
    exit();
}

// Prepare course details
$qrCodeUrl = isset($course['qrCodeUrl']) ? htmlspecialchars($course['qrCodeUrl']) : 'default-qr-code.png';
$instructorName = $course['instructorName'] ?? 'Unknown Instructor';
$coursePrice = $course['price'] ?? 0;
$thumbnailUrl = isset($course['thumbnail']) ? htmlspecialchars($course['thumbnail']) : 'default-thumbnail.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="stylesheet" href="coursedetail1.css">
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
            <input type="text" placeholder="Search for anything">
        </div>
        <div style="font-weight: bold" class="navbar-item2">
            <a href="mylearning.php">My Learning</a>
        </div>
        <div class="navbar-item3">
    <a href="#" class="user-link">
        <img style="width: 40px; height: 40px;" src="user.png" alt="User" class="user-icon">
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
            <li><a href="#">Notifications</a></li>
            <li><a href="messages.php">Messages</a></li>
            <hr>
            <li><a href="#">Account settings</a></li>
            <li><a href="#">Subscriptions</a></li>
            <li><a href="#">Purchase history</a></li>
            <li class="logout"><a href="../logout/logout.php">Logout</a></li>
        </ul>
    </div>
</div>
        </div>
        <div class="navbar-item4">
            <a href="cart.php" class="cart-link">
                <img style="width: 40px; height: 40px;" src="shopping-cart.png" alt="Shopping Cart" class="user-icon">
            </a>
        </div>
        <div class="navbar-item5">
            <a href="notifications.php" class="notification-link">
                <img style="width: 40px; height: 40px;" src="bell1.png" alt="Notifications" class="user-icon">
            </a>
        </div>
    </nav>
    <div class="navbar-item6">
        <a style="width: 40px; height: 40px;" href="message_instructor.php"><img src="messenger.png" alt="User" class="user-icon"></a>
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
                <a href="category.html?category=Mobile%20App%20Development">Mobile App Development</a>
            </div>
        </div>

        <!-- Pricing and Preview Section -->
        <div class="course-preview">
            <?php
            // Fetch the thumbnail URL from the database, defaulting to 'default-thumbnail.png' if not available
            $thumbnailUrl = isset($course['thumbnail']) ? htmlspecialchars($course['thumbnail']) : 'default-thumbnail.png'; 
            ?>
            <div class="video-thumbnail" style="background-image: url('<?php echo $thumbnailUrl; ?>');">
                <span class="play-icon">&#9658; Preview this course</span>
            </div>
            <div class="pricing">
                <p class="price">₱<?php echo htmlspecialchars($course['price']); ?></p>
                <button class="add-to-cart" onclick="addToCart('<?php echo $course['_id']; ?>')">Add to cart</button>
                <div id="paypal-button-container"></div>
                <div id="paymentModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn" onclick="closeModal()">&times;</span>
                        <h2>Complete Your Payment</h2>
                        <p>Scan the QR code below to pay via GCash:</p>
                        <img src="<?php echo $qrCodeUrl; ?>" alt="Instructor's QR Code" class="course-qr-code">
                        <p class="instructions">Open GCash and scan the QR code to complete your payment.</p>
                        <p class="instructions">Upload your payment proof after scanning the QR code.</p>
        
        <!-- Form for uploading proof -->
        <form id="paymentProofForm" action="upload_payment_proof.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="courseId" value="<?php echo $courseId; ?>">
    <input type="file" name="paymentProof" accept="image/*" required>
    <button type="submit" style="background-color: green; color: white; border: none; padding: 10px 15px; cursor: pointer;">
        Submit Proof
    </button>
</form>
            </div>
        </div>

<!-- Add Buy Now button and script -->
<button class="buy-now" onclick="handleBuyNowClick('<?php echo htmlspecialchars($course['_id']); ?>', <?php echo ($course['price'] == 0) ? 'true' : 'false'; ?>)">
    <?php echo ($course['price'] == 0) ? 'Enroll for Free' : 'Buy Now'; ?>
</button>

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
            <li>Switch careers and get a job as an Android Developer</li>
            <li>You will be able to develop modern Android apps</li>
            <li>Go from a complete beginner to a real Android App Developer</li>
            <li>Make beautiful, professional Android apps</li>
        </ul>
    </section>
    
    
    <script src="coursedetail.js"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=ARReS41A7ir_B9tKmYjeySe25o2OSCrIuiqRs_BeFJazaAMaGSGKnPrPehW5hUBDOGXLgRgyRgS2zmBT&currency=PHP"></script>
    <script>
        
function openModal() {
    document.getElementById("paymentModal").style.display = "block";
}

// Close the modal
function closeModal() {
    document.getElementById("paymentModal").style.display = "none";
}

// Close the modal if the user clicks outside of it
window.onclick = function(event) {
    if (event.target === document.getElementById("paymentModal")) {
        closeModal();
    }
};

  // Function to handle the Buy Now click
  function handleBuyNowClick(courseId) {
    console.log("Course ID:", courseId);  // Check this in the browser console
    const coursePrice = <?php echo $coursePrice; ?>;
    if (coursePrice == 0) {
        addCourseToLearning(courseId);
    } else {
        openModal();
    }
}

function handleBuyNowClick(courseId, isFree) {
    if (isFree) {
        enrollForFree(courseId);  // Separate function for free enrollment
    } else {
        openModal();  // Existing modal for payment
    }
}


function enrollForFree(courseId) {
    fetch('enroll_free_course.php', {  // Use the new backend script
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ courseId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('You are now enrolled in this course!');
            window.location.href = 'mylearning.php';  // Redirect to My Learning page
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Enrollment error:', error);
        alert('An error occurred while enrolling.');
    });
}

    function addCourseToLearning(courseId) {
        fetch('add_to_learning.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ courseId: courseId })
})
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok');  // Check if the response has an HTTP error
    }
    return response.json();  // Convert response to JSON
})
.then(data => {
    if (data.success) {
        alert('Course added to your learning!');
        window.location.href = 'mylearning.php';
    } else {
        alert('Error: ' + data.message);  // Log backend message
    }
})
.catch(error => {
    console.error('Fetch error:', error);  // Log network or parsing errors
    alert('An error occurred.');  // Display user-friendly error
});
    }

    document.getElementById("paymentProofForm").onsubmit = async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);

    try {
        const response = await fetch('upload_payment_proof.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alert('Payment proof submitted successfully. Awaiting verification.');
            window.location.href = 'mylearning.php'; // Redirect to learning page
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    }
};

paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '<?php echo htmlspecialchars($coursePrice); ?>'
                    },
                    description: '<?php echo htmlspecialchars($course["title"]); ?>'
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                alert('Transaction completed by ' + details.payer.name.given_name);
                
                // Send transaction details to the backend for verification and enrollment
                fetch('process_paypal_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: data.orderID,
                        courseId: '<?php echo $courseId; ?>'
                    })
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          alert('Payment successful! You are now enrolled in the course.');
                          window.location.href = 'mylearning.php';
                      } else {
                          alert('Error: ' + data.message);
                      }
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      alert('An error occurred while processing the payment.');
                  });
            });
        },
        onError: function(err) {
            console.error(err);
            alert('An error occurred during the payment process.');
        }
    }).render('#paypal-button-container');

    </script>
</body>
</html>
