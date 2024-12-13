<?php
require '../connection/db_connection.php';
require '../vendor/autoload.php';
session_start();

use Cloudinary\Cloudinary;
use MongoDB\Client as MongoDBClient;

// Ensure the session is set correctly
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../dashboard/error404.html");
    exit();
}

// Initialize Cloudinary
$cloudinary = new Cloudinary([ 
    'cloud' => [
        'cloud_name' => 'dq2xtz64m',
        'api_key'    => '688496497383124',
        'api_secret' => 'HcEF-omvSqs3nMwRGN7uvpGX0rE',
    ],
]);

// Fetch courses
$coursesCollection = $db->courses;
$coursesCursor = $coursesCollection->find(['instructorId' => $_SESSION['user_id']]);
$courses = iterator_to_array($coursesCursor);
$qrCodesCollection = $db->qrCodes;

// Course creation logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = isset($_POST['isFree']) ? 0 : floatval($_POST['price']);
    $isFree = isset($_POST['isFree']);

    $assessments = json_decode($_POST['assessments'], true);
    if (is_array($assessments)) {
        foreach ($assessments as &$assessment) {
            $assessment['_id'] = new MongoDB\BSON\ObjectId();
            $assessment['status'] = 'accepted';
        }
    } else {
        $assessments = [];
    }
    

    $newCourse = [
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'price' => $price,
        'isFree' => $isFree,
        'instructorId' => $_SESSION['user_id'],
        'instructorName' => $_POST['instructorName'] ?? '',
        'videoUrl' => $_POST['videoUrl'] ?? '',
        'assessments' => $assessments,
        'qrCodeUrl' => null,
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
        'updatedAt' => new MongoDB\BSON\UTCDateTime(),
        'status' => 'pending'
    ];

    if (isset($_FILES['qrCode']) && $_FILES['qrCode']['error'] === UPLOAD_ERR_OK) {
        $qrCode = $_FILES['qrCode'];
        $allowedMimeTypes = ['image/jpeg', 'image/png'];
    
        if (in_array($qrCode['type'], $allowedMimeTypes)) {
            // Upload QR code to Cloudinary
            $uploadResponse = $cloudinary->uploadApi()->upload($qrCode['tmp_name'], ['folder' => 'courses/qr_codes']);
            $newCourse['qrCodeUrl'] = $uploadResponse['secure_url'];
    
            // Insert QR code URL into the qrCodes collection
            $qrCodeEntry = [
                'instructorId' => $_SESSION['user_id'],
                'courseTitle' => $title,
                'qrCodeUrl' => $uploadResponse['secure_url'],
                'uploadedAt' => new MongoDB\BSON\UTCDateTime()
            ];
    
            $qrCodesCollection->insertOne($qrCodeEntry); // Save the QR code in qrCodes collection
        } else {
            echo "Invalid QR Code format.";
            exit();
        }
    }
    // Insert course into MongoDB
    $result = $coursesCollection->insertOne($newCourse);
    header("Location: instructordashboard.php?success=1");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Udemy Style Sidebar</title>
    <link rel="stylesheet" href="instructordashboard2.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Sidebar Menu Items with PNG icons -->
        <a href="#" class="sidebar-item">
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

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header section with user info -->
        <!-- Header section with user info -->
<header class="header">
    <div class="notification-container">
        <div class="icon">🔔</div> <!-- Replace with an actual bell icon if desired -->
        <div class="profile-dropdown">
            <div class="profile">J</div>
            <div class="dropdown-menu">
                <a href="../dashboard/logout.php">Logout</a>
            </div>
        </div>
    </div>
</header>


        <!-- "Jump Into Course Creation" section -->
        <section class="course-creation">
            <p>Jump Into Course Creation</p>
            <button class="create-course-btn" id="createCourseBtn">Create Your Course</button>
        </section>

        <?php if (count($courses) > 0): ?>
    <div class="course-container">
        <?php foreach ($courses as $course): ?>
            <div class="course-item">
                <div class="course-video">
                    <video width="320" height="240" controls>
                        <source src="<?php echo htmlspecialchars($course['videoUrl']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="course-details-box">
                <div class="course-info">
    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
    <p><strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructorName']); ?></p>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($course['category']); ?></p>
    <p><strong>Price:</strong> <?php echo $course['isFree'] ? 'Free' : number_format($course['price'], 2) . ' USD'; ?></p>
    <p><strong>Status:</strong> <?php echo ucfirst(isset($course['status']) ? $course['status'] : 'pending'); ?></p>
    <!-- Display QR Code -->
    <?php if (isset($course['qrCodeUrl'])): ?>
        <p><strong>Payment QR Code:</strong> <img src="<?php echo htmlspecialchars($course['qrCodeUrl']); ?>" alt="QR Code" style="width: 150px;"></p>
    <?php endif; ?>
