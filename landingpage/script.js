// Navigation Menu Toggle
function showMenu() {
    const navLinks = document.getElementById('navLinks');
    navLinks.classList.add('active');
}

function hideMenu() {
    const navLinks = document.getElementById('navLinks');
    navLinks.classList.remove('active');
}

// Smooth Scrolling for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 100,
                behavior: 'smooth'
            });
            
            // Close mobile menu if open
            hideMenu();
        }
    });
});

// Preloader
window.addEventListener('load', function() {
    const preloader = document.querySelector('.preloader');
    setTimeout(() => {
        preloader.classList.add('preloader-finish');
        setTimeout(() => {
            preloader.style.display = 'none';
        }, 500);
        
        // Initial animations when page loads
        animateHeroSection();
    }, 1000);
});

// Hero Section Animations
function animateHeroSection() {
    // Initialize Particles.js for background effect
    if (typeof particlesJS !== 'undefined' && document.getElementById('particles-js')) {
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: "#4a6cf7" },
                shape: { type: "circle", stroke: { width: 0, color: "#000000" } },
                opacity: { value: 0.5, random: true, anim: { enable: true, speed: 1, opacity_min: 0.1, sync: false } },
                size: { value: 3, random: true, anim: { enable: true, speed: 2, size_min: 0.1, sync: false } },
                line_linked: { enable: true, distance: 150, color: "#4a6cf7", opacity: 0.4, width: 1 },
                move: { enable: true, speed: 1, direction: "none", random: true, straight: false, out_mode: "out", bounce: false }
            },
            interactivity: {
                detect_on: "canvas",
                events: {
                    onhover: { enable: true, mode: "bubble" },
                    onclick: { enable: true, mode: "push" },
                    resize: true
                },
                modes: {
                    grab: { distance: 400, line_linked: { opacity: 1 } },
                    bubble: { distance: 200, size: 6, duration: 2, opacity: 0.8, speed: 3 },
                    repulse: { distance: 200, duration: 0.4 },
                    push: { particles_nb: 4 },
                    remove: { particles_nb: 2 }
                }
            },
            retina_detect: true
        });
    }

    // Initialize 3D scene for hero section
    initHero3DScene();
    
    // Animate overlay cards
    const overlayCards = document.querySelectorAll('.overlay-card');
    overlayCards.forEach(card => {
        const speed = parseFloat(card.getAttribute('data-speed'));
        gsap.to(card, {
            y: Math.random() * 30 - 15,
            duration: 2 + Math.random(),
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut",
            delay: Math.random()
        });
    });
    
    // Animate floating badges
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
        const speed = parseFloat(badge.getAttribute('data-speed'));
        gsap.to(badge, {
            y: -10,
            duration: 1.5 + Math.random(),
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut",
            delay: Math.random()
        });
    });
}

