<?php
header('Content-Type: text/html');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Review System Connectivity Test</h1>";

// Test if files exist
echo "<h2>File Existence Check</h2>";
$files = [
    '../admin/review-management.html' => 'Review Management HTML',
    '../admin/review-management.css' => 'Review Management CSS',
    '../admin/review-management.js' => 'Review Management JavaScript',
    'reviews.php' => 'Reviews API',
    'simple-test-reviews.php' => 'Simple Test Reviews API',
    'test-review-data.php' => 'Test Review Data API'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<p>✅ $description file exists: $file</p>";
    } else {
        echo "<p>❌ $description file NOT found: $file</p>";
    }
}

// Test simple test reviews API
echo "<h2>Simple Test Reviews API Check</h2>";
echo "<p>Testing access to simple-test-reviews.php...</p>";

if (file_exists('simple-test-reviews.php')) {
    $url = "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . 
          "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/simple-test-reviews.php";
    
    echo "<p>URL: $url</p>";
    
    // Try to fetch using curl
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p>HTTP Status: $httpCode</p>";
        
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "<p>✅ API is accessible</p>";
            
            // Check if it's valid JSON
            $json = json_decode($response);
            if ($json !== null) {
                echo "<p>✅ API returns valid JSON</p>";
                echo "<p>Number of reviews: " . count($json) . "</p>";
            } else {
                echo "<p>❌ API does not return valid JSON</p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 300)) . "...</pre>";
            }
        } else {
            echo "<p>❌ Could not access API (HTTP $httpCode)</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    } else {
        echo "<p>⚠️ CURL not available, can't test API access</p>";
        echo "<p>You can try accessing <a href='$url' target='_blank'>$url</a> directly</p>";
    }
} else {
    echo "<p>❌ simple-test-reviews.php file doesn't exist. Please create it first.</p>";
}

// Test CORS headers
echo "<h2>CORS Headers Check</h2>";
$corsFiles = [
    'simple-test-reviews.php',
    'reviews.php'
];

foreach ($corsFiles as $file) {
    if (file_exists($file)) {
        echo "<p>Testing CORS headers for $file...</p>";
        
        $url = "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . 
              "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/$file";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (strpos($response, 'Access-Control-Allow-Origin') !== false) {
            echo "<p>✅ CORS headers are set for $file</p>";
        } else {
            echo "<p>❌ CORS headers are missing for $file</p>";
            echo "<p>This might cause issues when accessing from JavaScript</p>";
        }
    }
}

// Provide links for next steps
echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='../admin/review-management.html' target='_blank'>Open Review Management Page</a></li>";
echo "<li><a href='simple-test-reviews.php' target='_blank'>View Test Reviews Data</a></li>";
echo "</ul>";

echo "<h2>Debugging Tips</h2>";
echo "<ol>";
echo "<li>Open your browser's developer tools (F12 or Ctrl+Shift+I)</li>";
echo "<li>Check the Console tab for JavaScript errors</li>";
echo "<li>Check the Network tab to see if API requests are succeeding</li>";
echo "<li>Make sure all required JavaScript and CSS files are loading properly</li>";
echo "</ol>";
?> 