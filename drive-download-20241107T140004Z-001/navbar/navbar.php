<?php 
// Check if a user session exists
if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

require '../vendor/autoload.php';
require '../connection/db_connection.php';

use MongoDB\BSON\ObjectId;

// Fetch the user ID and role from the session
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'student'; // Default role if not set

$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$usersCollection = $database->selectCollection('users');
$user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectID($userId)]);

if ($user) {
    $firstname = $user['firstname'] ?? 'Unknown';
    $lastname = $user['lastname'] ?? 'User';
    $userEmail = $user['email'] ?? 'No email';
} else {
    $firstname = 'Unknown';
    $lastname = 'User';
    $userEmail = 'No email';
}

try {
    // Query the user collection based on the user ID and role
    $user = $usersCollection->findOne([
        '_id' => new ObjectId($userId),
        'role' => $role
    ]);

    if ($user) {
        $userName = htmlspecialchars($user['firstname'] . ' ' . $user['lastname']);
        $userEmail = htmlspecialchars($user['email']);
    } else {
        $userName = 'Unknown User';
        $userEmail = 'Unknown Email';
    }
} catch (Exception $e) {
    $userName = 'Error fetching user';
    $userEmail = 'Error fetching email';
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav style="border-color: black" class="navbar">
        <div class="navbar-logo">
            <img src="../dashboard/haha.png" alt="Code Logo">
        </div>
        <div class="navbar-logo">
            <img src="../dashboard/code logo.png" alt="Code Logo">
        </div>
    
      
        <div style="font-weight: bold" class="navbar-item12">
            <a href="../dashboard/community.php">Community</a>
        </div>
        <div style="font-weight: bold" class="navbar-item11">
            <a href="../dashboard/dashboard26.php">Home</a>
        </div>
        <div style="font-weight: bold" class="navbar-item1">
            <input style="border-color:black" type="text" placeholder="Search for anything">
        </div>
        <div style="font-weight: bold" class="navbar-item2">
            <a href="../dashboard/mylearning.php">My Learning</a>
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
        <ul style="font-weight: bold" class="menu-list">
            <li><a href="mylearning.php">My learning</a></li>
            <li><a href="cart.php">My cart</a></li>
            <hr>
            <li><a href="#">Notifications</a></li>
            <li><a href="message_instructor.php">Messages</a></li>
            <hr>
            <li><a href="#">Account settings</a></li>
            <li><a href="#">Subscriptions</a></li>
            <li><a href="#">Purchase history</a></li>
            <li class="logout"><a href="../logout/logout.php">Logout</a></li>
        </ul>
    </div>
</div>
        <div class="navbar-item4">
            <a href="cart.php" class="cart-link">
                <img src="../dashboard/shopping-cart.png" alt="Shopping Cart" class="user-icon">
            </a>
            <a href="#" class="cart-text"></a>
        </div>
        <div class="navbar-item5">
            <a href="notifications.php" class="notification-link">
                <img src="../dashboard/bell1.png" alt="Notifications" class="user-icon">
                <span class="notification-text"></span>
            </a>
        </div>
        
    </nav>
    <div class="navbar-item6">
    <a href="message_instructor.php"><img src="../dashboard/messenger.png" alt="User" class="user-icon"></a>
    </div>

    <div style="font-weight: bold" class="category-links">
    <a href="category.php?category=Web%20Development">Web Development</a>
    <a href="category.php?category=IT%20&%20Software">IT & Software</a>
    <a href="category.php?category=UI/UX%20Design">UI/UX Design</a>
    <a href="category.php?category=CyberSecurity">CyberSecurity</a>
    <a href="category.php?category=Cloud%20Computing">Cloud Computing</a>
    <a href="category.php?category=Internet%20of%20Things%20(IoT)">Internet of Things (IoT)</a>
    </div>
  

<script>
    let currentIndex = 0;
const images = document.querySelectorAll(".carousel-image");
const intervalTime = 3000; 

function showNextImage() {
    
    images[currentIndex].classList.remove("active");
    
    
    currentIndex = (currentIndex + 1) % images.length;
    
   
    images[currentIndex].classList.add("active");
}


images[currentIndex].classList.add("active");
setInterval(showNextImage, intervalTime);



function nextSlide() {
    const carousel = document.querySelector('.carousel-items');
    carousel.scrollBy({ left: 320, behavior: 'smooth' }); 
}

function prevSlide() {
    const carousel = document.querySelector('.carousel-items');
    carousel.scrollBy({ left: -320, behavior: 'smooth' });
}

const userIcon = document.querySelector('.user-icon');
    const dropdown = document.getElementById('dropdown');
    const chatbox = document.getElementById('chatbox');
    const message1 = document.getElementById('message1');
    const chatInput = document.getElementById('chatInput');
    const chatboxBody = document.getElementById('chatboxBody');
    const sendButton = document.getElementById('sendButton');

   
    userIcon.addEventListener('mouseover', () => {
      dropdown.classList.add('active');
    });

   
    userIcon.addEventListener('mouseout', () => {
      dropdown.classList.remove('active');
    });

  
    message1.addEventListener('click', () => {
      chatbox.style.display = 'flex'; 
      dropdown.classList.remove('active'); 
    });

   
    sendButton.addEventListener('click', () => {
      const message = chatInput.value.trim();
      if (message) {
        const newMessage = document.createElement('p');
        newMessage.textContent = `You: ${message}`;
        chatboxBody.appendChild(newMessage);
        chatInput.value = ''; 
        chatboxBody.scrollTop = chatboxBody.scrollHeight; 
      }
    });

   
    document.addEventListener('click', (e) => {
      if (!chatbox.contains(e.target) && !dropdown.contains(e.target) && !userIcon.contains(e.target)) {
        chatbox.style.display = 'none';
      }
    });

    document.querySelector('.cart-link').addEventListener('click', async () => {
    try {
        const response = await fetch('../addtocart/fetchCart.php'); // Fetch data from the PHP endpoint
        const cartItems = await response.json(); // Parse the response as JSON

        // Display cart items (customize as needed)
        let cartContent = '';
        if (cartItems.length === 0) {
            cartContent = '<p>Your cart is empty.</p>';
        } else {
            cartItems.forEach(item => {
                cartContent += `<p>${item.title} - ${item.price}</p>`;
            });
        }

        // Create a popup or modal to show the cart content
        const cartModal = document.createElement('div');
        cartModal.className = 'cart-modal';
        cartModal.innerHTML = `
            <div class="cart-modal-content">
                <span class="close-cart">&times;</span>
                <h2>Your Cart</h2>
                ${cartContent}
            </div>`;
        
        document.body.appendChild(cartModal);

        // Close the modal when the 'close' button is clicked
        document.querySelector('.close-cart').addEventListener('click', () => {
            document.body.removeChild(cartModal);
        });
    } catch (error) {
        console.error('Error fetching cart data:', error);
    }
});

</script>
<script src="script.js"></script>
</body>
</html>