/**
 * Common JavaScript for sidebar functionality
 * Fixed version to avoid blinking/refreshing issues
 */
document.addEventListener("DOMContentLoaded", function() {
    console.log("Common JS loaded - Fixed navigation");
    
    // First, ensure we're dealing with the sidebar navigation
    if (!document.querySelector(".sidebar .nav")) {
        return; // Exit if we're not on a page with sidebar
    }
    
    // Get current page
    const currentPage = window.location.pathname.split("/").pop().toLowerCase();
    console.log("Current page:", currentPage);
    
    // Make sure all menu items are fully clickable
    const navItems = document.querySelectorAll(".sidebar .nav li");
    navItems.forEach(item => {
        // Make the entire li element clickable
        item.style.position = 'relative'; // Ensure the li has position relative
        
        // If the item doesn't have a direct link inside (just in case)
        if (!item.querySelector('a')) {
            item.addEventListener('click', function(e) {
                // Get the destination from the text content
                const destination = this.textContent.trim().toLowerCase().replace(/\s+/g, '') + ".html";
                // Don't navigate if it's the active item
                if (!this.classList.contains('active')) {
                    window.location.href = destination;
                }
                e.stopPropagation(); // Prevent event bubbling
            });
        } else {
            // For items with anchor tags, make sure the whole li is clickable
            if (!item.classList.contains('active')) {
                item.addEventListener('click', function(e) {
                    // Always trigger the anchor link when clicking anywhere on the li
                    const anchor = this.querySelector('a');
                    if (anchor && anchor.href) {
                        window.location.href = anchor.href;
                    }
                    e.stopPropagation(); // Prevent event bubbling
                });
            }
        }
        
        // Create an invisible overlay to ensure edge clicks work
        const overlay = document.createElement('div');
        overlay.style.position = 'absolute';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.zIndex = '1';
        
        // Don't add overlay to active items
        if (!item.classList.contains('active')) {
            const anchor = item.querySelector('a');
            if (anchor && anchor.href) {
                overlay.addEventListener('click', function(e) {
                    window.location.href = anchor.href;
                    e.stopPropagation();
                });
                item.appendChild(overlay);
            }
        }
    });
    
    // Handle logout button separately
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        // Remove existing event handlers
        const newLogoutBtn = logoutBtn.cloneNode(true);
        logoutBtn.parentNode.replaceChild(newLogoutBtn, logoutBtn);
        
        // Add new event handler
        newLogoutBtn.addEventListener("click", function(e) {
            e.preventDefault();
            if (confirm("Are you sure you want to logout?")) {
                // Use logout function from auth.js if available
                if (typeof logout === "function") {
                    logout();
                } else {
                    // Fallback if auth.js is not included
                    localStorage.clear();
                    window.location.href = "login.html";
                }
            }
            return false;
        });
    }
});
