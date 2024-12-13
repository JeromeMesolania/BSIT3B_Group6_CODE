<?php
require '../vendor/autoload.php';
session_start();

use Cloudinary\Cloudinary;
use MongoDB\BSON\ObjectId;
use Cloudinary\Uploader;

// Set up Cloudinary instance
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dq2xtz64m',
        'api_key'    => '688496497383124',
        'api_secret' => 'HcEF-omvSqs3nMwRGN7uvpGX0rE'
    ]
]);

// MongoDB connection setup
$mongoClient = new MongoDB\Client("mongodb://localhost:27017");
$database = $mongoClient->selectDatabase('CODE');
$collection = $database->selectCollection('courses');

// Fetch the instructor's name from the session
$instructorName = $_SESSION['username'];

// Process the video upload form submission
if (isset($_POST['upload'])) {
    $video = $_FILES['video'];
    $allowedExtensions = ['mp4', 'avi', 'mov', 'mkv'];
    $fileExtension = pathinfo($video['name'], PATHINFO_EXTENSION);

    // Check if file extension is valid
    if (in_array($fileExtension, $allowedExtensions)) {
        $filePath = $video['tmp_name'];

        try {
            // Upload video to Cloudinary
            $uploadResponse = $cloudinary->uploadApi()->upload($filePath, [
                'resource_type' => 'video'
            ]);

            $cloudinaryVideoUrl = $uploadResponse['secure_url'];

            // Check if courseId is provided and is a valid ObjectId
            if (isset($_POST['courseId']) && !empty($_POST['courseId'])) {
                try {
                    $courseId = new ObjectId($_POST['courseId']);
                } catch (Exception $e) {
                    echo "Invalid course ID format.";
                    exit();
                }

                // Update the course document with the video URL
                $result = $collection->updateOne(
                    ['_id' => $courseId],
                    ['$set' => ['videoUrl' => $cloudinaryVideoUrl]]
                );

                // Check if the update was successful
                if ($result->getModifiedCount() > 0) {
                    echo "Video uploaded successfully! The video is available at: <a href='$cloudinaryVideoUrl'>$cloudinaryVideoUrl</a>";
                } else {
                    echo "Error updating course document.";
                }
            } else {
                echo "Course ID is missing.";
            }
        } catch (Exception $e) {
            echo "Error uploading video: " . $e->getMessage();
        }
    } else {
        echo "Invalid video format. Only MP4, AVI, MOV, and MKV are allowed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="instructor.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Welcome, Instructor <?php echo htmlspecialchars($instructorName); ?>!</h1>
        <p>Here are your options:</p>
        
            <h2>Upload Video for Your Course</h2>
            <form method="POST" enctype="multipart/form-data">
                <label for="video">Choose video to upload:</label>
                <input type="file" name="video" id="video" required>
                <input type="hidden" name="courseId" value="<?php echo htmlspecialchars($_POST['courseId'] ?? ''); ?>">
                <button type="submit" name="upload">Upload Video</button>
            </form>

            <?php if (isset($cloudinaryVideoUrl)): ?>
                <a href="javascript:void(0);" onclick="openVideoModal('<?php echo $cloudinaryVideoUrl; ?>')">Watch Video</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="videoModal" class="modal">
        <div class="modal-content">
            <span id="closeModal" class="close">&times;</span>
            <video id="videoPlayer" controls>
                <source id="videoSource" src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <button id="fullscreenBtn" class="fullscreen-btn">Fullscreen</button>
        </div>
    </div>
    <script src="instructor.js"></script>
</body>
</html>
