/**
 * Main Application Frontend Script
 *
 * This script initializes all frontend interactivity, including UI enhancements
 * and Bootstrap component functionality. It is structured to be scalable,
 * performant, and safe.
 */

// Encapsulate all logic in a single object to keep the global namespace clean.
const App = {
    /**
     * Main entry point for the application's JavaScript.
     */
    init() {
        // We wrap our initializers in a try...catch block to prevent a single
        // error from breaking all other scripts.
        try {
            this.initHeaderScrollEffect();
            this.initBootstrapComponents();
        } catch (error) {
            console.error('Error during App initialization:', error);
        }
        
        console.log('Application JS initialized.');
    },

    /**
     * Handles the visual effect for the fixed-top header on page scroll.
     *
     * This function adds a 'scrolled' class to the navbar when the user scrolls,
     * allowing CSS to apply styles like a box-shadow. It is optimized for
     * performance using requestAnimationFrame.
     */
    initHeaderScrollEffect() {
        const header = document.querySelector('.navbar.fixed-top');
        if (!header) return; // Exit if no fixed header is found.

        let isTicking = false;

        const updateHeaderState = () => {
            const hasScrolled = window.scrollY > 10;
            header.classList.toggle('scrolled', hasScrolled);
            isTicking = false;
        };

        window.addEventListener('scroll', () => {
            if (!isTicking) {
                window.requestAnimationFrame(updateHeaderState);
                isTicking = true;
            }
        }, { passive: true }); // Use passive listener for better scroll performance.
    },

    /**
     * Initializes all Bootstrap 5 components that require JavaScript activation.
     *
     * While many Bootstrap components work automatically with data-* attributes,
     * some, like Tooltips, must be explicitly initialized.
     */
    initBootstrapComponents() {
        // Example for enabling all tooltips on a page.
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }
};

// Ensure the script runs only after the entire DOM is ready.
document.addEventListener('DOMContentLoaded', () => {
    // Check if the Bootstrap object is available before initializing our app.
    if (typeof bootstrap !== 'undefined') {
        App.init();
    } else {
        console.error('Bootstrap JS not found. Please ensure it is loaded correctly in your footer template.');
    }
});
