<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';  

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "You need to log in first.";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];
    $comment = $_POST['comment'];

    // Insert comment into the comments collection
    $comment_data = [
        'post_id' => new MongoDB\BSON\ObjectID($post_id),
        'user_id' => new MongoDB\BSON\ObjectID($user_id),
        'comment' => $comment,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];

    $result = $commentsCollection->insertOne($comment_data);

    // Update the comment count in the post
    $postsCollection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectID($post_id)],
        ['$inc' => ['comments_count' => 1]]
    );

    if ($result->getInsertedCount() == 1) {
        echo "Comment added successfully.";
        header('Location: index.php'); // Redirect back to the posts page
    } else {
        echo "Failed to add comment.";
    }
}
?>
