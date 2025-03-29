<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Setup and Repair Tool</h1>";

// Define database credentials
$servername = "localhost";  // Change if your database server is different
$username = "root";         // Replace with your actual database username
$password = "";             // Replace with your actual database password 
$dbname = "campuscompass";  // The target database name

try {
    // Create a new connection without specifying database
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Connected to database server</p>";
    
    // Check if campuscompass database exists
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ '$dbname' database exists</p>";
    } else {
        echo "<p>❌ '$dbname' database does not exist. Creating it...</p>";
        
        if ($conn->query("CREATE DATABASE $dbname")) {
            echo "<p>✅ Created '$dbname' database</p>";
        } else {
            throw new Exception("Failed to create database: " . $conn->error);
        }
    }
    
    // Select the database
    if (!$conn->select_db($dbname)) {
        throw new Exception("Failed to select database: " . $conn->error);
    }
    
    echo "<p>✅ Using '$dbname' database</p>";
    
    // 1. Fix collegereviews table
    $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ collegereviews table exists</p>";
        
        // Check if it has data
        $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
        $row = $result->fetch_assoc();
        $count = $row['count'];
        
        echo "<p>collegereviews table has $count records</p>";
        
        if ($count === 0) {
            echo "<p>❌ collegereviews table is empty. Adding sample data...</p>";
            // Logic to add sample data here
        }
    } else {
        echo "<p>❌ collegereviews table does not exist. Creating it...</p>";
        
        $sql = "CREATE TABLE collegereviews (
            review_id INT(11) NOT NULL AUTO_INCREMENT,
            user_id INT(11) NULL,
            college_ranking INT(11) NOT NULL,
            rating DECIMAL(3,1) NOT NULL,
            review_text TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (review_id)
        )";
        
        if ($conn->query($sql)) {
            echo "<p>✅ Created collegereviews table</p>";
        } else {
            echo "<p>❌ Failed to create collegereviews table: " . $conn->error . "</p>";
        }
    }
    
    // 2. Fix colleges table
    $result = $conn->query("SHOW TABLES LIKE 'colleges'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ colleges table exists</p>";
    } else {
        echo "<p>❌ colleges table does not exist. Creating it...</p>";
        
        $sql = "CREATE TABLE colleges (
            id INT(11) NOT NULL AUTO_INCREMENT,
            rankings INT(11) NOT NULL,
            name VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        )";
        
        if ($conn->query($sql)) {
            echo "<p>✅ Created colleges table</p>";
            
            // Add sample colleges
            echo "<p>Adding sample colleges...</p>";
            
            $sampleColleges = [
                ['rankings' => 1, 'name' => 'Harvard University', 'location' => 'Cambridge, MA'],
                ['rankings' => 2, 'name' => 'Stanford University', 'location' => 'Stanford, CA'],
                ['rankings' => 3, 'name' => 'MIT', 'location' => 'Cambridge, MA'],
                ['rankings' => 4, 'name' => 'Princeton University', 'location' => 'Princeton, NJ'],
                ['rankings' => 5, 'name' => 'Yale University', 'location' => 'New Haven, CT']
            ];
            
            $insertCount = 0;
            foreach ($sampleColleges as $college) {
                $stmt = $conn->prepare("INSERT INTO colleges (rankings, name, location) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $college['rankings'], $college['name'], $college['location']);
                
                if ($stmt->execute()) {
                    $insertCount++;
                }
                
                $stmt->close();
            }
            
            echo "<p>Added $insertCount sample colleges</p>";
        } else {
            echo "<p>❌ Failed to create colleges table: " . $conn->error . "</p>";
        }
    }
    
    // 3. Auto-fix config/database.php file
    echo "<h2>Fixing database.php file</h2>";
    
    $configFile = "../config/database.php";
    $configDir = dirname($configFile);
    
    if (!is_dir($configDir)) {
        mkdir($configDir, 0755, true);
        echo "<p>Created config directory</p>";
    }
    
    $config = "<?php
// Database connection configuration
\$servername = \"$servername\";
\$username = \"$username\";
\$password = \"$password\";
\$dbname = \"$dbname\";

// Create connection
\$conn = new mysqli(\$servername, \$username, \$password);

// Check connection
if (\$conn->connect_error) {
    die(\"Connection failed: \" . \$conn->connect_error);
}

// Try to select the database
if (!\$conn->select_db(\$dbname)) {
    // If database doesn't exist, try to create it
    if (\$conn->query(\"CREATE DATABASE IF NOT EXISTS $dbname\")) {
        \$conn->select_db('$dbname');
    } else {
        die(\"Error creating database: \" . \$conn->error);
    }
}
?>";
    
    if (file_put_contents($configFile, $config)) {
        echo "<p>✅ Updated database.php configuration file</p>";
    } else {
        echo "<p>❌ Failed to update database.php file. Please update it manually with the following content:</p>";
        echo "<pre>" . htmlspecialchars($config) . "</pre>";
    }
    
    // 4. Add sample data to collegereviews if needed
    $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
    $row = $result->fetch_assoc();
    if ((int)$row['count'] === 0) {
        echo "<h2>Adding sample collegereviews data</h2>";
        
        // Sample college reviews
        $sampleReviews = [
            [
                'user_id' => 101,
                'college_ranking' => 1,
                'rating' => 4.8,
                'review_text' => 'Our institution prides itself on providing a world-class education with state-of-the-art facilities and renowned faculty members.',
                'status' => 'approved'
            ],
            [
                'user_id' => 102,
                'college_ranking' => 2,
                'rating' => 4.2,
                'review_text' => 'We offer exceptional value for education with strong industry connections and impressive post-graduation placement rates.',
                'status' => 'pending'
            ],
            [
                'user_id' => 103,
                'college_ranking' => 3,
                'rating' => 4.5,
                'review_text' => 'Our college has been recognized for excellence in research and innovation, with numerous grants and partnerships with leading companies.',
                'status' => 'approved'
            ],
            [
                'user_id' => 104,
                'college_ranking' => 4,
                'rating' => 3.9,
                'review_text' => 'The academic environment at our institution challenges students while providing the support they need to succeed in their careers.',
                'status' => 'pending'
            ],
            [
                'user_id' => 105,
                'college_ranking' => 5,
                'rating' => 4.7,
                'review_text' => 'Our small class sizes and personalized approach to education ensure that every student receives the attention they deserve.',
                'status' => 'rejected'
            ]
        ];
        
        // Insert sample data
        $insertCount = 0;
        foreach ($sampleReviews as $review) {
            $stmt = $conn->prepare("INSERT INTO collegereviews (user_id, college_ranking, rating, review_text, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iidss", $review['user_id'], $review['college_ranking'], $review['rating'], $review['review_text'], $review['status']);
            
            if ($stmt->execute()) {
                $insertCount++;
            } else {
                echo "<p>❌ Error inserting review: " . $stmt->error . "</p>";
            }
            
            $stmt->close();
        }
        
        echo "<p>✅ Added $insertCount sample reviews to the collegereviews table</p>";
    }
    
    echo "<h2>Setup Complete</h2>";
    echo "<p>Your database should now be properly set up.</p>";
    echo "<p><a href='review-check.php'>Run a system check</a> to confirm everything is working.</p>";
    echo "<p><a href='../admin/review-management.html'>Go to Review Management</a> to view your collegereviews data.</p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 