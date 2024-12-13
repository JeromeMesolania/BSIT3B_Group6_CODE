<?php
session_start();
require '../connection/db_connection.php';
require '../vendor/autoload.php';

use Cloudinary\Cloudinary;
use MongoDB\Client as MongoDBClient;

// Check if the user is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    error_log("User is not an instructor. Role: " . $_SESSION['role']);
    header("Location: ../dashboard/error404.html");
    exit();
}

// Log session data for debugging
error_log("Session Data: " . print_r($_SESSION, true));

// Cloudinary configuration
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dq2xtz64m',
        'api_key'    => '688496497383124',
        'api_secret' => 'HcEF-omvSqs3nMwRGN7uvpGX0rE',
    ],
]);

// MongoDB configuration
$mongoClient = new MongoDBClient("mongodb://localhost:27017");
$db = $mongoClient->CODE;
$coursesCollection = $db->courses;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and validate form inputs
    $title = $_POST['title'];
    $instructorName = $_POST['instructorName'];
    $description = $_POST['description'];
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $isFree = isset($_POST['isFree']);
    $instructorId = $_SESSION['user_id'];
    $category = $_POST['category'];

    // Verify instructor exists in the database
    $instructor = $db->users->findOne(['_id' => new MongoDB\BSON\ObjectId($instructorId)]);
    if (!$instructor) {
        error_log("Instructor not found in the database: " . $instructorId);
        echo "Instructor information is invalid.";
        exit();
    }

    // Handle assessments
    $assessments = json_decode($_POST['assessments'], true) ?? [];
    $assessments = array_map(function($assessment) {
        $assessment['_id'] = new MongoDB\BSON\ObjectId();
        return $assessment;
    }, $assessments);

    // Handle video upload
    if (isset($_FILES['videoFile']) && $_FILES['videoFile']['error'] == 0) {
        $videoUploadResult = $cloudinary->uploadApi()->upload($_FILES['videoFile']['tmp_name'], [
            'resource_type' => 'video',
            'folder' => 'courses'
        ]);
        $videoUrl = $videoUploadResult['secure_url'];
    } else {
        echo "Video file upload failed.";
        exit();
    }

    // Handle thumbnail upload
    if (isset($_FILES['thumbnailFile']) && $_FILES['thumbnailFile']['error'] == 0) {
        $thumbnailUploadResult = $cloudinary->uploadApi()->upload($_FILES['thumbnailFile']['tmp_name'], [
            'folder' => 'course_thumbnails'
        ]);
        $thumbnailUrl = $thumbnailUploadResult['secure_url'];
    } else {
        echo "Thumbnail file upload failed.";
        exit();
    }

    // Handle QR code upload (optimized to avoid redundancy)
    if (isset($_FILES['qrCode']) && $_FILES['qrCode']['error'] === UPLOAD_ERR_OK) {
        $allowedMimeTypes = ['image/jpeg', 'image/png'];
        if (in_array($_FILES['qrCode']['type'], $allowedMimeTypes)) {
            $qrUploadResult = $cloudinary->uploadApi()->upload($_FILES['qrCode']['tmp_name'], [
                'folder' => 'courses/qr_codes',
            ]);
            $qrCodeUrl = $qrUploadResult['secure_url'];
        } else {
            echo "Invalid QR code file type. Please upload a JPG or PNG image.";
            exit();
        }
    } else {
        $qrCodeUrl = null;  // Default if no QR code uploaded
    }

    // Create course data
    $newCourse = [
        'title'        => $title,
        'description'  => $description,
        'price'        => $price,
        'isFree'       => $isFree,
        'instructorId' => $instructorId,
        'instructorName' => $instructorName,
        'category'     => $category,
        'videoUrl'     => $videoUrl,
        'thumbnailUrl' => $thumbnailUrl,
        'assessments'  => $assessments,
        'qrCodeUrl'    => $qrCodeUrl,
        'createdAt'    => new MongoDB\BSON\UTCDateTime(),
        'updatedAt'    => new MongoDB\BSON\UTCDateTime(),
        'status'       => 'pending'
    ];

    // Insert into MongoDB
    $result = $coursesCollection->insertOne($newCourse);
    if ($result->getInsertedCount() == 1) {
        header("Location: instructordashboard.php");
        exit();
    } else {
        echo "Failed to create course.";
    }
}
?>
