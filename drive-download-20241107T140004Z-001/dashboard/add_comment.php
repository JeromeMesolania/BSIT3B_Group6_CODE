<?php
require '../vendor/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$postsCollection = $client->CODE->posts;
$usersCollection = $client->CODE->users;  // Assuming 'users' collection stores user data

$postId = new MongoDB\BSON\ObjectId($_POST['post_id']);
$commentText = $_POST['comment'];

$userId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

// Fetch the user's details (firstname, lastname, username) from the users collection
$user = $usersCollection->findOne(['user_id' => new MongoDB\BSON\ObjectId($userId)]);
$commentUser = $user ? $user['firstname'] . ' ' . $user['lastname'] : 'Anonymous';  // Combine firstname and lastname

// Create the new comment with the user's name
$newComment = [
    'comment_id' => new MongoDB\BSON\ObjectId(),
    'user' => $commentUser,
    'content' => $commentText,
    'replies' => []
];

$postsCollection->updateOne(
    ['_id' => $postId],
    ['$push' => ['comments' => $newComment]]
);

header('Location: community.php');
exit();
?>
