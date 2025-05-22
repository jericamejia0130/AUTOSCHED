<?php
// No need to start session here - it's already started in index.php
?>

<style>
    /* Explicit dark mode styles */
    body.dark-mode {
        background-color: #1f1f1f !important;
        color: #ffffff !important;
    }
    
    body:not(.dark-mode) {
        background-color: #f5f5f5 !important;
        color: #000000 !important;
    }
    
    .collapse a {
        text-indent: 10px;
    }
    nav#sidebar {
        background: #f8f9fa !important; /* Solid light background instead of image */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow-y: auto !important; /* Ensure scrolling works */
        height: calc(100vh - 20px) !important;
        z-index: 1049 !important; /* Ensure proper stacking in mobile */
    }
    .sidebar-logo {
        max-width: 150px; /* Increased from 85px */
        border-radius: 50%;
        border: 2px solid #f8f9fa;
        margin-top: 20px; /* Increased from 15px */
        margin-bottom: 20px; /* Added for better spacing */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 5px; /* Increased from 3px */
        background-color: white; /* White background */
        transition: transform 0.3s ease; /* Smooth transition for hover effect */
    }
    
    .sidebar-logo:hover {
        transform: scale(1.05); /* Slight zoom on hover */
    }
    
    /* Main content container adjustments */
    #view-panel {
        position: relative;
        z-index: 1000; /* Lower than sidebar for proper stacking */
        transition: all 0.3s ease;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        nav#sidebar {
            min-width: 250px;
            max-width: 250px;
            margin-left: -250px;
            transition: margin-left 0.3s;
            position: fixed;
            z-index: 1050;
            height: 100% !important;
            overflow-y: auto !important;
            padding-top: 0 !important; /* Reset padding on mobile */
        }
        
        nav#sidebar.active {
            margin-left: 0;
        }
        
        .sidebar-logo {
            max-width: 120px; /* Increased from 75px */
            border-radius: 50%; /* Keep it circular */
            border: 2px solid #f8f9fa; /* Keep the border */
            margin-top: 15px;
        }
        
        .sidebar-list a {
            font-size: 0.95rem; /* Slightly smaller text for mobile */
            padding: 8px 10px;
        }
        
        #content {
            width: 100%;
            padding: 15px;
            transition: all 0.3s;
        }
        
        #sidebarCollapse {
            display: block;
            position: fixed;
            top: 10px; /* Move up slightly */
            left: 10px;
            z-index: 1060;
            background: transparent; /* Changed to transparent */
            color: var(--light-primary);
            border: none;
            width: 35px; /* Smaller button */
            height: 35px; /* Smaller button */
            text-align: center;
        }
        
        body.sidebar-active {
            overflow: hidden; /* Prevent scrolling when sidebar is open */
        }
        
        .overlay {
            display: none;
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            transition: all 0.5s ease-in-out;
        }
        
        .overlay.active {
            display: block;
            opacity: 1;
        }
    }
    
    /* iPhone SE and small devices */
    @media (max-width: 375px) {
        nav#sidebar {
            min-width: 85%;
            max-width: 85%;
        }
        
        .sidebar-logo {
            max-width: 100px; /* Increased from 65px */
            border-radius: 50%; /* Keep it circular */
            border: 2px solid #f8f9fa; /* Keep the border */
            margin-top: 15px;
        }
        
        .sidebar-list a {
            font-size: 0.9rem;
            padding: 6px 8px;
        }
        
        .sidebar-list .nav-item .icon-field {
            width: 25px;
            display: inline-block;
            text-align: center;
        }
        
        #sidebarCollapse {
            width: 35px;
            height: 35px;
            top: 10px;
            left: 10px;
        }
    }
    
    /* Windows 11 style navbar updates */
    nav#sidebar {
        border-radius: 8px;
        margin: 10px;
        height: calc(100vh - 20px);
    }
    
    .sidebar-list {
        padding-top: 20px; /* Ensure there's space at the top of the sidebar list */
        padding-bottom: 80px !important; /* Ensure bottom items are visible when scrolling */
    }
    
    .sidebar-list a {
        margin: 2px 5px;
        border-radius: 5px;
        transition: all 0.2s;
    }
    
    .sidebar-list a:hover {
        transform: translateY(-2px);
    }
    
    .sidebar-list a.active {
        font-weight: bold;
    }
    
    .sidebar-list a .icon-field {
        width: 30px;
        display: inline-block;
        text-align: center;
    }
    
    /* Logo container with extra space */
    .text-center.py-3 {
        padding-top: 5px !important; /* Reduce top padding */
        padding-bottom: 5px !important; /* Reduce bottom padding */
    }
    
    /* Dark mode compatibility */
    body.dark-mode nav#sidebar {
        background: #333 !important; /* Solid dark background */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    
    body.dark-mode .sidebar-list a {
        color: var(--dark-text) !important;
        border-bottom: none !important;
    }
    
    body.dark-mode .sidebar-list a:hover,
    body.dark-mode .sidebar-list a.active {
        background-color: var(--dark-hover) !important;
        color: var(--dark-primary) !important;
    }
    
    body.dark-mode #sidebarCollapse {
        color: var(--dark-primary) !important;
        background-color: transparent !important;
    }
    
    /* Light mode specific */
    body:not(.dark-mode) .sidebar-list a {
        color: var(--light-text) !important;
        border-bottom: none !important;
    }
    
    body:not(.dark-mode) .sidebar-list a:hover,
    body:not(.dark-mode) .sidebar-list a.active {
        background-color: var(--light-hover) !important;
        color: var(--light-primary) !important;
    }
    
    body:not(.dark-mode) #sidebarCollapse {
        color: var(--light-primary) !important;
        background-color: transparent !important;
    }
