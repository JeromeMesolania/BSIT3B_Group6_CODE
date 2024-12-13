<?php
session_start();
require '../connection/db_connection.php';

// Retrieve the email from the session
$email = $_SESSION['pending_user']['email'] ?? null;

// If no email is found in the session, redirect or show an error
if (!$email) {
    die("No email found. Please complete the registration process.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $_POST['code'];

    // Cast the confirmation code to an integer to ensure the type matches
    $code = (int)$code;

    $db = connectMongoDB();
    $usersCollection = $db->users;

    // Find the user by email and confirmation code
    $user = $usersCollection->findOne([
        'email' => $email,
        'confirmation_code' => $code // Ensure the code is an integer in the query
    ]);

    if ($user) {
        // If the code matches, update user status to active and remove the confirmation code
        $usersCollection->updateOne(
            ['email' => $email],
            ['$set' => ['status' => 'active'], '$unset' => ['confirmation_code' => '']]
        );

        echo "Registration successful! You can now log in.";
        header("Location: ../test/index.php");
        exit();
    } else {
        echo "<p class='error'>Invalid confirmation code. Please check the code and try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Confirmation</title>
    <style>
        /* General body styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Container styling */
        .container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        /* Form element styling */
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        label {
            font-weight: bold;
            font-size: 1rem;
        }

        input[type="text"] {
            padding: 0.7rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #007BFF;
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 0.7rem;
            font-size: 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        h2 {
            margin-bottom: 1rem;
            color: #444;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Confirmation</h2>
        <form action="confirmation.php" method="post">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <label for="code">Enter Confirmation Code:</label>
            <input type="text" id="code" name="code" required>
            <button type="submit">Confirm</button>
        </form>
    </div>
</body>
</html>
