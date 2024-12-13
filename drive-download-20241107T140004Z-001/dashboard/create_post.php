<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

    use MongoDB\BSON\ObjectID;
    use MongoDB\BSON\UTCDateTime;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "You need to log in first.";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $content = $_POST['content'];
    $media = isset($_FILES['media']) ? $_FILES['media']['name'] : []; 

    $post = [
        'user_id' => new MongoDB\BSON\ObjectID($user_id),
        'content' => $content,
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'media' => $media,  // Store the media files URL
        'comments_count' => 0
    ];

    $result = $postsCollection->insertOne($post);
    if ($result->getInsertedCount() == 1) {
        echo "Post created successfully.";
    } else {
        echo "Failed to create post.";
    }
}
?>
