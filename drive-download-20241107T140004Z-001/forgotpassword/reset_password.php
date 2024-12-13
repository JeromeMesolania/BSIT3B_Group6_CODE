<?php
require '../connection/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['code'], $_POST['password'])) {
    $email = $_POST['email'];
    $code = $_POST['code'];
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Connect to MongoDB
    $db = connectMongoDB();
    $usersCollection = $db->users;
    $user = $usersCollection->findOne(['email' => $email]);

    if ($user && $user['reset_code'] == $code) {
        // Update the password
        $usersCollection->updateOne(
            ['email' => $email],
            ['$set' => ['password' => $newPassword], '$unset' => ['reset_code' => "", 'reset_code_expiry' => ""]]
        );
        echo "Password successfully reset! You can now <a href='../test/index.php'>login</a>.";
    } else {
        echo "Invalid or expired code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="reset_password.css">
</head>
<body>
    <div class="reset-password-container">
        <h2>Reset Password</h2>
        <form method="POST" action="reset_password.php">
            <input type="email" name="email" placeholder="Enter your email" required><br>
            <input type="text" name="code" placeholder="Enter the reset code" required><br>
            <input type="password" name="password" placeholder="New password" required><br>
            <input type="password" name="password" placeholder="Confirm new password" required><br>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
