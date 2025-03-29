// Document Ready Function
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation
    window.showMenu = function() {
        document.getElementById('navLinks').classList.add('active');
    }

    window.hideMenu = function() {
        document.getElementById('navLinks').classList.remove('active');
    }

    // Initialize Map
    initMap();
    
    // Initialize Form
    initContactForm();
    
    // Initialize FAQ accordion
    initFaqAccordion();
    
    // Initialize animated connection lines
    initConnectionLines();
});

// Initialize Leaflet Map
function initMap() {
    // Create map instance with San Francisco coordinates
    const map = L.map('contactMap').setView([37.7749, -122.4194], 14);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Custom marker icon
    const customIcon = L.icon({
        iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/images/marker-icon.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/images/marker-shadow.png',
        shadowSize: [41, 41]
    });
    
    // Add marker for TheCodex office location
    const marker = L.marker([37.7749, -122.4194], {icon: customIcon}).addTo(map);
    
    // Add popup to marker
    marker.bindPopup(`
        <div style="text-align: center;">
            <strong>TheCodex Headquarters</strong><br>
            123 Education Lane<br>
            San Francisco, CA 94105
        </div>
    `).openPopup();
    
    // Disable zoom when scrolling over map (to prevent page scroll issues)
    map.scrollWheelZoom.disable();
    
    // Enable zoom when the mouse is over the map
    document.getElementById('contactMap').addEventListener('mouseover', function() {
        map.scrollWheelZoom.enable();
    });
    
    // Disable zoom when the mouse leaves the map
    document.getElementById('contactMap').addEventListener('mouseout', function() {
        map.scrollWheelZoom.disable();
    });
}

// Initialize Contact Form with Validation
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    const successMessage = document.getElementById('successMessage');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form fields
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;
            const consent = document.getElementById('consent').checked;
            
            // Simple validation
            if (!name || !email || !subject || !message || !consent) {
                alert('Please fill in all required fields and agree to the privacy policy.');
                return;
            }
            
            // Email validation regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return;
            }
            
            // Phone validation (optional)
            if (phone) {
                const phoneRegex = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
                if (!phoneRegex.test(phone)) {
                    alert('Please enter a valid phone number or leave it blank.');
                    return;
                }
            }
            
            // Simulate form submission with loading state
            const submitBtn = contactForm.querySelector('.submit-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnIcon = submitBtn.querySelector('.btn-icon i');
            
            // Change button to loading state
            btnText.textContent = 'Sending...';
            btnIcon.classList.remove('fa-paper-plane');
            btnIcon.classList.add('fa-spinner');
            btnIcon.classList.add('fa-spin');
            submitBtn.disabled = true;
            
            // Simulate API call delay
            setTimeout(() => {
                // Show success message
                successMessage.classList.add('show');
                
                // Reset form
                contactForm.reset();
                
                // Reset button state
                btnText.textContent = 'Send Message';
                btnIcon.classList.remove('fa-spinner');
                btnIcon.classList.remove('fa-spin');
                btnIcon.classList.add('fa-paper-plane');
                submitBtn.disabled = false;
            }, 1500);
        });
    }
    
    // Close success message
    const closeSuccessBtn = document.querySelector('.close-success');
    if (closeSuccessBtn) {
        closeSuccessBtn.addEventListener('click', function() {
            successMessage.classList.remove('show');
        });
    }
    
    // Form field animations
    const formFields = document.querySelectorAll('.form-field input, .form-field select, .form-field textarea');
    
    formFields.forEach(field => {
        // Add focus class when field is focused
        field.addEventListener('focus', function() {
            this.closest('.form-field').classList.add('focused');
        });
        
        // Remove focus class when field is blurred, unless it has value
        field.addEventListener('blur', function() {
            if (!this.value) {
                this.closest('.form-field').classList.remove('focused');
            }
        });
        
        // Check initial state (for browser autofill)
        if (field.value) {
            field.closest('.form-field').classList.add('focused');
        }
    });
}

// Initialize FAQ Accordion
function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            // Toggle active class
            item.classList.toggle('active');
            
            // Close other FAQ items
            faqItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                }
            });
        });
    });
}

// Initialize Animated Connection Lines
function initConnectionLines() {
    const connectionLines = document.querySelector('.connection-lines');
    
    if (!connectionLines) return;
    
    // Add dynamic connection lines on mouse movement
    document.addEventListener('mousemove', function(e) {
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;
        
        // Adjust background position based on mouse movement
        const offsetX = mouseX * 20; // Adjust multiplier for more/less movement
        const offsetY = mouseY * 20;
        
        connectionLines.style.backgroundPosition = `${offsetX}px ${offsetY}px, ${offsetX * 2}px ${offsetY * 2}px`;
    });
}

// Add parallax effect to info cards
window.addEventListener('scroll', function() {
    const infoCards = document.querySelectorAll('.info-card');
    
    infoCards.forEach((card, index) => {
        const scrollPosition = window.pageYOffset;
        const cardOffset = index * 50; // Staggered offset for each card
        
        if (scrollPosition > 200) {
            const translateY = (scrollPosition - 200) * 0.05 + cardOffset * 0.01;
            card.style.transform = `translateY(-${translateY}px)`;
        } else {
            card.style.transform = 'translateY(0)';
        }
    });
});

// Add hover effect for social cards
document.querySelectorAll('.social-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        // Add subtle rotation
        const randomRotation = (Math.random() * 6) - 3; // Random value between -3 and 3
        this.style.transform = `translateY(-10px) rotate(${randomRotation}deg)`;
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = '';
    });
}); 