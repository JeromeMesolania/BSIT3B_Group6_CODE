<?php
require '../connection/db_connection.php';
require '../mailer/mailer.php';

require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Connect to MongoDB
    $db = connectMongoDB();
    $usersCollection = $db->users;
    $user = $usersCollection->findOne(['email' => $email]);

    if ($user) {
        // Generate a 6-digit verification code
        $resetCode = random_int(100000, 999999);

        // Store the reset code and expiry time in the database
        $usersCollection->updateOne(
            ['email' => $email],
            ['$set' => [
                'reset_code' => $resetCode,
                'reset_code_expiry' => new MongoDB\BSON\UTCDateTime((time() + 600) * 1000) // Valid for 10 minutes
            ]]
        );

        // Send the reset code via email
        if (sendPasswordResetCode($email, $resetCode)) {
            echo "A reset code has been sent to your email.";
        } else {
            echo "Error: Unable to send the reset code.";
        }
    } else {
        echo "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="forgot_password.css">
</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <form method="POST" action="forgot_password.php">
            <input type="email" name="email" placeholder="Enter your email" required><br>
            <button href="reset_password.php" type="submit">Send Reset Code</button>
        </form>
    </div>
</body> 
</html>
