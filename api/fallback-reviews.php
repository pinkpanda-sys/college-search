<?php
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review System Fallback</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #2c3e50;
        }
        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 150px;
        }
        .reviews {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        .review-card {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .review-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .review-body {
            padding: 15px;
        }
        .review-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status {
            padding: 3px 6px;
            border-radius: 50px;
            font-size: 12px;
        }
        .status.pending {
            background-color: #fff8e1;
            color: #f39c12;
        }
        .status.approved {
            background-color: #e8f8f5;
            color: #2ecc71;
        }
        .status.rejected {
            background-color: #feeae9;
            color: #e74c3c;
        }
        .actions button {
            background: none;
            border: none;
            cursor: pointer;
            margin-left: 10px;
        }
        .view {
            color: #3498db;
        }
        .approve {
            color: #2ecc71;
        }
        .reject {
            color: #e74c3c;
        }
        .delete {
            color: #e74c3c;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Review System Fallback</h1>
        <p>This page shows sample reviews for testing purposes when the main system is not working.</p>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Reviews</h3>
                <div>3</div>
            </div>
            <div class="stat-card">
                <h3>Pending Reviews</h3>
                <div>1</div>
            </div>
            <div class="stat-card">
                <h3>Approved Reviews</h3>
                <div>1</div>
            </div>
            <div class="stat-card">
                <h3>Rejected Reviews</h3>
                <div>1</div>
            </div>
        </div>
        
        <div class="reviews">
            <div class="review-card">
                <div class="review-header">
                    <h3>Sample University</h3>
                    <div>
                        <span>4.5 <i class="fas fa-star" style="color: #f39c12;"></i></span>
                        <span class="status pending">pending</span>
                    </div>
                </div>
                <div class="review-body">
                    <p>This is a sample review for testing purposes. The campus is beautiful and the professors are excellent.</p>
                    <div>
                        <i class="fas fa-user-circle"></i>
                        <span>John Doe</span>
                    </div>
                </div>
                <div class="review-footer">
                    <span>2023-05-15</span>
                    <div class="actions">
                        <button class="view"><i class="fas fa-eye"></i> View</button>
                        <button class="approve"><i class="fas fa-check"></i> Approve</button>
                        <button class="reject"><i class="fas fa-times"></i> Reject</button>
                        <button class="delete"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>
            
            <div class="review-card">
                <div class="review-header">
                    <h3>Test College</h3>
                    <div>
                        <span>3.5 <i class="fas fa-star" style="color: #f39c12;"></i></span>
                        <span class="status approved">approved</span>
                    </div>
                </div>
                <div class="review-body">
                    <p>The facilities are good but could use some improvement. Overall a decent college with good faculty.</p>
                    <div>
                        <i class="fas fa-user-circle"></i>
                        <span>Jane Smith</span>
                    </div>
                </div>
                <div class="review-footer">
                    <span>2023-05-10</span>
                    <div class="actions">
                        <button class="view"><i class="fas fa-eye"></i> View</button>
                        <button class="delete"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>
            
            <div class="review-card">
                <div class="review-header">
                    <h3>Another University</h3>
                    <div>
                        <span>2.0 <i class="fas fa-star" style="color: #f39c12;"></i></span>
                        <span class="status rejected">rejected</span>
                    </div>
                </div>
                <div class="review-body">
                    <p>I had a terrible experience at this university. The administration was unhelpful and classes were disorganized.</p>
                    <div>
                        <i class="fas fa-user-circle"></i>
                        <span>Alex Johnson</span>
                    </div>
                </div>
                <div class="review-footer">
                    <span>2023-05-05</span>
                    <div class="actions">
                        <button class="view"><i class="fas fa-eye"></i> View</button>
                        <button class="delete"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>
        </div>
        
        <a href="../admin/review-management.html" class="btn">Go Back to Review Management</a>
        
        <h2>Troubleshooting Tips</h2>
        <ol>
            <li>Check if your database connection is working properly</li>
            <li>Verify that the reviews table exists in your database</li>
            <li>Ensure that the API endpoints (reviews.php, test-review-data.php) are accessible</li>
            <li>Look for JavaScript errors in the browser console</li>
            <li>Make sure all CSS files are loading properly</li>
        </ol>
    </div>
</body>
</html> 