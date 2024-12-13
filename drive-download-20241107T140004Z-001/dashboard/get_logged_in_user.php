<?php
// get_logged_in_user.php
session_start();
if (isset($_SESSION['user_id'])) {
    echo json_encode(['userId' => $_SESSION['user_id']]);
} else {
    echo json_encode(['userId' => null]);
}
?>
