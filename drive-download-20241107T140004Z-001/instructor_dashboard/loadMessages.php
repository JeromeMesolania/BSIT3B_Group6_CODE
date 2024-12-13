<?php
session_start();
require '../connection/db_connection.php'; 
require '../vendor/autoload.php'; 

$userId = $_SESSION['user_id'];
$instructorId = "instructor_id";

$messages = $collection->find([
    '$or' => [
        ['senderId' => $userId, 'receiverId' => $instructorId],
        ['senderId' => $instructorId, 'receiverId' => $userId]
    ]
])->toArray();

echo json_encode($messages);
