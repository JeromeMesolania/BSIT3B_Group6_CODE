<?php
session_start();
require '../connection/db_connection.php';
require '../vendor/autoload.php'; // Ensure the Cloudinary SDK is included

use Cloudinary\Cloudinary;

// Cloudinary configuration
$cloudinary = new Cloudinary([
    'cloud_name' => 'dq2xtz64m', 
    'api_key' => '688496497383124', 
    'api_secret' => 'HcEF-omvSqs3nMwRGN7uvpGX0rE'
]);

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = connectMongoDB();
$usersCollection = $db->users;
$userId = $_SESSION['user_id'];
$user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $profilePictureUrl = $user['profilePicture'] ?? '';

    // Handle profile picture upload to Cloudinary
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
        // Upload image to Cloudinary
        $uploadedFile = $cloudinary->uploadApi()->upload($_FILES['profilePicture']['tmp_name']);
        
        // Get the URL of the uploaded image
        $profilePictureUrl = $uploadedFile['secure_url'];

        // If upload fails, display an error
        if (!$profilePictureUrl) {
            echo "<p class='error'>Error uploading profile picture!</p>";
        }
    }

    // Handle email change
    $newEmail = $_POST['newEmail'] ?? '';
    if ($newEmail && $newEmail != $user['email']) {
        $usersCollection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($userId)],
            ['$set' => ['email' => $newEmail]]
        );
        echo "<p class='success'>Email updated successfully!</p>";
    }

    // Handle password change
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    if ($currentPassword && $newPassword) {
        if (password_verify($currentPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $usersCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($userId)],
                ['$set' => ['password' => $hashedPassword]]
            );
            echo "<p class='success'>Password updated successfully!</p>";
        } else {
            echo "<p class='error'>Current password is incorrect!</p>";
        }
    }

    // Update other user information
    $updateData = [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'profilePicture' => $profilePictureUrl, // Save the Cloudinary URL
    ];

    $usersCollection->updateOne(['_id' => new MongoDB\BSON\ObjectId($userId)], ['$set' => $updateData]);
    echo "<p class='success'>Profile updated successfully!</p>";
}

// Handle profile picture upload to Cloudinary
if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
    try {
        // Upload image to Cloudinary
        $uploadedFile = $cloudinary->uploadApi()->upload($_FILES['profilePicture']['tmp_name']);
        
        // Get the URL of the uploaded image
        $profilePictureUrl = $uploadedFile['secure_url'];

        // Update the profile picture URL in the database immediately
        $usersCollection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($userId)],
            ['$set' => ['profilePicture' => $profilePictureUrl]]
        );
    } catch (Exception $e) {
        echo "<p class='error'>Error uploading profile picture: " . $e->getMessage() . "</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }
        .container h2 {
            text-align: left;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 700;
        }
        .profile-pic {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
        }
        .profile-pic img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e4e4e4;
        }
        .profile-pic button {
            position: absolute;
            bottom: 0;
            background-color: #00A181;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            font-weight: 700;
            margin-bottom: 5px;
            color: #444;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }
        button[type="submit"] {
            background-color: #00A181;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button[type="submit"]:hover {
            background-color: #151542;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Public Profile</h2>
        <div class="profile-pic">
            <!-- Display either the user's profile picture from Cloudinary or the default one -->
            <img src="<?php echo !empty($user['profilePicture']) ? htmlspecialchars($user['profilePicture']) : '../dashboard/profile-picture.png'; ?>" alt="profile-picture.png">
            <form action="editAccount.php" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="file" name="profilePicture" id="profilePictureInput">
                <button type="submit">Change Picture</button>
            </form>
            <button type="button" onclick="document.getElementById('profilePictureInput').click();">Change Picture</button>
        </div>

        <form action="editAccount.php" method="POST">
            <label for="firstname">First Name</label>
            <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>" required>

            <label for="lastname">Last Name</label>
            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>" required>

            <label for="newEmail">New Email</label>
            <input type="email" id="newEmail" name="newEmail" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">

            <label for="currentPassword">Current Password</label>
            <input type="password" id="currentPassword" name="currentPassword">

            <label for="newPassword">New Password</label>
            <input type="password" id="newPassword" name="newPassword">

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