// Initialize Three.js Scene
function initHero3DScene() {
    if (typeof THREE === 'undefined' || !document.getElementById('hero-3d-scene')) return;
    
    const container = document.getElementById('hero-3d-scene');
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    
    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    container.appendChild(renderer.domElement);
    
    // Create a group for all objects
    const group = new THREE.Group();
    scene.add(group);
    
    // Add multiple spheres with different materials
    const sphereGeometry = new THREE.SphereGeometry(0.5, 32, 32);
    
    // Create different materials
    const material1 = new THREE.MeshStandardMaterial({
        color: 0x4a6cf7,
        roughness: 0.3,
        metalness: 0.7
    });
    
    const material2 = new THREE.MeshStandardMaterial({
        color: 0x6941c6,
        roughness: 0.5,
        metalness: 0.5
    });
    
    const material3 = new THREE.MeshStandardMaterial({
        color: 0xff6b6b,
        roughness: 0.2,
        metalness: 0.8
    });
    
    // Create spheres
    const sphere1 = new THREE.Mesh(sphereGeometry, material1);
    sphere1.position.set(-1.5, 0, -1);
    group.add(sphere1);
    
    const sphere2 = new THREE.Mesh(sphereGeometry, material2);
    sphere2.position.set(1.5, 0, -2);
    sphere2.scale.set(1.5, 1.5, 1.5);
    group.add(sphere2);
    
    const sphere3 = new THREE.Mesh(sphereGeometry, material3);
    sphere3.position.set(0, 1.5, -3);
    sphere3.scale.set(0.7, 0.7, 0.7);
    group.add(sphere3);
    
    // Add lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);
    
    const pointLight = new THREE.PointLight(0x4a6cf7, 1);
    pointLight.position.set(2, 3, 4);
    scene.add(pointLight);
    
    const pointLight2 = new THREE.PointLight(0xff6b6b, 1);
    pointLight2.position.set(-2, -3, 4);
    scene.add(pointLight2);
    
    // Position camera
    camera.position.z = 5;
    
    // Mouse movement effect
    let mouseX = 0;
    let mouseY = 0;
    document.addEventListener('mousemove', (event) => {
        mouseX = (event.clientX / window.innerWidth) * 2 - 1;
        mouseY = -(event.clientY / window.innerHeight) * 2 + 1;
    });
    
    // Animation loop
    function animate() {
        requestAnimationFrame(animate);
        
        // Rotate the group based on mouse position
        group.rotation.y += 0.002;
        group.rotation.x += 0.001;
        
        group.rotation.y += (mouseX * 0.01 - group.rotation.y) * 0.05;
        group.rotation.x += (mouseY * 0.01 - group.rotation.x) * 0.05;
        
        // Animate individual spheres
        sphere1.rotation.y += 0.01;
        sphere2.rotation.y += 0.015;
        sphere3.rotation.y += 0.02;
        
        renderer.render(scene, camera);
    }
    
    animate();
    
    // Handle window resize
    window.addEventListener('resize', () => {
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    });
}

// Parallax Effect for Hero Content
document.addEventListener('mousemove', function(e) {
    const parallaxElements = document.querySelectorAll('[data-speed]');
    parallaxElements.forEach(element => {
        const speed = parseFloat(element.getAttribute('data-speed'));
        const x = (window.innerWidth - e.pageX * speed) / 100;
        const y = (window.innerHeight - e.pageY * speed) / 100;
        
        element.style.transform = `translateX(${x}px) translateY(${y}px)`;
    });
});

// Magnetic Buttons
document.querySelectorAll('.magnetic-button').forEach(button => {
    button.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const moveX = (x - centerX) / 10;
        const moveY = (y - centerY) / 10;
        
        this.style.transform = `translate(${moveX}px, ${moveY}px)`;
    });
    
    button.addEventListener('mouseleave', function() {
        this.style.transform = '';
    });
});

// Scroll Reveal Animation
const revealSections = document.querySelectorAll('.reveal-section');
const splitTexts = document.querySelectorAll('.split-text');

function checkReveal() {
    revealSections.forEach(section => {
        const sectionTop = section.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (sectionTop < windowHeight * 0.8) {
            section.classList.add('active');
        }
    });
    
    splitTexts.forEach(text => {
        const textTop = text.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (textTop < windowHeight * 0.8) {
            text.classList.add('active');
        }
    });
}

window.addEventListener('scroll', checkReveal);
window.addEventListener('load', checkReveal);

// Counter Animation
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // ms
                const frameRate = 20; // ms
                const steps = duration / frameRate;
                let currentCount = 0;
                const increment = target / steps;
                
                let interval = setInterval(() => {
                    currentCount += increment;
                    if (currentCount >= target) {
                        counter.textContent = target.toLocaleString() + (target >= 1000 ? '+' : '');
                        clearInterval(interval);
                    } else {
                        counter.textContent = Math.floor(currentCount).toLocaleString();
                    }
                }, frameRate);
                
                // Animate progress bars inside stat items
                const progressFill = counter.parentElement.querySelector('.progress-fill');
                if (progressFill) {
                    progressFill.style.width = '100%';
                }
                
                observer.unobserve(counter);
            }
        });
    }, options);
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
});

