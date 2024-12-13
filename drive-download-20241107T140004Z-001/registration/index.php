<?php
// Include your mailer and database connection
require '../connection/db_connection.php';
require '../mailer/mailer.php';

require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    $db = connectMongoDB();
    $usersCollection = $db->users;

    // Check if user already exists by username or email
    $existingUser = $usersCollection->findOne(['$or' => [
        ['username' => $username],
        ['email' => $email]
    ]]);

    if ($existingUser) {
        die("Username or email already exists!");
    }

    // Generate a random 6-digit confirmation code
    $confirmationCode = rand(100000, 999999);

    // Send confirmation code to user's email
    if (!sendConfirmationCode($email, $confirmationCode)) {
        die("Failed to send confirmation code. Please try again.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Generate a MongoDB ObjectId
    $userId = new MongoDB\BSON\ObjectId(); // Unique user identifier (ObjectId)

    // Prepare user data
    $userData = [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role,
        'confirmation_code' => $confirmationCode,
        'status' => 'pending',
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
        '_id' => $userId // Save the ObjectId as the main identifier
    ];

    if ($role == 'student') {
        // Custom studentId format (optional)
        $userData['studentId'] = 'student_' . $userId; // Create a custom studentId with prefix
    } elseif ($role == 'instructor') {
        // Custom instructorId format (optional)
        $userData['instructorId'] = 'instructor_' . $userId; // Create a custom instructorId with prefix
    }

    // Save user details in the database
    $usersCollection->insertOne($userData);

    // Store the user's email temporarily in the session for the confirmation page
    session_start();
    $_SESSION['pending_user'] = ['email' => $email];

    // Redirect to confirmation page
    header("Location: confirmation.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Registration Container -->
    <div class="registration-container">
        <!-- Logo Container -->
        <div class="logo-container">
            <img src="haha.png" alt="Logo">
            <img src="code logo.png" alt="Logo">
        </div>

        <div class="registration-form">
            <h2>Create an Account</h2>
            <form action="#" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">Firstname</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Lastname</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" required>
                            <span class="eye-icon" onclick="togglePassword('password', 'eye-icon')">
                                <img src="eye.png" alt="Show" id="eye-icon">
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <div class="password-container">
                            <input type="password" id="confirm-password" name="confirm-password" required>
                            <span class="eye-icon" onclick="togglePassword('confirm-password', 'confirm-eye-icon')">
                                <img src="eye.png" alt="Show" id="confirm-eye-icon">
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Select Role</label>
                    <select id="role" name="role" required>
                        <option value="student">Student</option>
                        <option value="instructor">Instructor</option>
                    </select>
                </div>
                <button href="confirmation.php" type="submit" class="btn">Sign Up</button>
                <p class="account">Already have an account? <a href="../test/index.php">Log In</a></p>
            </form>
        </div>
    </div>

    <script>
       function togglePassword(fieldId, iconId) {
    const passwordField = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(iconId);
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.src = "eye-off.png"; // Change to 'Hide' icon
    } else {
        passwordField.type = "password";
        eyeIcon.src = "eye.png"; // Change to 'Show' icon
    }
}
    </script>

</body>
</html>
