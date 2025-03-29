<?php
header('Content-Type: application/json');
echo json_encode([
    [
        'review_id' => 1,
        'user_id' => 101,
        'college_ranking' => 1,
        'rating' => 4.5,
        'review_text' => 'This is a sample review from the test API. It should appear in your review management system.',
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'source' => 'reviews',
        'college_name' => 'Test University',
        'college_location' => 'Test City, Test State',
        'user_name' => 'Test User'
    ],
    [
        'review_id' => 2,
        'user_id' => 102,
        'college_ranking' => 2,
        'rating' => 3.5,
        'review_text' => 'This is a sample college review from the test API. It should appear in your review management system.',
        'status' => 'approved',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'source' => 'collegereviews',
        'college_name' => 'Another University',
        'college_location' => 'Another City, Another State',
        'user_name' => 'College Reviewer'
    ],
    [
        'review_id' => 3,
        'user_id' => 103,
        'college_ranking' => 3,
        'rating' => 2.0,
        'review_text' => 'This is a rejected review sample from the test API. It should appear in your review management system.',
        'status' => 'rejected',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'source' => 'reviews',
        'college_name' => 'Third University',
        'college_location' => 'Third City, Third State',
        'user_name' => 'Rejected User'
    ]
]);
?> 