// Testimonial Slider
document.addEventListener('DOMContentLoaded', function() {
    const testimonialSlider = document.querySelector('.testimonial-slider');
    const testimonialCards = document.querySelectorAll('.testimonial-card');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const dots = document.querySelectorAll('.dot');
    
    let currentIndex = 0;
    
    // Initial setup
    updateSlider();
    
    // Previous button click
    prevBtn.addEventListener('click', function() {
        currentIndex = Math.max(currentIndex - 1, 0);
        updateSlider();
    });
    
    // Next button click
    nextBtn.addEventListener('click', function() {
        currentIndex = Math.min(currentIndex + 1, testimonialCards.length - 1);
        updateSlider();
    });
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            currentIndex = index;
            updateSlider();
        });
    });
    
    // Update slider position and controls
    function updateSlider() {
        testimonialSlider.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        // Update dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
        
        // Disable/enable navigation buttons
        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex === testimonialCards.length - 1;
        
        // Update button opacity based on state
        prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
        nextBtn.style.opacity = currentIndex === testimonialCards.length - 1 ? '0.5' : '1';
    }
    
    // Auto slide
    let autoSlideInterval = setInterval(() => {
        currentIndex = (currentIndex + 1) % testimonialCards.length;
        updateSlider();
    }, 5000);
    
    // Stop auto slide on manual navigation
    [prevBtn, nextBtn, ...dots].forEach(el => {
        el.addEventListener('click', () => {
            clearInterval(autoSlideInterval);
            
            // Restart after 10 seconds of inactivity
            autoSlideInterval = setInterval(() => {
                currentIndex = (currentIndex + 1) % testimonialCards.length;
                updateSlider();
            }, 5000);
        });
    });
    
    // Update on window resize
    window.addEventListener('resize', updateSlider);
});

// Progress Tracker for How It Works Section
const progressTracker = document.querySelector('.progress-tracker');
if (progressTracker) {
    const progressCircles = document.querySelectorAll('.progress-circle');
    const progressFill = document.querySelector('.progress-line .progress-fill');
    const steps = document.querySelectorAll('.step');
    
    // Update progress on scroll
    function updateProgress() {
        const stepsContainer = document.querySelector('.steps-container');
        const containerTop = stepsContainer.getBoundingClientRect().top;
        const containerHeight = stepsContainer.offsetHeight;
        const containerBottom = containerTop + containerHeight;
        const windowHeight = window.innerHeight;
        
        // Calculate progress percentage
        let progress = 0;
        if (containerTop <= windowHeight * 0.5) {
            progress = Math.min(1, (windowHeight * 0.5 - containerTop) / (containerHeight * 0.8));
        }
        
        // Update progress line
        progressFill.style.width = `${progress * 100}%`;
        
        // Update active circles
        const activeStepIndex = Math.floor(progress * steps.length);
        progressCircles.forEach((circle, index) => {
            circle.classList.toggle('active', index <= activeStepIndex);
        });
    }
    
    window.addEventListener('scroll', updateProgress);
    window.addEventListener('resize', updateProgress);
    
    // Circle click to scroll to step
    progressCircles.forEach((circle, index) => {
        circle.addEventListener('click', () => {
            const targetStep = steps[index];
            window.scrollTo({
                top: targetStep.offsetTop - 200,
                behavior: 'smooth'
            });
        });
    });
}

// Initialize Vanilla Tilt for interactive cards
if (typeof VanillaTilt !== 'undefined') {
    VanillaTilt.init(document.querySelectorAll("[data-tilt]"), {
        max: 15,
        speed: 400,
        glare: true,
        "max-glare": 0.3
    });
}

