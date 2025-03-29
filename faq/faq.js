// Document Ready Function
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation
    window.showMenu = function() {
        document.getElementById('navLinks').classList.add('active');
    }

    window.hideMenu = function() {
        document.getElementById('navLinks').classList.remove('active');
    }

    // Initialize FAQ Categories
    initCategories();
    
    // Initialize FAQ Accordion
    initAccordion();
    
    // Initialize Search Functionality
    initSearch();
    
    // Initialize Search Tags
    initSearchTags();
    
    // Show the general category by default
    document.querySelector('.accordion-group.general').classList.add('active');
    document.querySelector('.category-title#general').classList.add('active');
});

// Initialize FAQ Categories
function initCategories() {
    const categoryItems = document.querySelectorAll('.category-item');
    const categoryTitles = document.querySelectorAll('.category-title');
    const accordionGroups = document.querySelectorAll('.accordion-group');
    
    categoryItems.forEach(item => {
        item.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            
            // Remove active class from all category titles and accordion groups
            categoryTitles.forEach(title => title.classList.remove('active'));
            accordionGroups.forEach(group => group.classList.remove('active'));
            
            // Add active class to selected category title and accordion group
            document.querySelector(`.category-title#${category}`).classList.add('active');
            document.querySelector(`.accordion-group.${category}`).classList.add('active');
            
            // Scroll to the accordion section
            document.querySelector('.category-title.active').scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                inline: 'nearest'
            });
        });
    });
    
    // Category titles click event
    categoryTitles.forEach(title => {
        title.addEventListener('click', function() {
            const categoryId = this.getAttribute('id');
            
            // Check if already active
            if (this.classList.contains('active')) {
                // Toggle the accordion group visibility
                document.querySelector(`.accordion-group.${categoryId}`).classList.toggle('active');
                return;
            }
            
            // Remove active class from all category titles and accordion groups
            categoryTitles.forEach(title => title.classList.remove('active'));
            accordionGroups.forEach(group => group.classList.remove('active'));
            
            // Add active class to selected category title and accordion group
            this.classList.add('active');
            document.querySelector(`.accordion-group.${categoryId}`).classList.add('active');
        });
    });
}

// Initialize FAQ Accordion
function initAccordion() {
    const accordions = document.querySelectorAll('.accordion');
    
    accordions.forEach(accordion => {
        const header = accordion.querySelector('.accordion-header');
        
        header.addEventListener('click', function() {
            // Toggle active class
            accordion.classList.toggle('active');
            
            // Update icon
            const icon = this.querySelector('.accordion-icon i');
            if (accordion.classList.contains('active')) {
                icon.classList.remove('fa-plus');
                icon.classList.add('fa-minus');
            } else {
                icon.classList.remove('fa-minus');
                icon.classList.add('fa-plus');
            }
            
            // Close other accordions in the same group (optional)
            const siblings = accordion.parentElement.querySelectorAll('.accordion');
            siblings.forEach(sibling => {
                if (sibling !== accordion && sibling.classList.contains('active')) {
                    sibling.classList.remove('active');
                    
                    // Update icon for siblings
                    const siblingIcon = sibling.querySelector('.accordion-icon i');
                    siblingIcon.classList.remove('fa-minus');
                    siblingIcon.classList.add('fa-plus');
                }
            });
        });
    });
}

// Initialize Search Functionality
function initSearch() {
    const searchInput = document.getElementById('faqSearch');
    const accordions = document.querySelectorAll('.accordion');
    const categoryTitles = document.querySelectorAll('.category-title');
    const accordionGroups = document.querySelectorAll('.accordion-group');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (searchTerm === '') {
            // If search is empty, show all categories and reset to default view
            categoryTitles.forEach(title => title.style.display = 'flex');
            
            // Hide all accordion groups except the first one
            accordionGroups.forEach((group, index) => {
                if (index === 0) {
                    group.classList.add('active');
                } else {
                    group.classList.remove('active');
                }
            });
            
            // Make all accordions visible and closed
            accordions.forEach(accordion => {
                accordion.style.display = 'block';
                accordion.classList.remove('active');
                
                // Reset icons
                const icon = accordion.querySelector('.accordion-icon i');
                icon.classList.remove('fa-minus');
                icon.classList.add('fa-plus');
            });
            
            // Activate the first category title
            categoryTitles.forEach((title, index) => {
                if (index === 0) {
                    title.classList.add('active');
                } else {
                    title.classList.remove('active');
                }
            });
            
            return;
        }
        
        // Show all accordion groups for searching
        accordionGroups.forEach(group => group.classList.add('active'));
        
        let hasResults = false;
        let visibleAccordions = new Set();
        
        // Search in accordion content
        accordions.forEach(accordion => {
            const header = accordion.querySelector('.accordion-header h3').textContent.toLowerCase();
            const content = accordion.querySelector('.accordion-content p').textContent.toLowerCase();
            
            if (header.includes(searchTerm) || content.includes(searchTerm)) {
                accordion.style.display = 'block';
                accordion.classList.add('active'); // Open matching accordions
                
                // Update icon
                const icon = accordion.querySelector('.accordion-icon i');
                icon.classList.remove('fa-plus');
                icon.classList.add('fa-minus');
                
                hasResults = true;
                
                // Track which categories have visible accordions
                const parentGroup = accordion.closest('.accordion-group');
                visibleAccordions.add(parentGroup.classList[1]); // Add the category class
            } else {
                accordion.style.display = 'none';
            }
        });
        
        // Only show category titles with visible accordions
        categoryTitles.forEach(title => {
            const categoryId = title.getAttribute('id');
            
            if (visibleAccordions.has(categoryId)) {
                title.style.display = 'flex';
                title.classList.add('active');
            } else {
                title.style.display = 'none';
                title.classList.remove('active');
            }
        });
        
        // If no results, show a message
        if (!hasResults) {
            // Could add a "no results" message here
            console.log('No results found for:', searchTerm);
        }
    });
}

// Initialize Search Tags
function initSearchTags() {
    const searchTags = document.querySelectorAll('.search-tag');
    const categoryItems = document.querySelectorAll('.category-item');
    
    searchTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            
            // Remove active class from all tags
            searchTags.forEach(tag => tag.classList.remove('active'));
            
            // Add active class to clicked tag
            this.classList.add('active');
            
            // Find and click the corresponding category item
            categoryItems.forEach(item => {
                if (item.getAttribute('data-category') === filterValue) {
                    item.click();
                    
                    // Scroll to the categories section
                    document.querySelector('.faq-categories').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                        inline: 'nearest'
                    });
                }
            });
        });
    });
}

// Additional animation for floating questions
document.addEventListener('mousemove', function(e) {
    const floatingQuestions = document.querySelectorAll('.floating-question');
    
    floatingQuestions.forEach(question => {
        // Calculate distance from mouse to center of question
        const rect = question.getBoundingClientRect();
        const questionX = rect.left + rect.width / 2;
        const questionY = rect.top + rect.height / 2;
        
        const distanceX = e.clientX - questionX;
        const distanceY = e.clientY - questionY;
        
        // Calculate distance
        const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);
        
        // Apply subtle movement based on mouse position (only if close enough)
        if (distance < 300) {
            const moveX = distanceX * 0.02;
            const moveY = distanceY * 0.02;
            
            question.style.transform = `translate(${-moveX}px, ${-moveY}px)`;
        } else {
            question.style.transform = 'translate(0, 0)';
        }
    });
}); 