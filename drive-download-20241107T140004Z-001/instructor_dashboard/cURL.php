<?php
require_once '../vendor/autoload.php'; 

use Firebase\Auth\Token\Exception\InvalidToken;
use Firebase\Auth\Token\Verifier;

session_start();

// Firebase API credentials
$firebase_project_id = "videocode-ad5ae";
$firebase_api_key = "AIzaSyD27m7lMO0xzf2qQA_yRO4CQ7LL9Gs40C0";

// Function to upload file to Firebase Storage
function uploadToFirebase($file, $uploadPath) {
    $url = "https://firebasestorage.googleapis.com/v0/b/videocode-ad5ae.appspot.com/o?uploadType=multipart&name=$uploadPath";

    // Prepare the file and metadata
    $fileData = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
    $boundary = uniqid();
    
    // Set the request headers
    $headers = [
        "Authorization: Bearer AIzaSyD27m7lMO0xzf2qQA_yRO4CQ7LL9Gs40C0",
        "Content-Type: multipart/form-data; boundary=$boundary",
    ];

    // Prepare the multipart form data for the POST request
    $data = "--$boundary\r\n";
    $data .= "Content-Disposition: form-data; name=\"file\"; filename=\"" . $file['name'] . "\"\r\n";
    $data .= "Content-Type: " . $file['type'] . "\r\n\r\n";
    $data .= $fileData . "\r\n";
    $data .= "--$boundary--";

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        // Handle error
        echo 'cURL error: ' . curl_error($ch);
        return false;
    }

    curl_close($ch);

    // Parse response to extract the file URL (if successful)
    $responseData = json_decode($response, true);
    if (isset($responseData['downloadTokens'])) {
        $fileUrl = "https://firebasestorage.googleapis.com/v0/b/videocode-ad5ae.appspot.com/o/" . urlencode($uploadPath) . "?alt=media&token=" . $responseData['downloadTokens'];
        return $fileUrl;
    }

    return false;
}
