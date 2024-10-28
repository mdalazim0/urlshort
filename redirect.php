<?php
// Firebase configuration (add your Firebase connection here)
require 'vendor/autoload.php'; // Make sure to include Firebase PHP SDK

use Kreait\Firebase\Factory;

$factory = (new Factory)->withServiceAccount('path/to/your/firebase_credentials.json');
$database = $factory->createDatabase();

// Get the short code from the URL
$shortCode = $_GET['id'] ?? '';

// Fetch the link from the database
if ($shortCode) {
    $linkData = $database->getReference('urls/' . $shortCode)->getValue();
    if ($linkData) {
        $link = $linkData['link'];

        // Redirect to the original link
        header("Location: $link");
        exit();
    } else {
        echo "Short URL not found!";
    }
} else {
    echo "No short code provided!";
}
?>
