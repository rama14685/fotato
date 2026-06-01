import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Testimonial Carousel Auto-Slider Logic
document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('testimonial-slider');
    const prevBtn = document.getElementById('prev-testimonial');
    const nextBtn = document.getElementById('next-testimonial');
    const dots = document.querySelectorAll('#testimonial-dots button');

    if (!slider) return;

    let autoSlideInterval = null;

    // Get width of a card + gap dynamically
    function getScrollAmount() {
        const firstCard = slider.firstElementChild;
        if (firstCard) {
            // width of card + margin/gap
            return firstCard.offsetWidth + 24; 
        }
        return 380; // fallback
    }

    function updateDots() {
        if (!dots.length) return;
        
        // Find which slide is currently in viewport
        const cardWidth = getScrollAmount();
        const activeIndex = Math.min(
            dots.length - 1,
            Math.max(0, Math.round(slider.scrollLeft / cardWidth))
        );

        dots.forEach((dot, idx) => {
            if (idx === activeIndex) {
                dot.classList.remove('bg-white/20');
                dot.classList.add('bg-[#a855f7]');
                dot.style.width = '24px'; // Make active dot wider/pill-shaped for premium UI
            } else {
                dot.classList.remove('bg-[#a855f7]');
                dot.classList.add('bg-white/20');
                dot.style.width = '10px';
            }
        });
    }

    function slideNext() {
        const cardWidth = getScrollAmount();
        const maxScroll = slider.scrollWidth - slider.clientWidth;
        
        if (slider.scrollLeft >= maxScroll - 15) {
            // Loop back to start smoothly
            slider.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            slider.scrollBy({ left: cardWidth, behavior: 'smooth' });
        }
    }

    function slidePrev() {
        const cardWidth = getScrollAmount();
        if (slider.scrollLeft <= 15) {
            // Loop to the end smoothly
            slider.scrollTo({ left: slider.scrollWidth, behavior: 'smooth' });
        } else {
            slider.scrollBy({ left: -cardWidth, behavior: 'smooth' });
        }
    }

    // Button controls
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            slideNext();
            resetAutoSlide();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            slidePrev();
            resetAutoSlide();
        });
    }

    // Dot controls
    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            const cardWidth = getScrollAmount();
            slider.scrollTo({ left: cardWidth * idx, behavior: 'smooth' });
            resetAutoSlide();
        });
    });

    // Update dots on scroll
    let scrollTimeout;
    slider.addEventListener('scroll', () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(updateDots, 60);
    });

    // Auto-slide functions
    function startAutoSlide() {
        autoSlideInterval = setInterval(slideNext, 4500);
    }

    function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
    }

    // Pause on hover
    slider.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
    slider.addEventListener('mouseleave', startAutoSlide);

    // Initial load
    startAutoSlide();
    updateDots();

    // Navbar Scroll Transition Logic
    const navbar = document.getElementById('main-navbar');
    if (navbar) {
        const updateNavbar = () => {
            const threshold = window.innerHeight - 150; 
            if (window.scrollY > threshold) {
                navbar.classList.remove('bg-[#0d061a]/50', 'backdrop-blur-lg', 'border-transparent');
                navbar.classList.add('bg-[#0d061a]', 'border-purple-500/10', 'shadow-lg');
            } else {
                navbar.classList.remove('bg-[#0d061a]', 'border-purple-500/10', 'shadow-lg');
                navbar.classList.add('bg-[#0d061a]/50', 'backdrop-blur-lg', 'border-transparent');
            }
        };

        window.addEventListener('scroll', updateNavbar);
        updateNavbar(); // initial check on load
    }

    // Collage Scroll Fade-In Logic (Staggered appearance in random order)
    const collage = document.getElementById('scroll-collage');
    if (collage) {
        const columns = [
            collage.querySelector('.collage-col-left-far'),
            collage.querySelector('.collage-col-left'),
            collage.querySelector('.collage-col-center'),
            collage.querySelector('.collage-col-right'),
            collage.querySelector('.collage-col-right-far')
        ];

        const observerOptions = {
            root: null,
            threshold: 0.15 // trigger when 15% of the collage is visible
        };

        const observer = new IntersectionObserver((entries, observerInstance) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Generate a shuffled array of delay indices (0 to 4)
                    const delayIndices = [0, 1, 2, 3, 4];
                    for (let i = delayIndices.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [delayIndices[i], delayIndices[j]] = [delayIndices[j], delayIndices[i]];
                    }

                    // Stagger the fade-in of the columns in random order
                    columns.forEach((col, idx) => {
                        if (col) {
                            col.style.transitionDelay = `${delayIndices[idx] * 150}ms`;
                            col.classList.remove('opacity-0', 'translate-y-20');
                            col.classList.add('opacity-100', 'translate-y-0');
                        }
                    });
                    // Unobserve after animating once
                    observerInstance.unobserve(entry.target);
                }
            });
        }, observerOptions);

        observer.observe(collage);
    }
});
