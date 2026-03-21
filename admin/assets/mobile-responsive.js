// Mobile responsive functionality
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const header = document.querySelector('.header');
    const footer = document.querySelector('.footer');
    const sidebarOverlay = document.createElement('div');
    
    // Create overlay
    sidebarOverlay.className = 'sidebar-overlay';
    document.body.appendChild(sidebarOverlay);
    
    // Toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
        
        // Close dropdowns when sidebar opens
        if (sidebar.classList.contains('active')) {
            closeAllDropdowns();
        }
    }
    
    // Close sidebar
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
    }
    
    // Toggle profile dropdown
    function toggleProfileDropdown() {
        const profileDropdown = document.querySelector('.profile-dropdown');
        if (profileDropdown) {
            profileDropdown.classList.toggle('active');
        }
    }
    
    // Close all dropdowns
    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
            dropdown.parentElement.classList.remove('active');
        });
    }
    
    // Event Listeners
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleSidebar);
    }
    
    sidebarOverlay.addEventListener('click', closeSidebar);
    
    // Profile dropdown toggle
    const profileTrigger = document.querySelector('.profile-trigger');
    if (profileTrigger) {
        profileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleProfileDropdown();
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.profile-dropdown') && !e.target.closest('.dropdown-menu')) {
            closeAllDropdowns();
        }
        
        // Close sidebar when clicking on main content on mobile
        if (window.innerWidth <= 1024 && !e.target.closest('.sidebar') && !e.target.closest('.menu-toggle')) {
            closeSidebar();
        }
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 1024) {
                closeSidebar();
            }
        }, 250);
    });
    
    // Touch events for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const swipeDistance = touchEndX - touchStartX;
        
        // Swipe right to open sidebar
        if (swipeDistance > swipeThreshold && window.innerWidth <= 1024) {
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
        }
        
        // Swipe left to close sidebar
        if (swipeDistance < -swipeThreshold && window.innerWidth <= 1024) {
            closeSidebar();
        }
    }
    
    // Update active menu item based on current page
    function updateActiveMenuItem() {
        const currentPath = window.location.pathname;
        const menuItems = document.querySelectorAll('.menu-item');
        
        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.includes(href.replace(/^\.\//, ''))) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    // Initialize
    updateActiveMenuItem();
    
    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Escape key closes all dropdowns and sidebar
        if (e.key === 'Escape') {
            closeAllDropdowns();
            closeSidebar();
        }
        
        // Tab key navigation in dropdowns
        if (e.key === 'Tab' && document.activeElement.closest('.dropdown-menu')) {
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            const lastItem = dropdownItems[dropdownItems.length - 1];
            
            if (e.shiftKey && document.activeElement === dropdownItems[0]) {
                closeAllDropdowns();
            } else if (!e.shiftKey && document.activeElement === lastItem) {
                closeAllDropdowns();
            }
        }
    });
    
    // Add loading state for quick actions
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!this.getAttribute('href') || this.getAttribute('href') === '#') {
                e.preventDefault();
                // Add loading animation
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                this.style.pointerEvents = 'none';
                
                // Simulate loading for demo
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.style.pointerEvents = 'auto';
                }, 1000);
            }
        });
    });
    
    // Handle table row selection on mobile
    if (window.innerWidth <= 480) {
        const tableRows = document.querySelectorAll('.data-table tbody tr');
        tableRows.forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function(e) {
                if (!e.target.closest('.table-actions')) {
                    this.classList.toggle('selected');
                }
            });
        });
    }
});