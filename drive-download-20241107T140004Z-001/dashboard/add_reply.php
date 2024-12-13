<?php
require '../vendor/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$postsCollection = $client->CODE->posts;
$usersCollection = $client->CODE->users; 

$postId = new MongoDB\BSON\ObjectId($_POST['post_id']);
$commentId = new MongoDB\BSON\ObjectId($_POST['comment_id']);
$replyText = $_POST['reply'];

$userId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

// Fetch the user's details (firstname, lastname, username) from the users collection
$user = $usersCollection->findOne(['user_id_id' => new MongoDB\BSON\ObjectId($userId)]);
$replyUser = $user ? $user['firstname'] . ' ' . $user['lastname'] : 'Anonymous';  // Combine firstname and lastname

// Create the new reply with the user's name
$newReply = [
    'user' => $replyUser,
    'content' => $replyText
];

$postsCollection->updateOne(
    ['_id' => $postId, 'comments.comment_id' => $commentId],
    ['$push' => ['comments.$.replies' => $newReply]]
);

header('Location: community.php');
exit();
?>
