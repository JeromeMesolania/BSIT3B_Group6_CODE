<?php
require '../vendor/autoload.php';
require '../connection/db_connection.php';

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You need to log in first.";
    exit;
}

$user_id = $_SESSION['user_id'];
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$postsCollection = $database->selectCollection('posts');
$usersCollection = $database->selectCollection('users');
$likesCollection = $database->selectCollection('likes');
$dislikesCollection = $database->selectCollection('dislikes');
$user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectID($user_id)]);

if ($user) {
    $firstname = $user['firstname'] ?? 'Unknown';
    $lastname = $user['lastname'] ?? 'User';
    $userEmail = $user['email'] ?? 'No email';
} else {
    $firstname = 'Unknown';
    $lastname = 'User';
    $userEmail = 'No email';
}


// Handle creating a new post
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
    $content = $_POST['content'];
    $media = isset($_FILES['media']) ? $_FILES['media'] : null;
    $photo = isset($_FILES['photo']) ? $_FILES['photo'] : null;
    
    // Handle file upload
    $mediaName = '';
    $photoName = '';

    $uploadDir = __DIR__ . '/uploads/'; // Use absolute path to ensure correctness

    // Create the uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory with write permissions
    }

    if ($media && $media['error'] === UPLOAD_ERR_OK) {
        $mediaName = $uploadDir . basename($media['name']);
        if (!move_uploaded_file($media['tmp_name'], $mediaName)) {
            echo "Error: Failed to move uploaded file for media.";
        }
    }

    if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
        $photoName = $uploadDir . basename($photo['name']);
        if (!move_uploaded_file($photo['tmp_name'], $photoName)) {
            echo "Error: Failed to move uploaded file for photo.";
        }
    }

    // Create post
    $post = [
        'user_id' => new MongoDB\BSON\ObjectID($user_id),
        'content' => $content,
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'media' => $mediaName,  // Store the media file URL
        'photo' => $photoName,  // Store the photo URL
        'comments_count' => 0
    ];

    $result = $postsCollection->insertOne($post);
    if ($result->getInsertedCount() == 1) {
        echo "Post created successfully.";
    } else {
        echo "Failed to create post.";
    }
}

// Handle like and dislike actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['post_id'])) {
    $postId = new MongoDB\BSON\ObjectID($_POST['post_id']);
    $action = $_POST['action'];

    if ($action == 'like') {
        $likesCollection->insertOne([
            'user_id' => new MongoDB\BSON\ObjectID($user_id),
            'post_id' => $postId,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
        ]);
    } elseif ($action == 'dislike') {
        $dislikesCollection->insertOne([
            'user_id' => new MongoDB\BSON\ObjectID($user_id),
            'post_id' => $postId,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
        ]);
    }
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment']) && isset($_POST['post_id'])) {
    $postId = new ObjectID($_POST['post_id']);
    $commentContent = $_POST['comment'];

    $comment = [
        'comment_id' => new ObjectID(),
        'user_id' => new ObjectID($user_id),
        'content' => $commentContent,
        'created_at' => new UTCDateTime(),
        'replies' => []
    ];

    $postsCollection->updateOne(
        ['_id' => $postId],
        ['$push' => ['comments' => $comment]]
    );
}

// Handle new reply submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply']) && isset($_POST['post_id']) && isset($_POST['comment_id'])) {
    $postId = new ObjectID($_POST['post_id']);
    $commentId = new ObjectID($_POST['comment_id']);
    $replyContent = $_POST['reply'];

    $reply = [
        'reply_id' => new ObjectID(),
        'user_id' => new ObjectID($user_id),
        'content' => $replyContent,
        'created_at' => new UTCDateTime()
    ];

    $postsCollection->updateOne(
        ['_id' => $postId, 'comments.comment_id' => $commentId],
        ['$push' => ['comments.$.replies' => $reply]]
    );
}



$maxFileSize = 5 * 1024 * 1024; // 5 MB limit
$maxWidth = 800; // Max width in pixels
$maxHeight = 800; // Max height in pixels

