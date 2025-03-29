// Navigation Menu Toggle
function showMenu() {
    const navLinks = document.getElementById('navLinks');
    navLinks.classList.add('active');
}

function hideMenu() {
    const navLinks = document.getElementById('navLinks');
    navLinks.classList.remove('active');
}

// Preloader
window.addEventListener('load', function() {
    const preloader = document.querySelector('.preloader');
    setTimeout(() => {
        preloader.classList.add('preloader-finish');
        setTimeout(() => {
            preloader.style.display = 'none';
        }, 500);
        
        // Initial animations when page loads
        animateContent();
    }, 1000);
    
    // Check for URL parameters to see if we need to populate the search
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('q');
    if (searchQuery) {
        document.getElementById('collegeSearch').value = searchQuery;
        // Auto-trigger search
        filterColleges();
    }
});

// Animate content on load
function animateContent() {
    const heroContent = document.querySelector('.hero-content');
    heroContent.classList.add('animate');
    
    // Animate college cards with staggered delay
    const collegeCards = document.querySelectorAll('.college-card');
    collegeCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animate');
        }, 100 * index);
    });
}

// Advanced Filter Toggle
const advFilterToggle = document.getElementById('advancedFilterToggle');
const advFilters = document.getElementById('advancedFilters');

if (advFilterToggle) {
    advFilterToggle.addEventListener('click', function() {
        advFilters.classList.toggle('active');
        this.classList.toggle('active');
    });
}

// Filter buttons functionality
const filterBtns = document.querySelectorAll('.filter-btn');
filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        filterBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.getAttribute('data-filter');
        filterColleges(filter);
    });
});

// College filtering function
function filterColleges(category = 'all') {
    const searchInput = document.getElementById('collegeSearch').value.toLowerCase();
    const collegeCards = document.querySelectorAll('.college-card');
    let visibleCount = 0;
    
    collegeCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category').toLowerCase();
        const collegeName = card.querySelector('h3').textContent.toLowerCase();
        const collegeLocation = card.querySelector('.college-location').textContent.toLowerCase();
        const collegeTags = card.querySelectorAll('.tag');
        
        let tagMatch = false;
        collegeTags.forEach(tag => {
            if (tag.textContent.toLowerCase().includes(searchInput)) {
                tagMatch = true;
            }
        });
        
        // Check if card matches search and category filter
        const matchesSearch = searchInput === '' || 
                             collegeName.includes(searchInput) || 
                             collegeLocation.includes(searchInput) || 
                             tagMatch;
                             
        const matchesCategory = category === 'all' || cardCategory.includes(category);
        
        if (matchesSearch && matchesCategory) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update the results count
    document.getElementById('resultsCount').textContent = visibleCount;
    
    // Show/hide "No results" message
    const noResultsMsg = document.querySelector('.no-results');
    if (noResultsMsg) {
        if (visibleCount === 0) {
            noResultsMsg.style.display = 'block';
        } else {
            noResultsMsg.style.display = 'none';
        }
    }
}

// Search input event listener
const searchInput = document.getElementById('collegeSearch');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        // Get the active filter button
        const activeFilter = document.querySelector('.filter-btn.active');
        const filterValue = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';
        
        filterColleges(filterValue);
    });
    
    // Search form submission
    const searchForm = document.querySelector('.search-box');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Get the active filter button
            const activeFilter = document.querySelector('.filter-btn.active');
            const filterValue = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';
            
            filterColleges(filterValue);
        });
    }
}

// Sorting functionality
const sortSelect = document.getElementById('sortBy');
if (sortSelect) {
    sortSelect.addEventListener('change', function() {
        const sortValue = this.value;
        sortColleges(sortValue);
    });
}

function sortColleges(sortOption) {
    const collegeGrid = document.getElementById('collegeGrid');
    const collegeCards = Array.from(document.querySelectorAll('.college-card'));
    
    collegeCards.sort((a, b) => {
        switch(sortOption) {
            case 'ranking':
                return parseInt(a.getAttribute('data-ranking')) - parseInt(b.getAttribute('data-ranking'));
            case 'tuition-low':
                return parseInt(a.getAttribute('data-fees')) - parseInt(b.getAttribute('data-fees'));
            case 'tuition-high':
                return parseInt(b.getAttribute('data-fees')) - parseInt(a.getAttribute('data-fees'));
            case 'acceptance':
                return parseInt(a.getAttribute('data-acceptance')) - parseInt(b.getAttribute('data-acceptance'));
            default:
                return 0;
        }
    });
    
    // Clear the grid and append sorted cards
    collegeGrid.innerHTML = '';
    collegeCards.forEach(card => {
        collegeGrid.appendChild(card);
    });
}

// Range slider functionality
const tuitionRange = document.getElementById('tuitionRange');
const tuitionMax = document.getElementById('tuitionMax');

if (tuitionRange) {
    tuitionRange.addEventListener('input', function() {
        const value = this.value;
        if (value >= 2500000) {
            tuitionMax.textContent = '₹25,00,000+';
        } else {
            tuitionMax.textContent = '₹' + parseInt(value).toLocaleString('en-IN');
        }
    });
}

