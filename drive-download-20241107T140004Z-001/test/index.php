<?php
require '../connection/db_connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = connectMongoDB();

    // Check Admin Credentials
    $adminCollection = $db->admin;
    $admin = $adminCollection->findOne(['username' => $username]);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = (string)$admin['_id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['role'] = 'admin';  // Explicitly set role as admin

        // Redirect to admin dashboard
        header("Location: ../admin/admin.php");
        exit();
    }

    // Check Regular User Credentials
    $usersCollection = $db->users;
    $user = $usersCollection->findOne(['username' => $username]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = (string)$user['_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on user role
        if ($user['role'] === 'instructor') {
            header("Location: ../instructor_dashboard/instructordashboard.php");
        } else {
            header("Location: ../dashboard/dashboard26.php");
        }
        exit();
    } else {
        echo "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login For HTML CSS</title>
    <link rel="stylesheet" href="test1.css">
</head>
<body>
    <!-- Login Form Section -->
    <div class="login-container">
        <div class="login-form">
        <h1>Welcome to <span class="code-letter c">C</span><span class="code-letter o">O</span><span class="code-letter d">D</span><span class="code-letter e">E</span></h1>
            <div class="container">
                <div class="main">
                    <div class="content">
                        <h2>Log In</h2>
                        <form method="post">
    <input type="text" name="username" placeholder="Username" required autofocus="">
    
    <!-- Password container with eye icon -->
    <div class="password-container">
        <input type="password" id="password" name="password" placeholder="Password" required>
        <img src="eye.png" alt="Show" class="eye-icon" onclick="togglePassword('password', this)">
    </div>

    <button class="btn" type="submit">Login</button>

    <!-- Forgot Password button -->
    <div class="forgot-password">
        <a href="../forgotpassword/forgot_password.php" class="forgot-password-link">Forgot Password?</a>
    </div>
</form>

                    </div>
                    <div class="context-box">
                        <h3>New Here?</h3>
                        <p>Register and discover a great amount of new opportunities</p>
                        <a href="../registration/index.php" class="register-btn">Register</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassword(fieldId, iconElement) {
    const passwordField = document.getElementById(fieldId);
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        iconElement.src = "../registration/eye-off.png";  // Toggle to 'hide' icon
    } else {
        passwordField.type = "password";
        iconElement.src = "../registration/eye.png";  // Toggle to 'show' icon
    }
}

</script>


</body>
</html>
