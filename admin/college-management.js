document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const collegeTable = document.getElementById('collegeTableBody');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const noDataMessage = document.getElementById('noDataMessage');
    const searchInput = document.getElementById('searchCollege');
    
    // Modal Elements
    const collegeModal = document.getElementById('collegeModal');
    const modalTitle = document.getElementById('modalTitle');
    const collegeForm = document.getElementById('collegeForm');
    const isEditMode = document.getElementById('isEditMode');
    
    // Form Fields
    const collegeRanking = document.getElementById('collegeRanking');
    const collegeName = document.getElementById('collegeName');
    const collegeContact = document.getElementById('collegeContact');
    const collegeFees = document.getElementById('collegeFees');
    const collegeLocation = document.getElementById('collegeLocation');
    const collegeMaplink = document.getElementById('collegeMaplink');
    
    // Buttons
    const addCollegeBtn = document.getElementById('addCollegeBtn');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    
    // Delete Modal Elements
    const deleteModal = document.getElementById('deleteModal');
    const deleteCollegeName = document.getElementById('deleteCollegeName');
    const closeDeleteModalBtn = document.getElementById('closeDeleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    let collegeToDelete = null;
    
    // Fetch and display colleges
    loadColleges();
    
    // Event Listeners
    addCollegeBtn.addEventListener('click', openAddCollegeModal);
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    collegeForm.addEventListener('submit', saveCollege);
    
    closeDeleteModalBtn.addEventListener('click', closeDeleteModal);
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    confirmDeleteBtn.addEventListener('click', deleteCollege);
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = collegeTable.querySelectorAll('tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Functions
    function logError(message, error) {
        console.error(message, error);
        return `${message}: ${error?.message || 'Unknown error'}`;
    }
    
    function loadColleges() {
        collegeTable.innerHTML = '';
        loadingIndicator.style.display = 'block';
        noDataMessage.style.display = 'none';
        
        // Use the simplified API
        fetch('../api/colleges.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server returned ${response.status} ${response.statusText}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (error) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Invalid JSON response');
                }
            })
            .then(data => {
                loadingIndicator.style.display = 'none';
                
                if (!data || data.length === 0) {
                    noDataMessage.style.display = 'block';
                    return;
                }
                
                // Sort colleges by ranking
                data.sort((a, b) => a.rankings - b.rankings);
                
                // Populate table with college data
                data.forEach(college => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${college.rankings}</td>
                        <td>${college.name}</td>
                        <td>${college.location}</td>
                        <td>${college.contact || '-'}</td>
                        <td>${formatCurrency(college.fees)}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-view" data-id="${college.rankings}" title="View College">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-edit" data-id="${college.rankings}" title="Edit College">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete" data-id="${college.rankings}" data-name="${college.name}" title="Delete College">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    collegeTable.appendChild(row);
                });
                
                // Add event listeners to the action buttons
                document.querySelectorAll('.btn-edit').forEach(btn => {
                    btn.addEventListener('click', () => openEditCollegeModal(btn.getAttribute('data-id')));
                });
                
                document.querySelectorAll('.btn-delete').forEach(btn => {
                    btn.addEventListener('click', () => openDeleteModal(
                        btn.getAttribute('data-id'),
                        btn.getAttribute('data-name')
                    ));
                });
                
                document.querySelectorAll('.btn-view').forEach(btn => {
                    btn.addEventListener('click', () => viewCollege(btn.getAttribute('data-id')));
                });
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                noDataMessage.style.display = 'block';
                noDataMessage.querySelector('p').textContent = `Error loading colleges: ${error.message}. Please try refreshing the page.`;
                console.error('Error:', error);
            });
    }
    
    function openAddCollegeModal() {
        modalTitle.textContent = 'Add New College';
        isEditMode.value = 'false';
        
        // Clear form fields
        collegeForm.reset();
        
        // Show modal
        collegeModal.classList.add('active');
    }
    
    function openEditCollegeModal(id) {
        modalTitle.textContent = 'Edit College';
        isEditMode.value = 'true';
        
        // Fetch college data and populate form
        fetch(`../api/colleges.php?id=${id}`)
            .then(response => response.json())
            .then(college => {
                collegeRanking.value = college.rankings;
                collegeName.value = college.name;
                collegeContact.value = college.contact || '';
                collegeFees.value = college.fees || '';
                collegeLocation.value = college.location;
                collegeMaplink.value = college.maplink || '';
                
                // Disable ranking field in edit mode
                collegeRanking.disabled = true;
                
                // Show modal
                collegeModal.classList.add('active');
            })
            .catch(error => {
                alert('Error fetching college data. Please try again.');
                console.error('Error:', error);
            });
    }
    
    function viewCollege(id) {
        window.open(`../navpages/college-details.html?id=${id}`, '_blank');
    }
    
    function saveCollege(e) {
        e.preventDefault();
        
        // Get form data and ensure proper types
        const formData = {
            rankings: parseInt(collegeRanking.value.trim()),
            name: collegeName.value.trim(),
            location: collegeLocation.value.trim(),
            contact: collegeContact.value.trim() || null,
            fees: collegeFees.value.trim() ? parseFloat(collegeFees.value.trim()) : null,
            maplink: collegeMaplink.value.trim() || null
        };
        
        console.log("Sending data:", formData); // Debug data being sent
        
        // Validate form data
        if (!formData.rankings || !formData.name || !formData.location) {
            alert('Please fill in all required fields.');
            return;
        }
        
        const isEdit = isEditMode.value === 'true';
        const url = '../api/colleges.php';
        const method = isEdit ? 'PUT' : 'POST';
        
        // Send data to server
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Server response:', text);
                    throw new Error(`HTTP error! Status: ${response.status}`);
                });
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Server returned invalid JSON response');
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Close modal and refresh college list
                closeModal();
                loadColleges();
                alert('College saved successfully!');
            } else {
                alert(data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            alert('Error saving college data: ' + error.message);
            console.error('Error:', error);
        });
    }
    
    function openDeleteModal(id, name) {
        collegeToDelete = id;
        deleteCollegeName.textContent = name;
        deleteModal.classList.add('active');
    }
    
    function deleteCollege() {
        if (!collegeToDelete) return;
        
        fetch(`../api/colleges.php?id=${collegeToDelete}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                loadColleges();
            } else {
                alert(data.message || 'An error occurred while deleting the college.');
            }
        })
        .catch(error => {
            alert('Error deleting college. Please try again.');
            console.error('Error:', error);
        });
    }
    
    function closeModal() {
        collegeModal.classList.remove('active');
        collegeRanking.disabled = false;
    }
    
    function closeDeleteModal() {
        deleteModal.classList.remove('active');
        collegeToDelete = null;
    }
    
    function formatCurrency(value) {
        if (!value) return '-';
        
        // Check if value is a number
        const num = parseFloat(value);
        if (isNaN(num)) return '-';
        
        // Format as currency
        if (num >= 100000) {
            return '₹' + (num / 100000).toFixed(2) + ' Lakh';
        } else {
            return '₹' + num.toLocaleString('en-IN');
        }
    }
}); 