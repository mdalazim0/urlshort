<?php
// Firebase configuration (make sure to include the Firebase PHP SDK)
require 'vendor/autoload.php'; // Composer autoload for Firebase PHP SDK

use Kreait\Firebase\Factory;

$factory = (new Factory)->withServiceAccount('path/to/your/firebase_credentials.json');
$database = $factory->createDatabase();

// Handle redirect if short code is present
if (isset($_GET['id'])) {
    $shortCode = $_GET['id'];
    $linkData = $database->getReference('urls/' . $shortCode)->getValue();
    
    if ($linkData) {
        $link = $linkData['link'];
        header("Location: $link");
        exit();
    } else {
        echo "<h2>Short URL not found!</h2>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link = $_POST['userLink'];

    // Check if the link starts with http:// or https://
    if (!preg_match("~^(?:f|ht)tps?://~i", $link)) {
        $link = 'http://' . $link; // Default to http://
    }

    $shortCode = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5); // Generate short code
    $transactionData = [
        'code' => $shortCode,
        'link' => $link,
        'status' => 'pending',
        'date' => date('Y-m-d H:i:s'),
    ];

    // Save to Firebase
    $database->getReference('urls/' . $shortCode)->set($transactionData);
    $shortUrl = "http://yourdomain.com/index.php?id=" . $shortCode; // Update to your actual domain
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Shortener</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        input[type="text"], button {
            margin: 10px 0;
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        #shortUrl {
            margin-top: 20px;
            display: none;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 320px;
            text-align: center;
        }
    </style>
</head>
<body>

    <h2>Create Short URL</h2>
    <form method="POST">
        <input type="text" name="userLink" placeholder="Enter your link here" required>
        <button type="submit">Create Link</button>
    </form>
    
    <?php if (isset($shortUrl)): ?>
    <div id="shortUrl" style="display: block;">
        <p><strong>Short URL:</strong> <span><?php echo $shortUrl; ?></span></p>
        <button onclick="copyToClipboard('<?php echo $shortUrl; ?>')">Copy Link</button>
    </div>
    <?php endif; ?>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Link copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>

</body>
</html>