</div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>You have no courses yet. Create one!</p>
<?php endif; ?>





        <!-- Modal for Course Creation -->
        <div id="courseModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeModal">&times;</span>
                <h2>Create Your Course</h2>
                <form id="courseForm" action="uploadCourse.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Course Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="instructorName">Instructor Name</label>
                        <input type="text" id="instructorName" name="instructorName" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="" disabled selected>Select a category</option>
                            <option value="Web Development">Web Development</option>
                            <option value="IT & Software">IT & Software</option>
                            <option value="UI/UX Design">UI/UX Design</option>
                            <option value="CyberSecurity">CyberSecurity</option>
                            <option value="Cloud Computing">Cloud Computing</option>
                            <option value="Internet of Things (IoT)">Internet of Things (IoT)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="videoFile">Upload Video</label>
                        <input type="file" id="videoFile" name="videoFile" accept="video/*" required>
                    </div>
                    <div class="form-group">
                        <label for="thumbnailFile">Upload Thumbnail</label>
                        <input type="file" id="thumbnailFile" name="thumbnailFile" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="isFree">Is Free?</label>
                        <input type="checkbox" id="isFree" name="isFree">
                    </div>

                    <div class="form-group">
    <h3>Course Assessment</h3>
    <div id="assessmentContainer">
        <div class="question-block">
            <label>Question:</label>
            <input type="text" name="questions[]" required>

            <label>Option 1:</label>
            <input type="text" name="options[][0]" required>

            <label>Option 2:</label>
            <input type="text" name="options[][1]" required>

            <label>Option 3:</label>
            <input type="text" name="options[][2]" required>

            <label>Option 4:</label>
            <input type="text" name="options[][3]" required>

            <label>Correct Answer (1-4):</label>
            <input type="number" name="correctAnswers[]" min="1" max="4" required>
        </div>
    </div>
    <button type="button" onclick="addQuestion()">Add Another Question</button>
</div>
<div class="form-group">
    <label for="qrCode">Upload QR Code (JPG/PNG)</label>
    <input type="file" id="qrCode" name="qrCode" accept="image/jpeg, image/png" required>
</div>

<input type="hidden" id="assessments" name="assessments">

    <button type="submit" class="submit-btn">Submit</button>
</form>
</div>
</div>

        <!-- Resource suggestion text -->
        <p class="resource-suggestion">
            Based on your experience, we think these resources will be helpful.
        </p>

        <!-- "Create an Engaging Course" card -->
        <section class="course-card">
            <div class="course-card-content">
                <img src="burat.png" alt="Illustration" class="course-image"> <!-- Replace with actual image if available -->
                <div class="course-text">
                    <h2>Create an Engaging Course</h2>
                    <p>
                        Whether you've been teaching for years or are teaching for the first time, you can make an engaging course. We've compiled resources and best practices to help you get to the next level, no matter where you're starting.
                    </p>
                    <a href="#" class="get-started-link">Get Started</a>
                </div>
            </div>
        </section>
    </div>

    <script src="https://upload-widget.cloudinary.com/global/all.js"></script>
<script>
  // Show the modal when 'Create Your Course' button is clicked
  document.getElementById('createCourseBtn').onclick = function() {
      document.getElementById('courseModal').style.display = 'block';
  };

  // Close the modal when 'x' is clicked
  document.getElementById('closeModal').onclick = function() {
      document.getElementById('courseModal').style.display = 'none';
  };

  // Close the modal when clicking outside the modal content
  window.onclick = function(event) {
      if (event.target == document.getElementById('courseModal')) {
          document.getElementById('courseModal').style.display = 'none';
      }
  };

  const priceInput = document.getElementById('price');
  const isFreeCheckbox = document.getElementById('isFree');

  // Function to update the checkbox state based on price input
  function toggleFreeCheckbox() {
      const price = parseFloat(priceInput.value); // Convert input to a number

      if (!price || price <= 0) { // If price is zero or empty
          isFreeCheckbox.disabled = false;
          isFreeCheckbox.checked = true; // Automatically check it when free
      } else {
          isFreeCheckbox.disabled = true;
          isFreeCheckbox.checked = false;
      }
  }

  // Add event listener to monitor changes in the price input
  priceInput.addEventListener('input', toggleFreeCheckbox);

  // Initial call to set the correct state on page load
  toggleFreeCheckbox();

 // Fix dynamic question block addition
let questionCount = 1;

function addQuestion() {
    const container = document.getElementById('assessmentContainer');
    const newQuestion = document.querySelector('.question-block').cloneNode(true);
    container.appendChild(newQuestion);
}


document.getElementById('courseForm').onsubmit = function(e) {
    const assessments = [];
    document.querySelectorAll('.question-block').forEach(block => {
        const question = block.querySelector('.question').value;
        const options = [
            block.querySelector('.option1').value,
            block.querySelector('.option2').value,
            block.querySelector('.option3').value,
            block.querySelector('.option4').value
        ];
        const correctAnswer = block.querySelector('.correctAnswer').value;

        assessments.push({ question, options, correctAnswer });
    });
    document.getElementById('assessments').value = JSON.stringify(assessments);
};

function gatherAssessments() {
    const questions = document.querySelectorAll('input[name="questions[]"]');
    const options = document.querySelectorAll('input[name="options[][]"]');
    const correctAnswers = document.querySelectorAll('input[name="correctAnswers[]"]');

    let assessments = [];

    for (let i = 0; i < questions.length; i++) {
        let assessment = {
            question: questions[i].value,
            options: [
                options[i * 4].value,
                options[i * 4 + 1].value,
                options[i * 4 + 2].value,
                options[i * 4 + 3].value,
            ],
            correctAnswer: correctAnswers[i].value
        };
        assessments.push(assessment);
    }

    // Update the hidden input with the assessments JSON
    document.getElementById('assessments').value = JSON.stringify(assessments);
}

// Attach the gather function to the form submit
document.getElementById('courseForm').addEventListener('submit', gatherAssessments);

const assessmentsData = [
    {
      question: "test sa assessment12",
      options: [
        "test sa assessment12",
        "test sa assessment12",
        "test sa assessment12",
        "test sa assessment12"
      ],
      correctAnswer: "test sa assessment12",
      _id: "674e0d1d79a15b5e140fa2c7"
    }
  ];

  // Set the hidden input's value to a JSON string
  document.getElementById('assessments').value = JSON.stringify(assessmentsData);
</script>

</body>
</html>