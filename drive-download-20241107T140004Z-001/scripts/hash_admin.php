<?php
require '../connection/db_connection.php';

function hashAdminPassword($plainPassword) {
    // Use password_hash to hash the plain text password
    return password_hash($plainPassword, PASSWORD_DEFAULT);
}

// Connect to MongoDB
$db = connectMongoDB();
$adminCollection = $db->admin;

// Define the admin credentials
$adminData = [
    'username' => 'admin',
    'password' => hashAdminPassword('admin_22-00645')  // Hash the plain password here
];

// Insert or Update the admin document in the database
$result = $adminCollection->updateOne(
    ['username' => $adminData['username']], // Search criteria
    ['$set' => $adminData],                 // Update operation
    ['upsert' => true]                      // Insert if not exists
);

if ($result->getModifiedCount() > 0 || $result->getUpsertedCount() > 0) {
    echo "Admin credentials have been added/updated successfully!";
} else {
    echo "No changes were made to the admin credentials.";
}
?>
