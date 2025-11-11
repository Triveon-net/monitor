// Animation Handler for LabservNET
class AnimationManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupPageLoader();
        this.setupScrollReveal();
        this.setupHoverEffects();
        this.setupStaggeredAnimations();
    }

    // Page Loader
    setupPageLoader() {
        // Show loader immediately
        this.showPageLoader();

        // Hide loader when page is fully loaded
        window.addEventListener('load', () => {
            setTimeout(() => {
                this.hidePageLoader();
            }, 500); // Small delay for smooth transition
        });

        // Fallback: hide after 3 seconds if load event doesn't fire
        setTimeout(() => {
            this.hidePageLoader();
        }, 3000);
    }

    showPageLoader() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.classList.remove('hidden');
        }
    }

    hidePageLoader() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.classList.add('hidden');
            // Remove from DOM after animation
            setTimeout(() => {
                if (loader.parentNode) {
                    loader.parentNode.removeChild(loader);
                }
            }, 500);
        }
    }

    // Scroll Reveal Animation
    setupScrollReveal() {
        const revealElements = document.querySelectorAll('.scroll-reveal');
        
        if (revealElements.length === 0) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    // Optional: stop observing after reveal
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(el => observer.observe(el));
    }

    // Hover Effects
    setupHoverEffects() {
        // Add ripple effect to buttons
        document.querySelectorAll('.btn-ripple').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple-effect');

                this.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });
    }

    // Staggered Animations for Grids/Lists
    setupStaggeredAnimations() {
        const containers = document.querySelectorAll('[data-stagger]');
        
        containers.forEach(container => {
            const children = container.children;
            Array.from(children).forEach((child, index) => {
                child.classList.add('stagger-item');
                child.style.animationDelay = `${index * 0.1}s`;
            });
        });
    }

    // Animate Element (programmatic animation)
    animate(element, animationClass, callback) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;

        element.classList.add(animationClass);
        
        const handleAnimationEnd = () => {
            element.removeEventListener('animationend', handleAnimationEnd);
            if (callback) callback();
        };
        
        element.addEventListener('animationend', handleAnimationEnd);
    }

    // Fade In Element
    fadeIn(element, duration = 600) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;

        element.style.opacity = '0';
        element.style.display = 'block';
        element.style.transition = `opacity ${duration}ms ease-in-out`;
        
        setTimeout(() => {
            element.style.opacity = '1';
        }, 10);
    }

    // Fade Out Element
    fadeOut(element, duration = 600, callback) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;

        element.style.opacity = '1';
        element.style.transition = `opacity ${duration}ms ease-in-out`;
        
        setTimeout(() => {
            element.style.opacity = '0';
            setTimeout(() => {
                element.style.display = 'none';
                if (callback) callback();
            }, duration);
        }, 10);
    }

    // Slide Toggle
    slideToggle(element, duration = 400) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;

        if (element.style.display === 'none' || !element.style.display) {
            this.slideDown(element, duration);
        } else {
            this.slideUp(element, duration);
        }
    }

    slideDown(element, duration = 400) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;

        element.style.removeProperty('display');
        let display = window.getComputedStyle(element).display;
        if (display === 'none') display = 'block';
        element.style.display = display;
        
        const height = element.offsetHeight;
        element.style.overflow = 'hidden';
        element.style.height = 0;
        element.style.paddingTop = 0;
        element.style.paddingBottom = 0;
        element.style.marginTop = 0;
        element.style.marginBottom = 0;
        element.offsetHeight; // Force repaint
        element.style.transition = `height ${duration}ms ease, padding ${duration}ms ease, margin ${duration}ms ease`;
        element.style.height = height + 'px';
        element.style.removeProperty('padding-top');
        element.style.removeProperty('padding-bottom');
        element.style.removeProperty('margin-top');
        element.style.removeProperty('margin-bottom');
        
        setTimeout(() => {
            element.style.removeProperty('height');
            element.style.removeProperty('overflow');
            element.style.removeProperty('transition');
        }, duration);
    }

    slideUp(element, duration = 400) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;

        element.style.height = element.offsetHeight + 'px';
        element.offsetHeight; // Force repaint
        element.style.transition = `height ${duration}ms ease, padding ${duration}ms ease, margin ${duration}ms ease`;
        element.style.overflow = 'hidden';
        element.style.height = 0;
        element.style.paddingTop = 0;
        element.style.paddingBottom = 0;
        element.style.marginTop = 0;
        element.style.marginBottom = 0;
        
        setTimeout(() => {
            element.style.display = 'none';
            element.style.removeProperty('height');
            element.style.removeProperty('padding-top');
            element.style.removeProperty('padding-bottom');
            element.style.removeProperty('margin-top');
            element.style.removeProperty('margin-bottom');
            element.style.removeProperty('overflow');
            element.style.removeProperty('transition');
        }, duration);
    }

    // Show Notification
    showNotification(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} notification-enter`;
        notification.textContent = message;
        
        // Style
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.padding = '15px 20px';
        notification.style.borderRadius = '8px';
        notification.style.zIndex = '10000';
        notification.style.minWidth = '250px';
        notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        
        // Colors based on type
        const colors = {
            info: { bg: '#3b82f6', text: '#fff' },
            success: { bg: '#10b981', text: '#fff' },
            warning: { bg: '#f59e0b', text: '#fff' },
            error: { bg: '#ef4444', text: '#fff' }
        };
        
        const color = colors[type] || colors.info;
        notification.style.backgroundColor = color.bg;
        notification.style.color = color.text;
        
        document.body.appendChild(notification);
        
        // Auto remove
        setTimeout(() => {
            notification.classList.remove('notification-enter');
            notification.classList.add('notification-exit');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    // Counter Animation
    animateCounter(element, start, end, duration = 2000) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;

        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = Math.round(current);
        }, 16);
    }

    // Progress Bar Animation
    animateProgress(element, percentage, duration = 1000) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;

        element.style.width = '0%';
        element.style.transition = `width ${duration}ms ease-out`;
        
        setTimeout(() => {
            element.style.width = percentage + '%';
        }, 10);
    }

    // Shake Animation (for errors)
    shake(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;

        element.style.animation = 'shake 0.5s';
        
        setTimeout(() => {
            element.style.animation = '';
        }, 500);
    }
}

// Shake keyframes (add to head if not in CSS)
const shakeKeyframes = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
        20%, 40%, 60%, 80% { transform: translateX(10px); }
    }
`;

// Add shake keyframes if not present
if (!document.querySelector('#shake-keyframes')) {
    const style = document.createElement('style');
    style.id = 'shake-keyframes';
    style.textContent = shakeKeyframes;
    document.head.appendChild(style);
}

// Initialize Animation Manager
const animManager = new AnimationManager();

// Export for use in other scripts
if (typeof window !== 'undefined') {
    window.AnimationManager = AnimationManager;
    window.animManager = animManager;
}