// Initialize GSAP ScrollTrigger for animations
if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    // Animate feature cards
    gsap.utils.toArray('.feature-card').forEach((card, i) => {
        gsap.from(card, {
            y: 100,
            opacity: 0,
            duration: 1,
            ease: "power3.out",
            scrollTrigger: {
                trigger: card,
                start: "top 80%",
                end: "top 50%",
                toggleActions: "play none none none"
            }
        });
    });
    
    // Animate steps
    gsap.utils.toArray('.step').forEach((step, i) => {
        gsap.from(step, {
            x: i % 2 === 0 ? -100 : 100,
            opacity: 0,
            duration: 1,
            ease: "power3.out",
            scrollTrigger: {
                trigger: step,
                start: "top 80%",
                end: "top 50%",
                toggleActions: "play none none none"
            }
        });
    });
    
    // Animate testimonial cards
    gsap.utils.toArray('.testimonial-card').forEach((card, i) => {
        gsap.from(card, {
            scale: 0.9,
            opacity: 0,
            duration: 1,
            ease: "power3.out",
            scrollTrigger: {
                trigger: card,
                start: "top 80%",
                end: "top 50%",
                toggleActions: "play none none none"
            }
        });
    });
    
    // Animate CTA section
    gsap.from('.cta-content', {
        y: 100,
        opacity: 0,
        duration: 1,
        ease: "power3.out",
        scrollTrigger: {
            trigger: '.cta-section',
            start: "top 80%",
            end: "top 50%",
            toggleActions: "play none none none"
        }
    });
    
    // Animate shapes in CTA section
    gsap.utils.toArray('.shape').forEach((shape, i) => {
        gsap.from(shape, {
            scale: 0,
            opacity: 0,
            duration: 1.5,
            delay: i * 0.2,
            ease: "elastic.out(1, 0.3)",
            scrollTrigger: {
                trigger: '.cta-section',
                start: "top 80%",
                toggleActions: "play none none none"
            }
        });
    });
}

// Fixed Header on Scroll
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.style.padding = '15px 7%';
        navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
        navbar.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
    } else {
        navbar.style.padding = '20px 7%';
        navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
        navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
    }
});

// Form Validation (for search form)
const searchForm = document.querySelector('.search-container');
if (searchForm) {
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const searchInput = this.querySelector('input');
        if (searchInput.value.trim() === '') {
            searchInput.classList.add('error');
            searchInput.focus();
        } else {
            // Process search
            searchInput.classList.remove('error');
            console.log('Searching for:', searchInput.value);
            // Redirect to college search page with query parameter
            window.location.href = '../navpages/college-search.html?q=' + encodeURIComponent(searchInput.value);
        }
    });
}

// Back to top button
const createBackToTopButton = () => {
    const button = document.createElement('button');
    button.innerHTML = '<i class="fas fa-arrow-up"></i>';
    button.className = 'back-to-top';
    button.style.position = 'fixed';
    button.style.bottom = '30px';
    button.style.right = '30px';
    button.style.width = '50px';
    button.style.height = '50px';
    button.style.borderRadius = '50%';
    button.style.backgroundColor = 'var(--primary-color)';
    button.style.color = 'white';
    button.style.border = 'none';
    button.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
    button.style.cursor = 'pointer';
    button.style.display = 'none';
    button.style.zIndex = '999';
    button.style.transition = 'all 0.3s ease';
    document.body.appendChild(button);
    
    button.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.3)';
    });
    
    button.addEventListener('mouseleave', function() {
        this.style.transform = '';
        this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
    });
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            button.style.display = 'block';
            button.style.animation = 'fadeIn 0.5s forwards';
        } else {
            button.style.display = 'none';
        }
    });
    
    button.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
};

createBackToTopButton();

// Add hover effects to feature cards
const featureCards = document.querySelectorAll('.feature-card');
featureCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-15px)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(-10px)';
    });
});

// Lazy loading for images (optional fallback for browsers that don't support native lazy loading)
document.addEventListener('DOMContentLoaded', function() {
    if ('loading' in HTMLImageElement.prototype) {
        // Browser supports native lazy loading
    } else {
        // Fallback for browsers that don't support lazy loading
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        const lazyImageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });
        
        lazyImages.forEach(image => {
            lazyImageObserver.observe(image);
        });
    }
});