</style>

<!-- Mobile Sidebar Toggle Button -->
<button type="button" id="sidebarCollapse" class="d-md-none">
    <i class="fa fa-bars"></i>
</button>

<!-- Overlay for mobile sidebar -->
<div class="overlay"></div>

<nav id="sidebar" class='mx-lt-5 shadow'>
    <div class="sidebar-list">
        <!-- Logo Block -->
        <div class="text-center py-3">
            <img src="assets/uploads/softnet_logo.png" alt="SOFTNET Logo" class="img-fluid sidebar-logo">
        </div>
        <!-- End of Logo Block -->

        <a href="index.php?page=home" class="nav-item nav-home nav-link"><span class='icon-field'><i class="fa fa-home"></i></span> Dashboard</a>
        <a href="index.php?page=users" class="nav-item nav-users nav-link"><span class='icon-field'><i class="fa fa-users"></i></span> Users</a>
        <a href="index.php?page=courses" class="nav-item nav-courses nav-link"><span class='icon-field'><i class="fa fa-list"></i></span> Departments</a>
        <a href="index.php?page=subjects" class="nav-item nav-subjects nav-link"><span class='icon-field'><i class="fa fa-book"></i></span> Department Subjects </a>
        <a href="index.php?page=strand_subjects" class="nav-item nav-strand_subjects nav-link"><span class='icon-field'><i class="fa fa-book"></i></span> Strand Subjects</a>
        <a href="index.php?page=faculty" class="nav-item nav-faculty nav-link"><span class='icon-field'><i class="fa fa-user-tie"></i></span> Teachers Staff</a>
        <a href="index.php?page=schedule" class="nav-item nav-schedule nav-link"><span class='icon-field'><i class="fa fa-calendar-day"></i></span> Schedule</a>
        <a href="index.php?page=rooms" class="nav-item nav-rooms nav-link"><span class='icon-field'><i class="fa fa-door-open"></i></span> Room & Laboratories</a>
        <a href="index.php?page=designation" class="nav-item nav-designation nav-link"><span class='icon-field'><i class="fa fa-id-badge"></i></span> Designation</a>
        <a href="index.php?page=strands" class="nav-item nav-strands nav-link"><span class='icon-field'><i class="fa fa-stream"></i></span> Strand</a>
        <a href="index.php?page=sections" class="nav-item nav-sections nav-link"><span class='icon-field'><i class="fa fa-layer-group"></i></span> Section</a>
        <a href="index.php?page=update_schedule" class="nav-item nav-update_schedule nav-link"><span class='icon-field'><i class="fa fa-calendar"></i></span> View Schedules</a>
        <a href="index.php?page=student_info#preserve-scroll" class="nav-item nav-student_info nav-link" id="student_info_link"><span class='icon-field'><i class="fa fa-users"></i></span> Student Info</a>

        <?php if(isset($_SESSION['login_type']) && ($_SESSION['login_type'] == 'Admin' || $_SESSION['login_type'] == 1)): ?>
        <a href="index.php?page=users" class="nav-item nav-users nav-link">
            <span class='icon-field'><i class="fa fa-users"></i></span> Users
        </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 'Faculty'): ?>
        <a href="view_schedule.php" class="nav-item nav-schedule nav-link">
            <span class='icon-field'><i class="fa fa-calendar-day"></i></span> My Schedule
        </a>
        <?php endif; ?>
        
      
    </div>
