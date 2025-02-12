<?php
// Configuration
include_once('../../../include/php/connect.php');


// Get image data and caption from request body
$requestBody = json_decode(file_get_contents('php://input'), true);
$imageData = $requestBody['image'];
$caption = $requestBody['caption'];

// Decode base64-encoded image data
$imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
$imageData = str_replace(' ', '+', $imageData);
$imageData = base64_decode($imageData);

// Generate a unique filename
$filename = uniqid() . '.jpg';

// Save image to uploads directory
$uploadDirectory = 'uploads/';
$filePath = $uploadDirectory . $filename;
file_put_contents($filePath, $imageData);

// Insert photo file path and caption into database
$stmt = $conn->prepare("INSERT INTO test (photo_path, caption) VALUES (?, ?)");
$stmt->bind_param("ss", $filePath, $caption);
if ($stmt->execute()) {
    echo "Photo uploaded successfully.";
} else {
    echo "Error uploading photo: " . $conn->error;
}
$stmt->close();

// Close MySQL connection
$conn->close();
?>