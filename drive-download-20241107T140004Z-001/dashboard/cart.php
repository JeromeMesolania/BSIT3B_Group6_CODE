<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: error404.html");
    exit();
}

require '../vendor/autoload.php';
include '../navbar/navbar.php';

use MongoDB\BSON\ObjectId;

$userId = $_SESSION['user_id'];
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase('CODE');
$cartCollection = $database->selectCollection('cart');
$coursesCollection = $database->selectCollection('courses');

// Fetch the cart items for the user
$cartCursor = $cartCollection->find(['userId' => $userId])->toArray();

$cartItems = [];
foreach ($cartCursor as $cartItem) {
    // Ensure 'items' is an array and contains courses
    if (isset($cartItem['items']) && is_array($cartItem['items'])) {
        foreach ($cartItem['items'] as $item) {
            // If course details are already included in the item (i.e., not just courseId)
            if (isset($item['courseId']) && isset($item['title']) && isset($item['price'])) {
                $cartItems[] = [
                    'courseId' => $item['courseId'], // Store the courseId for future removal
                    'title' => $item['title'],
                    'price' => $item['price'],
                    'instructor' => $item['instructorName'] ?? 'Unknown Instructor', // Handle missing instructor
                ];
            } elseif (isset($item['courseId'])) {
                // If only the courseId is available, fetch course details from courses collection
                $courseId = new ObjectId($item['courseId']); // Ensure ObjectId format
                $course = $coursesCollection->findOne(['_id' => $courseId]);

                // Add course data to cartItems if found
                if ($course) {
                    $cartItems[] = [
                        'courseId' => (string) $course['_id'], // Store the courseId for future removal
                        'title' => $course['title'],
                        'price' => $course['price'],
                        'instructor' => $course['instructorName'] ?? 'Unknown Instructor', // Handle missing instructor
                    ];
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="cart.css">
</head>
<body>

<h1>Your Shopping Cart</h1>

<div class="cart-container">
    <?php if (empty($cartItems)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <ul class="cart-items">
            <?php foreach ($cartItems as $item): ?>
                <li class="cart-item">
                    <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                    <p><strong>Instructor:</strong> <?php echo htmlspecialchars($item['instructor']); ?></p>
                    <p><strong>Price:</strong> ₱<?php echo htmlspecialchars($item['price']); ?></p>
                    <!-- Use courseId in the data attribute for easy removal -->
                    <button class="remove-item" data-course-id="<?php echo htmlspecialchars($item['courseId']); ?>">Remove</button>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="checkout">
            <button class="checkout-btn">Proceed to Checkout</button>
        </div>
    <?php endif; ?>
</div>

<!-- Optional: Add JavaScript to handle item removal -->
<script>
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            
            // Perform AJAX request to remove item from cart
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ courseId: courseId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the item from the DOM
                    this.closest('.cart-item').remove();
                } else {
                    alert('Error removing item from cart.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to remove item from cart.');
            });
        });
    });
</script>

</body>
</html>