</nav>
<script>
    $('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active');
    
    // Toggle sidebar on mobile when button is clicked
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('.overlay').toggleClass('active');
        $('body').toggleClass('sidebar-active');
    });
    
    // Also close sidebar when overlay is clicked
    $('.overlay').on('click', function() {
        $('#sidebar').removeClass('active');
        $('.overlay').removeClass('active');
        $('body').removeClass('sidebar-active');
    });
    
    // Handle dropdown menus in sidebar
    $('.sidebar-list .dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        $(this).next().toggleClass('show');
    });
    
    // Ensure every navigation link closes the sidebar on mobile
    $('.sidebar-list a').on('click', function() {
        if ($(window).width() < 768) {
            // Small delay to ensure the click event completes navigation first
            setTimeout(function() {
                $('#sidebar').removeClass('active');
                $('.overlay').removeClass('active');
                $('body').removeClass('sidebar-active');
            }, 50);
        }
    });
    
    // Ensure sidebar is scrollable
    $('#sidebar').css('overflow-y', 'auto');
    
    // Handle window resize
    $(window).on('resize', function() {
        if ($(window).width() >= 768) {
            $('#sidebar').removeClass('active');
            $('.overlay').removeClass('active');
            $('body').removeClass('sidebar-active');
        }
    });
    
    // Initialize dark mode from preference
    function initDarkMode() {
    if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
        $('#desktop-dark-mode-icon').removeClass('fa-moon').addClass('fa-sun');
        $('#dark-mode-text').text('Light Mode');
        } else {
            document.body.classList.remove('dark-mode');
            $('#desktop-dark-mode-icon').removeClass('fa-sun').addClass('fa-moon');
            $('#dark-mode-text').text('Dark Mode');
        }
    }
    
    // Apply initial dark mode setting
    initDarkMode();
    
    // Remove any existing event listeners (to prevent duplicates)
    $('#desktop-dark-mode-toggle').off('click');
    
    // Desktop dark mode toggle with revised implementation
    $('#desktop-dark-mode-toggle').on('click', function(e) {
        // Stop event propagation to prevent multiple triggers
        e.preventDefault();
        e.stopPropagation();
        
        // Toggle dark mode class
        var isDarkMode = $('body').hasClass('dark-mode');
        
        // Toggle the state (opposite of current)
        if (isDarkMode) {
            $('body').removeClass('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
            $('#desktop-dark-mode-icon').removeClass('fa-sun').addClass('fa-moon');
            $('#dark-mode-text').text('Dark Mode');
            console.log('Dark mode turned OFF');
        } else {
            $('body').addClass('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
            $('#desktop-dark-mode-icon').removeClass('fa-moon').addClass('fa-sun');
            $('#dark-mode-text').text('Light Mode');
            console.log('Dark mode turned ON');
        }
        
        return false; // Prevent default behavior
    });
    
    // Load dark mode on page load with proper delay
    $(document).ready(function() {
        // Apply dark mode class directly based on localStorage
        setTimeout(function() {
            var shouldBeDarkMode = localStorage.getItem('darkMode') === 'enabled';
            if (shouldBeDarkMode) {
                $('body').addClass('dark-mode');
                $('#desktop-dark-mode-icon').removeClass('fa-moon').addClass('fa-sun');
                $('#dark-mode-text').text('Light Mode');
                console.log('Applied dark mode on page load');
            } else {
                $('body').removeClass('dark-mode');
                $('#desktop-dark-mode-icon').removeClass('fa-sun').addClass('fa-moon');
                $('#dark-mode-text').text('Dark Mode');
                console.log('Applied light mode on page load');
            }
        }, 100); // Small delay to ensure DOM is ready
        
        // Add scroll position preservation for all navbar links
        // Get current page from URL
        var currentPage = getPageFromUrl(window.location.href);
        
        // Restore scroll position if available for current page
        if (window.location.hash === '#preserve-scroll' && currentPage) {
            var savedScrollPos = localStorage.getItem(currentPage + '_scroll_pos');
            if (savedScrollPos) {
                setTimeout(function() {
                    window.scrollTo(0, parseInt(savedScrollPos));
                    
                    // Highlight the current nav item
                    var navItem = $('.nav-item.nav-' + currentPage);
                    if (navItem.length) {
                        // Add a temporary highlight effect
                        navItem.addClass('nav-highlight');
                        setTimeout(function() {
                            navItem.removeClass('nav-highlight');
                        }, 1000);
                        
                        // Scroll the sidebar to make the nav item visible
                        var sidebarContainer = $('#sidebar');
                        var itemOffset = navItem.offset().top;
                        var sidebarOffset = sidebarContainer.offset().top;
                        var sidebarScrollTop = sidebarContainer.scrollTop();
                        
                        // Calculate position to ensure item is visible in the middle of sidebar
                        var newScrollTop = sidebarScrollTop + (itemOffset - sidebarOffset) - (sidebarContainer.height() / 2);
                        sidebarContainer.animate({scrollTop: newScrollTop}, 300);
                    }
                }, 100);
            }
            
            // Remove the hash to avoid repeated scrolling
            if (history.pushState) {
                history.pushState(null, null, window.location.pathname + window.location.search);
            }
        }
        
        // Add click handler to save scroll position for all navbar links
        $('.sidebar-list a.nav-link').each(function() {
            var originalHref = $(this).attr('href');
            var page = getPageFromUrl(originalHref);
            
            // Update href to include hash
            if (originalHref.indexOf('#') === -1) {
                $(this).attr('href', originalHref + '#preserve-scroll');
            }
            
            // Add click handler to save current scroll position
            $(this).on('click', function() {
                // Save current scroll position for the current page
                var currentPage = getPageFromUrl(window.location.href);
                if (currentPage) {
                    localStorage.setItem(currentPage + '_scroll_pos', window.scrollY);
                }
            });
        });
        
        // Helper function to extract page name from URL
        function getPageFromUrl(url) {
            var match = url.match(/page=([^&]*)/);
            return match ? match[1] : 'home';
        }
        
        // Save scroll position periodically for current page
        var currentPage = getPageFromUrl(window.location.href);
        if (currentPage) {
            setInterval(function() {
                localStorage.setItem(currentPage + '_scroll_pos', window.scrollY);
            }, 1000);
        }
        
        // Add CSS for highlight effect
        $('<style>')
            .prop('type', 'text/css')
            .html(`
            .nav-highlight {
                background-color: rgba(0, 123, 255, 0.2) !important;
                box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
                transition: all 0.5s ease;
            }
            .nav-item.active {
                border-left: 3px solid #007bff !important;
                font-weight: bold !important;
            }
            .sidebar-list a.nav-link:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            `)
            .appendTo('head');
    });
</script>
