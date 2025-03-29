document.addEventListener('DOMContentLoaded', function() {
    console.log("Review management script loaded");
    
    // DOM Elements
    const reviewsList = document.getElementById('reviewsList');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const noDataMessage = document.getElementById('noDataMessage');
    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('statusFilter');
    const sourceSelect = document.getElementById('sourceFilter');
    const closeReviewModalBtn = document.getElementById('closeReviewModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    
    // Stats elements
    const totalReviewsEl = document.getElementById('totalReviews');
    const pendingReviewsEl = document.getElementById('pendingReviews');
    const approvedReviewsEl = document.getElementById('approvedReviews');
    const rejectedReviewsEl = document.getElementById('rejectedReviews');
    
    // Review Modal Elements
    const reviewModal = document.getElementById('reviewModal');
    const reviewModalBody = document.getElementById('reviewModalBody');
    const approveReviewBtn = document.getElementById('approveReviewBtn');
    const rejectReviewBtn = document.getElementById('rejectReviewBtn');
    const deleteReviewBtn = document.getElementById('deleteReviewBtn');
    
    // Current review ID and source for action buttons
    let currentReviewId = null;
    let currentReviewSource = null;
    
    // Skip test data and use the database API directly
    let currentEndpointIndex = 0; // Start with database API endpoint
    
    // Make the API endpoints
    const API_ENDPOINTS = [
        '../api/reviews.php',
        '../api/test-review-data.php', 
        '../api/simple-test-reviews.php'
    ];
    
    // Set up button for testing/debugging - allows a quick way to create college reviews table
    const setupButton = document.getElementById('setupCollegeReviews');
    if (setupButton) {
        setupButton.addEventListener('click', function() {
            window.location.href = '../api/setup-collegereviews.php';
        });
    }
    
    // Load reviews
    loadReviews();
    
    // Event Listeners
    if (closeReviewModalBtn) {
        closeReviewModalBtn.addEventListener('click', closeReviewModal);
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeReviewModal);
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterReviews, 500));
    }
    
    if (filterSelect) {
        filterSelect.addEventListener('change', filterReviews);
    }
    
    if (sourceSelect) {
        sourceSelect.addEventListener('change', filterReviews);
    }
    
    if (approveReviewBtn) {
        approveReviewBtn.addEventListener('click', () => updateReviewStatus('approved'));
    }
    
    if (rejectReviewBtn) {
        rejectReviewBtn.addEventListener('click', () => updateReviewStatus('rejected'));
    }
    
    if (deleteReviewBtn) {
        deleteReviewBtn.addEventListener('click', deleteReview);
    }
    
    // Debounce function to prevent excessive API calls
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
    
    // Function to load reviews from API
    function loadReviews() {
        console.log("Loading reviews");
        
        if (!reviewsList || !loadingIndicator || !noDataMessage) {
            console.error('Required DOM elements are missing');
            return;
        }
        
        showLoading();
        
        // Always use the primary database API endpoint
        let url = '../api/reviews.php';
        const params = new URLSearchParams();
        
        // Always set source to collegereviews
        params.append('source', 'collegereviews');
        
        if (filterSelect && filterSelect.value !== 'all') {
            params.append('status', filterSelect.value);
        }
        
        if (searchInput && searchInput.value.trim()) {
            params.append('search', searchInput.value.trim());
        }
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        console.log(`Loading reviews from: ${url}`);
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server returned ${response.status} ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                hideLoading();
                
                // Check if data contains an error
                if (data.error) {
                    showNoData(data.message || "Error loading reviews");
                    
                    // Show setup button
                    const setupBtn = document.getElementById('setupCollegeReviews');
                    if (setupBtn) {
                        setupBtn.style.display = 'block';
                    }
                    
                    return;
                }
                
                console.log(`Received ${data.length} reviews`, data);
                
                if (!data || data.length === 0) {
                    showNoData();
                    updateStats(0, 0, 0, 0);
                    return;
                }
                
                // Update stats
                const stats = calculateStats(data);
                updateStats(data.length, stats.pending, stats.approved, stats.rejected);
                
                // Display reviews
                displayReviews(data);
            })
            .catch(error => {
                console.error(`Error fetching reviews: ${error.message}`);
                hideLoading();
                showNoData("Error loading reviews: " + error.message);
            });
    }
    
    // Function to display reviews
    function displayReviews(reviews) {
        if (!reviewsList) {
            console.error('Reviews list element not found');
            return;
        }
        
        reviewsList.innerHTML = '';
        
        // Create review cards
        reviews.forEach(review => {
            const card = createReviewCard(review);
            reviewsList.appendChild(card);
        });
    }
    
    // Create a review card
    function createReviewCard(review) {
        // Account for different column naming conventions
        const reviewId = review.review_id || review.r_id || 'N/A';
        const userId = review.user_id || review.u_id || 'N/A';
        const collegeRanking = review.college_ranking || 'N/A';
        const rating = review.rating || 'N/A';
        const reviewText = review.review_text || 'No review text available';
        const status = review.status || 'pending'; // Default to pending if no status
        
        // Create a DOM element instead of returning a string
        const card = document.createElement('div');
        card.className = `review-card ${status}`;
        card.innerHTML = `
            <div class="review-header">
                <span class="review-id">#${reviewId}</span>
                <span class="review-status">${status.toUpperCase()}</span>
            </div>
            <div class="review-body">
                <p><strong>User ID:</strong> ${userId}</p>
                <p><strong>College:</strong> ${collegeRanking}</p>
                <p><strong>Rating:</strong> ${rating}/5</p>
                <p class="review-text">${reviewText.substring(0, 100)}...</p>
            </div>
            <div class="review-actions">
                <button class="view-details" data-id="${reviewId}">View Details</button>
                ${status === 'pending' ? `
                    <button class="approve-btn" data-id="${reviewId}">✓ Approve</button>
                    <button class="reject-btn" data-id="${reviewId}">✕ Delete</button>
                ` : ''}
            </div>
        `;
        
        // Add event listener for the "View Details" button
        const viewDetailsBtn = card.querySelector('.view-details');
        if (viewDetailsBtn) {
            viewDetailsBtn.addEventListener('click', function() {
                openReviewModal(reviewId);
            });
        }
        
        return card;
    }
    
    // Calculate stats from reviews
    function calculateStats(reviews) {
        const stats = {
            pending: 0,
            approved: 0,
            rejected: 0
        };
        
        reviews.forEach(review => {
            if (review.status === 'pending' || !review.status) {
                stats.pending++;
            } else if (review.status === 'approved') {
                stats.approved++;
            } else if (review.status === 'rejected') {
                stats.rejected++;
            }
        });
        
        return stats;
    }
    
    // Update stats display
    function updateStats(total, pending, approved, rejected) {
        if (totalReviewsEl) totalReviewsEl.textContent = total;
        if (pendingReviewsEl) pendingReviewsEl.textContent = pending;
        if (approvedReviewsEl) approvedReviewsEl.textContent = approved;
        if (rejectedReviewsEl) rejectedReviewsEl.textContent = rejected;
    }
    
    // Open review details modal
    function openReviewModal(reviewId, source) {
        if (!reviewModal || !reviewModalBody) {
            console.error('Review modal elements not found');
            return;
        }
        
        currentReviewId = reviewId;
        currentReviewSource = source || 'reviews';
        
        showLoading();
        
        // Try each endpoint in sequence for the modal data
        function tryNextEndpoint(index) {
            if (index >= API_ENDPOINTS.length) {
                hideLoading();
                alert('Error: Could not load review details from any endpoint');
                return;
            }
            
            const url = `${API_ENDPOINTS[index]}?id=${reviewId}&source=${source}`;
            console.log(`Trying to fetch review details from: ${url}`);
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Server returned ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(review => {
                    hideLoading();
                    
                    // Format date
                    let formattedDate = 'Unknown Date';
                    if (review.created_at) {
                        try {
                            const date = new Date(review.created_at);
                            formattedDate = date.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        } catch (e) {
                            console.error('Date parsing error:', e);
                        }
                    }
                    
                    // Account for different column naming conventions
                    const reviewId = review.review_id || review.r_id || 'N/A';
                    const userId = review.user_id || review.u_id || 'N/A';
                    const collegeRanking = review.college_ranking || 'N/A';
                    const rating = review.rating || 'N/A';
                    const reviewText = review.review_text || 'No review text available';
                    const status = review.status || 'pending'; // Default to pending if no status
                    
                    // Fill modal with review data
                    reviewModalBody.innerHTML = `
                        <div class="review-detail">
                            <h3>Review Details #${reviewId}</h3>
                            <div class="detail-group">
                                <p><strong>User ID:</strong> ${userId}</p>
                                <p><strong>College:</strong> ${collegeRanking}</p>
                                <p><strong>Rating:</strong> ${rating}/5</p>
                            </div>
                            <div class="detail-group">
                                <p><strong>Status:</strong> <span class="status-badge ${status}">${status.toUpperCase()}</span></p>
                                <p><strong>Created At:</strong> ${formattedDate}</p>
                            </div>
                            <div class="full-review">
                                <h4>Full Review Text:</h4>
                                <p>${reviewText}</p>
                            </div>
                        </div>
                    `;
                    
                    // Show/hide action buttons based on status
                    if (approveReviewBtn) approveReviewBtn.style.display = status === 'pending' ? 'inline-block' : 'none';
                    if (rejectReviewBtn) rejectReviewBtn.style.display = status === 'pending' ? 'inline-block' : 'none';
                    
                    // Show the modal
                    reviewModal.style.display = 'block';
                })
                .catch(error => {
                    console.error(`Error loading review details from ${url}:`, error);
                    // Try next endpoint
                    tryNextEndpoint(index + 1);
                });
        }
        
        // Start with the first endpoint
        tryNextEndpoint(0);
    }
    
    // Close review modal
    function closeReviewModal() {
        if (reviewModal) {
            reviewModal.style.display = 'none';
            currentReviewId = null;
            currentReviewSource = null;
        }
    }
    
    // Update review status
    async function updateReviewStatus(reviewId, status) {
        try {
            const response = await fetch('../api/reviews.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ review_id: reviewId, status })
            });
            return handleResponse(response, 'Status updated successfully');
        } catch (error) {
            showError('Error updating review status');
        }
    }
    
    // Delete review
    async function deleteReview(reviewId) {
        try {
            const response = await fetch(`../api/reviews.php?id=${reviewId}`, {
                method: 'DELETE'
            });
            return handleResponse(response, 'Review deleted successfully');
        } catch (error) {
            showError('Error deleting review');
        }
    }
    
    // Filter reviews
    function filterReviews() {
        console.log("Filtering reviews...");
        loadReviews(); // This will use current filter values
    }
    
    // Show loading indicator
    function showLoading() {
        if (loadingIndicator) loadingIndicator.style.display = 'block';
        if (noDataMessage) noDataMessage.style.display = 'none';
    }
    
    // Hide loading indicator
    function hideLoading() {
        if (loadingIndicator) loadingIndicator.style.display = 'none';
    }
    
    // Show no data message
    function showNoData(message = 'No reviews found. When users add reviews, they will appear here.') {
        if (noDataMessage) {
            noDataMessage.style.display = 'block';
            const messageElement = noDataMessage.querySelector('p');
            if (messageElement) {
                messageElement.textContent = message;
            }
        }
    }
    
    // Add a function to process external data if needed
    function processExternalData(data) {
        // If using jsonplaceholder.typicode.com fallback
        if (Array.isArray(data) && data.length > 0 && data[0].hasOwnProperty('email')) {
            console.log("Processing data from external API");
            return data.slice(0, 10).map(item => ({
                review_id: item.id,
                user_id: item.id,
                college_ranking: Math.floor(Math.random() * 10) + 1,
                rating: (Math.random() * 3 + 2).toFixed(1), // Random rating between 2-5
                review_text: item.body,
                status: ['pending', 'approved', 'rejected'][Math.floor(Math.random() * 3)],
                created_at: new Date().toISOString(),
                source: Math.random() > 0.5 ? 'reviews' : 'collegereviews',
                college_name: `College #${Math.floor(Math.random() * 100) + 1}`,
                college_location: 'Example Location',
                user_name: item.email.split('@')[0]
            }));
        }
        return data;
    }

    // Add this to your debug functions
    function checkDatabaseConnection() {
        const consoleOutput = document.getElementById('consoleOutput');
        if (!consoleOutput) return;
        
        consoleOutput.innerHTML = '<div style="color:#f39c12">Checking database connection...</div>' + consoleOutput.innerHTML;
        
        fetch('../api/reviews.php?test=db')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    consoleOutput.innerHTML = '<div style="color:#2ecc71">✅ Database connection successful</div>' + consoleOutput.innerHTML;
                } else {
                    consoleOutput.innerHTML = `<div style="color:#e74c3c">❌ Database connection failed: ${data.message || 'Unknown error'}</div>` + consoleOutput.innerHTML;
                }
            })
            .catch(error => {
                consoleOutput.innerHTML = `<div style="color:#e74c3c">❌ Database connection test failed: ${error.message}</div>` + consoleOutput.innerHTML;
            });
    }

    // Add this button to the debug menu
    document.getElementById('debugMenu').innerHTML += `
        <button id="checkDbBtn" style="padding: 4px 8px; background: #2980b9; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 11px; margin-top: 5px;">
            Check Database Connection
        </button>
    `;

    document.getElementById('checkDbBtn').addEventListener('click', checkDatabaseConnection);

    // Add event listeners for approve/reject buttons
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('approve-btn')) {
            const reviewId = e.target.dataset.id;
            await updateReviewStatus(reviewId, 'approved');
            loadReviews();
        }
        
        if (e.target.classList.contains('reject-btn')) {
            const reviewId = e.target.dataset.id;
            if (confirm('Are you sure you want to delete this review?')) {
                await deleteReview(reviewId);
                loadReviews();
            }
        }
    });

    function handleResponse(response, successMessage) {
        if (!response.ok) throw new Error('Request failed');
        const toast = document.createElement('div');
        toast.className = 'status-toast success';
        toast.textContent = successMessage;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    function showError(message) {
        const toast = document.createElement('div');
        toast.className = 'status-toast error';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}); 