function resizeImage($filePath, $targetWidth, $targetHeight) {
    list($originalWidth, $originalHeight) = getimagesize($filePath);
    $imageType = exif_imagetype($filePath);
    
    // Create image resource
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($filePath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($filePath);
            break;
        default:
            return false; // Unsupported format
    }

    // Calculate new dimensions
    $ratio = min($targetWidth / $originalWidth, $targetHeight / $originalHeight);
    $newWidth = $originalWidth * $ratio;
    $newHeight = $originalHeight * $ratio;

    // Create a new blank image
    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

    // Save resized image
    if ($imageType == IMAGETYPE_JPEG) {
        imagejpeg($resizedImage, $filePath, 85); // 85% quality for JPEG
    } elseif ($imageType == IMAGETYPE_PNG) {
        imagepng($resizedImage, $filePath, 8); // Compression level 8 for PNG
    }

    imagedestroy($image);
    imagedestroy($resizedImage);
    return true;
}


// Check if photo is uploaded
$photo = isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK ? $_FILES['photo'] : null;

// Handle file upload only if photo exists
if ($photo) {
    // Validate file size (max 5MB)
    if ($photo['size'] > $maxFileSize) {
        echo "Error: File is too large. Max size is 5MB.";
        exit;
    }

    // Validate image type (only JPEG and PNG)
    $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
    $detectedType = exif_imagetype($photo['tmp_name']);
    if (!in_array($detectedType, $allowedTypes)) {
        echo "Error: Only JPEG and PNG files are allowed.";
        exit;
    }

    // Move and resize the image
    $photoTmpName = $photo['tmp_name'];
    $photoName = $uploadDir . basename($photo['name']);

    if (move_uploaded_file($photoTmpName, $photoName)) {
        if (!resizeImage($photoName, $maxWidth, $maxHeight)) {
            echo "Error: Failed to process the image.";
            unlink($photoName); // Remove invalid file
            exit;
        }
    } else {
        echo "Error: Failed to move uploaded file for photo.";
    }
} else {
    $photoName = '';  // Set an empty value if no photo is uploaded
}


// Fetch all posts from the database, sorted by created_at (newest first)
$posts = $postsCollection->find([], ['sort' => ['created_at' => -1]])->toArray();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Platform</title>
    <link rel="stylesheet" href="community6.css">
</head>
<body>
    <nav style="border-color: black" class="navbar">
        <div class="navbar-logo">
            <img src="haha.png" alt="Code Logo">
        </div>
        <div class="navbar-logo">
            <img src="code logo.png" alt="Code Logo">
        </div>
      
        <div style="font-weight: bold" class="navbar-item12">
            <a href="community.php">Community</a>
        </div>
        <div style="font-weight: bold" class="navbar-item11">
            <a href="dashboard26.php">Home</a>
        </div>
        <div class="navbar-item1">
            <input style="border-color: black" type="text" placeholder="Search for anything">
        </div>
        <div style="font-weight: bold" class="navbar-item2">
            <a href="mylearning.php">My Learning</a>
        </div>
        <div class="navbar-item3">
    <a href="#" class="user-link">
        <img src="user.png" alt="User" class="user-icon">
    </a>
    <div class="dropdown-menu">
        <div class="user-info">
            <img src="user.png" alt="User" class="user-avatar">
            <p class="user-name"><?php echo $firstname . ' ' . $lastname; ?></p>
            <p class="user-email"><?php echo $userEmail; ?></p>
        </div>
        <ul class="menu-list">
            <li><a href="mylearning.php">My learning</a></li>
            <li><a href="cart.php">My cart</a></li>
            <hr>
            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="messages.php">Messages</a></li>
            <hr>
            <li><a href="editAccount.php">Account settings</a></li>
            <li><a href="#">Subscriptions</a></li>
            <li><a href="#">Purchase history</a></li>
            <li class="logout"><a href="../logout/logout.php">Logout</a></li>
        </ul>
    </div>