// College comparison functionality
const compareButtons = document.querySelectorAll('.btn-compare');
const comparisonModal = document.getElementById('comparisonModal');
const closeModal = document.querySelector('.close-modal');

if (compareButtons.length > 0) {
    compareButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const collegeCard = this.closest('.college-card');
            const collegeName = collegeCard.querySelector('h3').textContent;
            
            // Add college to comparison
            addToComparison(collegeName, collegeCard);
            
            // Show modal
            comparisonModal.classList.add('active');
        });
    });
}

if (closeModal) {
    closeModal.addEventListener('click', function() {
        comparisonModal.classList.remove('active');
    });
}

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    if (e.target === comparisonModal) {
        comparisonModal.classList.remove('active');
    }
});

// Add college to comparison
function addToComparison(collegeName, collegeCard) {
    const select1 = document.getElementById('compareCollege1');
    const select2 = document.getElementById('compareCollege2');
    
    // Check if option already exists
    let exists = false;
    Array.from(select1.options).forEach(option => {
        if (option.text === collegeName) exists = true;
    });
    
    if (!exists) {
        // Add to both dropdowns
        const option1 = new Option(collegeName, collegeName);
        const option2 = new Option(collegeName, collegeName);
        select1.add(option1);
        select2.add(option2);
    }
    
    // Select in first empty dropdown
    if (select1.value === "") {
        select1.value = collegeName;
        updateComparisonData(collegeCard, 1);
    } else if (select2.value === "") {
        select2.value = collegeName;
        updateComparisonData(collegeCard, 2);
    }
}

// Update comparison data
function updateComparisonData(collegeCard, columnNum) {
    const dataContainer = document.getElementById(`compareData${columnNum}`);
    
    if (collegeCard && dataContainer) {
        // Clone college data to show in comparison
        const clonedContent = collegeCard.querySelector('.college-content').cloneNode(true);
        dataContainer.innerHTML = '';
        dataContainer.appendChild(clonedContent);
    }
}

// Dropdowns for comparison
const compareDropdowns = document.querySelectorAll('.college-select');
if (compareDropdowns.length > 0) {
    compareDropdowns.forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            const selectedCollege = this.value;
            const columnNum = this.id.includes('1') ? 1 : 2;
            
            // Find the college card
            const collegeCards = document.querySelectorAll('.college-card');
            collegeCards.forEach(card => {
                const collegeName = card.querySelector('h3').textContent;
                if (collegeName === selectedCollege) {
                    updateComparisonData(card, columnNum);
                }
            });
        });
    });
}

// Apply filters button
const applyFiltersBtn = document.querySelector('.apply-btn');
if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', function() {
        // Get all selected filter values
        const selectedLocations = Array.from(document.querySelectorAll('input[name="location"]:checked')).map(cb => cb.value);
        const selectedPrograms = Array.from(document.querySelectorAll('input[name="program"]:checked')).map(cb => cb.value);
        const selectedTypes = Array.from(document.querySelectorAll('input[name="type"]:checked')).map(cb => cb.value);
        const tuitionValue = document.getElementById('tuitionRange').value;
        
        // Apply advanced filters
        applyAdvancedFilters(selectedLocations, selectedPrograms, selectedTypes, tuitionValue);
        
        // Hide filters panel
        advFilters.classList.remove('active');
        advFilterToggle.classList.remove('active');
    });
}

// Reset filters
const resetFiltersBtn = document.querySelector('.reset-btn');
if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', function() {
        // Reset all checkboxes
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        
        // Reset range slider
        if (tuitionRange) {
            tuitionRange.value = 2500000;
            tuitionMax.textContent = '₹25,00,000+';
        }
        
        // Show all colleges
        document.querySelectorAll('.college-card').forEach(card => {
            card.style.display = 'flex';
        });
        
        // Update count
        document.getElementById('resultsCount').textContent = document.querySelectorAll('.college-card').length;
    });
}

// Apply advanced filters
function applyAdvancedFilters(locations, programs, types, maxTuition) {
    const collegeCards = document.querySelectorAll('.college-card');
    let visibleCount = 0;
    
    collegeCards.forEach(card => {
        // Get card data
        const cardCategory = card.getAttribute('data-category').toLowerCase();
        const cardTuition = parseInt(card.getAttribute('data-fees'));
        
        // Check location filter
        let locationMatch = locations.length === 0; // If no locations selected, all match
        locations.forEach(location => {
            if (cardCategory.includes(location.toLowerCase())) {
                locationMatch = true;
            }
        });
        
        // Check program filter
        let programMatch = programs.length === 0; // If no programs selected, all match
        programs.forEach(program => {
            if (cardCategory.includes(program.toLowerCase())) {
                programMatch = true;
            }
        });
        
        // Check type filter
        let typeMatch = types.length === 0; // If no types selected, all match
        types.forEach(type => {
            if (cardCategory.includes(type.toLowerCase())) {
                typeMatch = true;
            }
        });
        
        // Check tuition filter
        const tuitionMatch = cardTuition <= maxTuition;
        
        // Show/hide card based on all filters
        if (locationMatch && programMatch && typeMatch && tuitionMatch) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update the results count
    document.getElementById('resultsCount').textContent = visibleCount;
} 