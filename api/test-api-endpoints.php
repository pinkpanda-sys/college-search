<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>API Endpoint Test</h1>";

$endpoints = [
    '../api/reviews.php' => 'Primary Reviews API',
    'test-review-data.php' => 'Test Review Data API',
    'simple-test-reviews.php' => 'Simple Test Reviews API'
];

echo "<h2>Testing Endpoints</h2>";

foreach ($endpoints as $endpoint => $description) {
    echo "<h3>$description</h3>";
    echo "<p>Endpoint: $endpoint</p>";
    
    // Test if file exists
    if (!file_exists($endpoint)) {
        echo "<p>❌ File does not exist</p>";
        continue;
    }
    
    echo "<p>✅ File exists</p>";
    
    // Make a request to the endpoint
    $url = "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . 
          "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/" . $endpoint;
    
    echo "<p>Testing URL: $url</p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p>HTTP Status: $httpCode</p>";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<p>✅ API is responding</p>";
        
        // Try to parse JSON
        $data = json_decode($response, true);
        if ($data !== null) {
            echo "<p>✅ API returns valid JSON</p>";
            echo "<p>Number of reviews: " . (is_array($data) ? count($data) : 'Not an array') . "</p>";
            
            if (is_array($data) && count($data) > 0) {
                echo "<details>";
                echo "<summary>View first review data</summary>";
                echo "<pre>" . htmlspecialchars(json_encode($data[0], JSON_PRETTY_PRINT)) . "</pre>";
                echo "</details>";
            } else {
                echo "<p>⚠️ API returned empty dataset</p>";
            }
        } else {
            echo "<p>❌ API does not return valid JSON</p>";
            echo "<details>";
            echo "<summary>View response</summary>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
            echo "</details>";
        }
    } else {
        echo "<p>❌ API request failed</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
    
    echo "<hr>";
}

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='setup-test-reviews.php'>Set up test review data</a></li>";
echo "<li><a href='simple-test-reviews.php'>View simple test reviews JSON</a></li>";
echo "<li><a href='../admin/review-management.html'>Go to Review Management</a></li>";
echo "</ul>";
?> 