</div>
        <div class="navbar-item4">
            <a href="cart.php"><img src="shopping-cart.png" alt="User" class="user-icon"></a>
        </div>
        <div class="navbar-item5">
            <a href="notifications.php"><img src="bell1.png" alt="User" class="user-icon"></a>
        </div>
        <div class="navbar-item6">
            <a href="message_instructor.php" onclick="openChat()"><img src="messenger.png" alt="User" class="user-icon"></a>
        </div>
    </nav>

    <div class="category-links">
    <a href="category.php?category=Web%20Development">Web Development</a>
    <a href="category.php?category=IT%20&%20Software">IT & Software</a>
    <a href="category.php?category=UI/UX%20Design">UI/UX Design</a>
    <a href="category.php?category=CyberSecurity">CyberSecurity</a>
    <a href="category.php?category=Cloud%20Computing">Cloud Computing</a>
    <a href="category.php?category=Internet%20of%20Things%20(IoT)">Internet of Things (IoT)</a>
    </div>

    <div class="post-box">
        <div class="post-profile">
            <div class="profile-icon"></div>
            <input type="text" placeholder="Write something..." id="postInput">
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="postModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-btn">&times;</span>
                <h2>Create Post</h2>
            </div>
            <div class="modal-body">
                <div class="modal-profile">
                    <div class="profile-icon"></div>
                    <span class="profile-name">Your Name</span>
                </div>
                <textarea placeholder="What's on your mind?" class="modal-textarea" id="contentInput"></textarea>
            </div>
            <div class="modal-footer">
                <button class="modal-action-button" id="photoButton">Photos</button>
                <button class="modal-action-button" id="fileButton">File</button>
                <input type="file" id="photoInput" style="display: none;" accept="image/*">
                <input type="file" id="fileInput" style="display: none;">
                <button class="modal-post-button" onclick="submitPost()">Post</button>
            </div>
        </div>
    </div>

    <div class="review-section">
    <?php
    foreach ($posts as $post) {
        // Fetch user details for the post
        $user = $usersCollection->findOne(['_id' => $post['user_id']]);
        $profileName = $user ? htmlspecialchars($user['username']) : 'Anonymous';
        $postContent = htmlspecialchars($post['content']);
        $postTime = $post['created_at']->toDateTime()->format('Y-m-d H:i:s');
        $postId = $post['_id'];
        $likeCount = $likesCollection->countDocuments(['post_id' => $postId]);
        $dislikeCount = $dislikesCollection->countDocuments(['post_id' => $postId]);

        echo "
            <div class='review-card review-card-3'>
                <div class='review-profile'>
                    <div class='profile-icon'></div>
                    <div class='profile-name'>$profileName</div>
                </div>
                <p class='review-text'>$postContent</p>";

        // Display photo if available
        if (!empty($post['photo'])) {
            $photoUrl = 'uploads/' . basename($post['photo']);
            echo '<img src="' . htmlspecialchars($photoUrl) . '" alt="Post Image" style="max-width: 100%; max-height: 300px; display: block; margin: 10px auto;">';
        }

        echo "<div class='review-meta'>
                <span>Posted on: $postTime</span>
                <div class='review-actions'>
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='post_id' value='$postId'>
                        <button type='submit' name='action' value='like'>👍 $likeCount</button>
                    </form>
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='post_id' value='$postId'>
                        <button type='submit' name='action' value='dislike'>👎 $dislikeCount</button>
                    </form>
                    <button class='comment-button' onclick='toggleComments(\"comments-$postId\")'>💬</button>
                </div>
            </div>";

        // Comment Section for Each Post
        echo "<div id='comments-$postId' style='display:none; margin-top: 15px;'>
                <form method='POST' action='add_comment.php'>
                    <textarea name='comment' placeholder='Write a comment...'></textarea>
                    <input type='hidden' name='post_id' value='$postId'>
                    <button type='submit'>Post Comment</button>
                </form>";

        // Display existing comments if available
        if (!empty($post['comments'])) {
            foreach ($post['comments'] as $comment) {
                $commentContent = htmlspecialchars($comment['content']);
                $commentUser = htmlspecialchars($comment['user']);
                echo "<div class='comment' style='margin-top: 10px; padding-left: 15px;'>
                        <p><strong>$commentUser:</strong> $commentContent</p>";

                // Reply Form
                echo "<form method='POST' action='add_reply.php' style='margin-top: 5px;'>
                        <input type='hidden' name='post_id' value='$postId'>
                        <input type='hidden' name='comment_id' value='" . $comment['comment_id'] . "'>
                        <textarea name='reply' placeholder='Write a reply...'></textarea>
                        <button type='submit'>Reply</button>
                    </form>";

                // Display replies if available
                if (!empty($comment['replies'])) {
                    echo "<div class='replies' style='margin-left: 30px;'>";
                    foreach ($comment['replies'] as $reply) {
                        $replyContent = htmlspecialchars($reply['content']);
                        $replyUser = htmlspecialchars($reply['user']);
                        echo "<div class='reply' style='margin-top: 5px;'>
                                <p><strong>$replyUser:</strong> $replyContent</p>
                              </div>";
                    }
                    echo "</div>";
                }

                echo "</div>"; // End of individual comment
            }
        }

        echo "</div>"; // End of comments section
        echo "</div>"; // End of review card
    }
    ?>
