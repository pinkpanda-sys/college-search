// Initialize AOS Animation Library
document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });

    // Mobile Navigation
    window.showMenu = function() {
        document.getElementById('navLinks').classList.add('active');
    }

    window.hideMenu = function() {
        document.getElementById('navLinks').classList.remove('active');
    }

    // Animate Stats Counter
    animateStatCounter();

    // Initialize 3D Timeline Effect
    initTimelineEffect();
    
    // Initialize Value Cards Interaction
    initValueCardsInteraction();
});

// Counter Animation for Stats
function animateStatCounter() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    // Check if elements are in viewport
    const isInViewport = function(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    };

    // Animate counter when in viewport
    const animateCounter = function(element) {
        const target = parseInt(element.getAttribute('data-count'));
        const duration = 2000; // milliseconds
        const step = target / duration * 10; // Update every 10ms
        let current = 0;
        
        element.classList.add('animate');
        
        const timer = setInterval(function() {
            current += step;
            
            if (target < 100) {
                element.textContent = Math.min(Math.round(current * 10) / 10, target);
            } else {
                element.textContent = Math.min(Math.round(current), target);
            }
            
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            }
        }, 10);
    };

    // Start animation for visible counters
    const startCounters = function() {
        statNumbers.forEach(function(statNumber) {
            if (isInViewport(statNumber) && !statNumber.classList.contains('animate')) {
                animateCounter(statNumber);
            }
        });
    };

    // Start animation on scroll
    window.addEventListener('scroll', startCounters);
    
    // Check on initial load
    setTimeout(startCounters, 500);
}

// 3D Timeline Effect
function initTimelineEffect() {
    // Only initialize if the timeline scene element exists
    const timelineScene = document.getElementById('timeline-scene');
    if (!timelineScene) return;

    // Add parallax effect to timeline items
    const timelineItems = document.querySelectorAll('.timeline-item');
    
    const parallaxEffect = function() {
        const scrollPosition = window.pageYOffset;
        
        timelineItems.forEach((item, index) => {
            const speed = index % 2 === 0 ? -0.05 : 0.05;
            const yPosition = scrollPosition * speed;
            
            item.style.transform = `translateY(${yPosition}px)`;
        });
    };
    
    // Apply effect on scroll
    window.addEventListener('scroll', parallaxEffect);
    
    // Initialize floating effect for timeline year bubbles
    const timelineYears = document.querySelectorAll('.timeline-year');
    
    timelineYears.forEach((year, index) => {
        // Create pulsing effect
        year.style.animation = `pulse 2s infinite ease-in-out ${index * 0.3}s`;
    });
}

// Value Cards Interaction
function initValueCardsInteraction() {
    const valueCards = document.querySelectorAll('.value-card');
    
    valueCards.forEach(card => {
        // Create subtle hover effect
        card.addEventListener('mouseenter', function() {
            valueCards.forEach(otherCard => {
                if (otherCard !== card) {
                    otherCard.style.opacity = '0.7';
                    otherCard.style.transform = 'scale(0.98)';
                }
            });
        });
        
        card.addEventListener('mouseleave', function() {
            valueCards.forEach(otherCard => {
                otherCard.style.opacity = '1';
                otherCard.style.transform = '';
            });
        });
        
        // Add click interaction
        card.addEventListener('click', function() {
            this.classList.toggle('expanded');
            
            // If expanded, scroll it into view
            if (this.classList.contains('expanded')) {
                setTimeout(() => {
                    this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
        });
    });
    
    // Add staggered animation to value stats
    const statsNumbers = document.querySelectorAll('.value-stats .stat-number');
    
    const animateValueStats = function() {
        statsNumbers.forEach((statNumber, index) => {
            setTimeout(() => {
                statNumber.style.opacity = '1';
                statNumber.style.transform = 'translateY(0)';
            }, index * 100);
        });
    };
    
    // Animate stats when values section is in viewport
    const valuesSection = document.querySelector('.values-section');
    
    const checkValuesInView = function() {
        const rect = valuesSection.getBoundingClientRect();
        const isInView = 
            rect.top < window.innerHeight &&
            rect.bottom >= 0;
        
        if (isInView) {
            animateValueStats();
            window.removeEventListener('scroll', checkValuesInView);
        }
    };
    
    window.addEventListener('scroll', checkValuesInView);
    setTimeout(checkValuesInView, 500); // Check on initial load
}

// Add pulse animation for the timeline year elements
document.head.insertAdjacentHTML('beforeend', 
`<style>
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 5px 15px rgba(74, 108, 247, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(74, 108, 247, 0.5);
    }
}

.value-stats .stat-number {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.value-card.expanded {
    transform: scale(1.05);
    z-index: 10;
    box-shadow: 0 20px 50px rgba(74, 108, 247, 0.25);
}
</style>`); 