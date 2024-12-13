<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

require '../vendor/autoload.php';
include '../navbar/navbar.php';

use MongoDB\BSON\ObjectId;

// Get user_id from session
$userId = $_SESSION['user_id'];
$usersCollection = $database->selectCollection('users');
$messagesCollection = $database->selectCollection('messages');

// Retrieve the student's document
$user = $usersCollection->findOne(['_id' => new ObjectId($userId)]);

if (!$user) {
    echo "User not found.";
    exit();
}

// Handle instructor search
$searchResults = [];
if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    if (!empty($searchQuery)) {
        $searchRegex = new MongoDB\BSON\Regex($searchQuery, 'i'); // Case-insensitive search
        $searchResults = $usersCollection->find([
            'role' => 'instructor',
            '$or' => [
                ['firstname' => $searchRegex],
                ['lastname' => $searchRegex],
            ]
        ])->toArray();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $instructorId = isset($_POST['instructorId']) ? $_POST['instructorId'] : null;

    if (!empty($message) && !empty($instructorId)) {
        $messagesCollection->insertOne([
            'senderId' => new ObjectId($userId), // Ensure this is the student's ID
            'receiverId' => new ObjectId($instructorId),
            'message' => $message,
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'read' => false
        ]);
        echo "Message sent successfully!";
    } else {
        echo "Message or Instructor ID is missing.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Instructor</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="message_instructor.css">
</head>
<body>

<h1>Message Instructor</h1>

<!-- Search Form -->
<form method="GET" action="message_instructor.php">
    <input type="text" name="search" placeholder="Search for instructors by name" required>
    <button type="submit">Search</button>
</form>

<?php if (!empty($searchResults)): ?>
    <h2>Search Results</h2>
    <ul>
        <?php foreach ($searchResults as $instructor): ?>
            <li>
                <?php echo htmlspecialchars($instructor['firstname'] . ' ' . $instructor['lastname']); ?>
                <form method="POST" action="message_instructor.php">
    <input type="hidden" name="instructorId" value="<?php echo htmlspecialchars($instructor['_id']); ?>">
    <textarea name="message" placeholder="Write your message here" required></textarea>
    <div class="button-group">
        <button type="submit">Send Message</button>
        <button type="button" class="reply-button" onclick="window.location.href='messages.php'">Reply</button>
    </div>
</form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php elseif (isset($_GET['search'])): ?>
    <p>No instructors found matching "<?php echo htmlspecialchars($searchQuery); ?>".</p>
<?php endif; ?>

</body>
</html>
