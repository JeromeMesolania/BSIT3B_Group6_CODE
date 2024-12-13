<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['comment_id'], $data['post_id'], $data['content'])) {
    $commentId = new MongoDB\BSON\ObjectID($data['comment_id']);
    $postId = new MongoDB\BSON\ObjectID($data['post_id']);
    $content = $data['content'];
    $user_id = $_SESSION['user_id'];

    $commentsCollection = $database->selectCollection('comments');

    // Add the reply to the comment
    $commentsCollection->updateOne(
        ['_id' => $commentId],
        ['$push' => ['replies' => [
            'user_id' => new MongoDB\BSON\ObjectID($user_id),
            'content' => $content,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]]]
    );

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}
?>
