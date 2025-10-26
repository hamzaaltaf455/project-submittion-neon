/**
 * Landing Page Interactive Features
 * - Hamburger menu toggle
 * - Mobile menu overlay
 * - Dropdown menu functionality
 * - Sticky header on scroll
 * - Smooth animations
 */

(function() {
    'use strict';

    // =====================================================
    // DOM Elements
    // =====================================================
    const hamburger = document.getElementById('hamburger');
    const mobileNav = document.getElementById('mobileNav');
    const menuOverlay = document.getElementById('menuOverlay');
    const siteHeader = document.getElementById('siteHeader');
    const dropdownTogglesMobile = document.querySelectorAll('.dropdown-toggle-mobile');

    // =====================================================
    // Mobile Menu Toggle
    // =====================================================
    function toggleMobileMenu() {
        const isActive = hamburger.classList.contains('active');
        
        if (isActive) {
            // Close menu
            hamburger.classList.remove('active');
            mobileNav.classList.remove('active');
            menuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        } else {
            // Open menu
            hamburger.classList.add('active');
            mobileNav.classList.add('active');
            menuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    // Hamburger button click
    if (hamburger) {
        hamburger.addEventListener('click', toggleMobileMenu);
    }

    // Overlay click - close menu
    if (menuOverlay) {
        menuOverlay.addEventListener('click', function() {
            if (hamburger.classList.contains('active')) {
                toggleMobileMenu();
            }
        });
    }

    // Close menu when clicking on a mobile menu link
    const mobileMenuLinks = document.querySelectorAll('.mobile-menu a');
    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't close if it's a dropdown toggle
            if (!this.classList.contains('dropdown-toggle-mobile')) {
                setTimeout(() => {
                    if (hamburger.classList.contains('active')) {
                        toggleMobileMenu();
                    }
                }, 300);
            }
        });
    });

    // =====================================================
    // Mobile Dropdown Toggle
    // =====================================================
    dropdownTogglesMobile.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const parent = this.parentElement;
            const isActive = parent.classList.contains('active');
            
            // Close all other dropdowns
            document.querySelectorAll('.has-dropdown-mobile').forEach(item => {
                if (item !== parent) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            if (isActive) {
                parent.classList.remove('active');
            } else {
                parent.classList.add('active');
            }
        });
    });

    // =====================================================
    // Sticky Header on Scroll
    // =====================================================
    let lastScrollTop = 0;
    const headerHeight = siteHeader ? siteHeader.offsetHeight : 0;

    function handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (siteHeader) {
            if (scrollTop > 50) {
                siteHeader.classList.add('scrolled');
            } else {
                siteHeader.classList.remove('scrolled');
            }
        }
        
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }

    // Throttle scroll events for performance
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (!scrollTimeout) {
            scrollTimeout = setTimeout(function() {
                handleScroll();
                scrollTimeout = null;
            }, 10);
        }
    });

    // Initial check
    handleScroll();

    // =====================================================
    // Smooth Scroll for Anchor Links
    // =====================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Skip if href is just "#"
            if (href === '#') {
                e.preventDefault();
                return;
            }
            
            const target = document.querySelector(href);
            
            if (target) {
                e.preventDefault();
                
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset;
                const offsetPosition = targetPosition - headerHeight;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // =====================================================
    // Close Mobile Menu on Escape Key
    // =====================================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && hamburger.classList.contains('active')) {
            toggleMobileMenu();
        }
    });

    // =====================================================
    // Prevent Body Scroll When Mobile Menu is Open
    // =====================================================
    function preventBodyScroll() {
        if (hamburger.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }

    // =====================================================
    // Window Resize Handler
    // =====================================================
    let resizeTimeout;
    window.addEventListener('resize', function() {
        if (!resizeTimeout) {
            resizeTimeout = setTimeout(function() {
                // Close mobile menu on desktop resize
                if (window.innerWidth >= 1024 && hamburger.classList.contains('active')) {
                    toggleMobileMenu();
                }
                resizeTimeout = null;
            }, 250);
        }
    });

    // =====================================================
    // Intersection Observer for Fade-in Animations
    // =====================================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const fadeInObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe feature cards for animation
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `all 0.6s ease ${index * 0.1}s`;
        fadeInObserver.observe(card);
    });

    // Observe testimonial card
    const testimonialCard = document.querySelector('.testimonial-card');
    if (testimonialCard) {
        testimonialCard.style.opacity = '0';
        testimonialCard.style.transform = 'translateY(30px)';
        testimonialCard.style.transition = 'all 0.6s ease';
        fadeInObserver.observe(testimonialCard);
    }

    // =====================================================
    // Dropdown Keyboard Navigation (Desktop)
    // =====================================================
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                
                const parent = this.parentElement;
                const dropdown = parent.querySelector('.dropdown-menu');
                
                if (dropdown) {
                    // Toggle visibility
                    const isVisible = dropdown.style.opacity === '1';
                    
                    if (isVisible) {
                        dropdown.style.opacity = '0';
                        dropdown.style.visibility = 'hidden';
                    } else {
                        dropdown.style.opacity = '1';
                        dropdown.style.visibility = 'visible';
                    }
                }
            }
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdowns = document.querySelectorAll('.has-dropdown');
        
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                const menu = dropdown.querySelector('.dropdown-menu');
                if (menu) {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                }
            }
        });
    });

    // =====================================================
    // Accessibility: Focus Management
    // =====================================================
    const focusableElements = 'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])';
    
    function trapFocus(element) {
        const focusableContent = element.querySelectorAll(focusableElements);
        const firstFocusable = focusableContent[0];
        const lastFocusable = focusableContent[focusableContent.length - 1];
        
        element.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') return;
            
            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    lastFocusable.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    firstFocusable.focus();
                    e.preventDefault();
                }
            }
        });
    }

    // Apply focus trap to mobile menu when open
    if (mobileNav) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    if (mobileNav.classList.contains('active')) {
                        trapFocus(mobileNav);
                    }
                }
            });
        });
        
        observer.observe(mobileNav, { attributes: true });
    }

    // =====================================================
    // Performance: Debounce Function
    // =====================================================
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // =====================================================
    // Log Initialization
    // =====================================================
    console.log('Landing page initialized successfully');
    console.log('Features: Mobile menu, sticky header, smooth scroll, animations, dark mode, keyboard navigation');

    // =====================================================
    // Dark Mode Toggle
    // =====================================================
    const themeToggle = document.getElementById('themeToggle');
    const htmlElement = document.documentElement;
    
    // Check for saved theme preference or default to light mode
    const currentTheme = localStorage.getItem('theme') || 'light';
    htmlElement.setAttribute('data-theme', currentTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Announce to screen readers
            const announcement = newTheme === 'dark' ? 'Dark mode enabled' : 'Light mode enabled';
            announceToScreenReader(announcement);
        });
    }
    
    // =====================================================
    // Keyboard Navigation for Dropdown Menu
    // =====================================================
    const desktopDropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    desktopDropdownToggles.forEach(toggle => {
        const parent = toggle.parentElement;
        const dropdownMenu = parent.querySelector('.dropdown-menu');
        
        if (!dropdownMenu) return;
        
        const dropdownLinks = dropdownMenu.querySelectorAll('a');
        let currentFocusIndex = -1;
        
        // Toggle with Enter or Space
        toggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                parent.classList.toggle('dropdown-open');
                
                if (parent.classList.contains('dropdown-open')) {
                    // Focus first link when opening
                    dropdownLinks[0]?.focus();
                    currentFocusIndex = 0;
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                parent.classList.add('dropdown-open');
                dropdownLinks[0]?.focus();
                currentFocusIndex = 0;
            } else if (e.key === 'Escape') {
                parent.classList.remove('dropdown-open');
                toggle.focus();
            }
        });
        
        // Navigate dropdown with arrow keys
        dropdownLinks.forEach((link, index) => {
            link.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const nextIndex = (index + 1) % dropdownLinks.length;
                    dropdownLinks[nextIndex].focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prevIndex = index === 0 ? dropdownLinks.length - 1 : index - 1;
                    dropdownLinks[prevIndex].focus();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    parent.classList.remove('dropdown-open');
                    toggle.focus();
                } else if (e.key === 'Tab' && !e.shiftKey && index === dropdownLinks.length - 1) {
                    parent.classList.remove('dropdown-open');
                }
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!parent.contains(e.target)) {
                parent.classList.remove('dropdown-open');
            }
        });
        
        // Show dropdown on hover (mouse users)
        parent.addEventListener('mouseenter', function() {
            parent.classList.add('dropdown-open');
        });
        
        parent.addEventListener('mouseleave', function() {
            parent.classList.remove('dropdown-open');
        });
    });
    
    // Update CSS to use .dropdown-open class
    const style = document.createElement('style');
    style.textContent = `
        .has-dropdown.dropdown-open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
    `;
    document.head.appendChild(style);
    
    // =====================================================
    // Enhanced Scroll Animations
    // =====================================================
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                // Stop observing after animation
                animationObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animateElements.forEach(element => {
        animationObserver.observe(element);
    });
    
    // =====================================================
    // Accessibility Helper: Announce to Screen Readers
    // =====================================================
    function announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('role', 'status');
        announcement.setAttribute('aria-live', 'polite');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }
    
    // Add screen reader only styles
    const srStyle = document.createElement('style');
    srStyle.textContent = `
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
            white-space: nowrap;
            border: 0;
        }
    `;
    document.head.appendChild(srStyle);

})();