</div>

<!-- JavaScript to Toggle Comment Section -->
<script>
function toggleComments(commentId) {
    const commentSection = document.getElementById(commentId);
    if (commentSection.style.display === 'none' || commentSection.style.display === '') {
        commentSection.style.display = 'block';
    } else {
        commentSection.style.display = 'none';
    }
}
</script>

    <script src="community.js"></script>
    <script>
        // JavaScript to trigger the file input dialogs
        document.getElementById('photoButton').addEventListener('click', function() {
            document.getElementById('photoInput').click();
        });

        document.getElementById('fileButton').addEventListener('click', function() {
            document.getElementById('fileInput').click();
        });

        // Handle the form submission
        function submitPost() {
            const content = document.getElementById('contentInput').value;
            const photoFile = document.getElementById('photoInput').files[0];
            const file = document.getElementById('fileInput').files[0];

            if (!content && !photoFile && !file) {
                alert('Please enter some content or select a file to upload.');
                return;
            }

            const formData = new FormData();
            formData.append('content', content);
            if (photoFile) formData.append('photo', photoFile);
            if (file) formData.append('media', file);

            fetch('community.php', {
                method: 'POST',
                body: formData,
            })
                .then((response) => response.text())
                .then((result) => {
                    alert(result); // Display the server response
                    location.reload(); // Reload the page to show the new post
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
        }

        function toggleComments(commentId) {
    const commentSection = document.getElementById(commentId);
    if (commentSection.style.display === 'none' || commentSection.style.display === '') {
        commentSection.style.display = 'block';
    } else {
        commentSection.style.display = 'none';
    }
}

function toggleReplies(commentId) {
    const replySection = document.getElementById(`replies_${commentId}`);
    replySection.style.display = (replySection.style.display === 'none' || replySection.style.display === '') ? 'block' : 'none';
}

function submitComment(postId) {
    const content = document.getElementById(`commentInput_${postId}`).value;
    if (!content) return;

    fetch('submit_comment.php', {
        method: 'POST',
        body: JSON.stringify({ post_id: postId, content: content }),
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.json()).then(data => {
        if (data.success) {
            // Reload comments section to display the new comment
            location.reload();
        }
    });
}

function submitReply(commentId, postId) {
    const content = document.getElementById(`replyInput_${commentId}`).value;
    if (!content) return;

    fetch('submit_reply.php', {
        method: 'POST',
        body: JSON.stringify({ comment_id: commentId, post_id: postId, content: content }),
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.json()).then(data => {
        if (data.success) {
            // Reload comments section to display the new reply
            location.reload();
        }
    });
}

    </script>
</body>
</html>