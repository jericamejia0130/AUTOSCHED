<style>
    /* Critical fix for text visibility in navbar - always force text to be visible */
    body:not(.dark-mode) .navbar.bg-primary,
    body:not(.dark-mode) .navbar.navbar-light.fixed-top.bg-primary {
        background-color: #ffffff !important;
    }
    
    body:not(.dark-mode) .navbar.bg-primary a:not(.btn):not(.badge),
    body:not(.dark-mode) .navbar.bg-primary .app-title,
    body:not(.dark-mode) .navbar.bg-primary .user-name,
    body:not(.dark-mode) .navbar.bg-primary .dropdown-toggle {
        color: #444444 !important;
    }
    
    body:not(.dark-mode) .navbar.bg-primary a:hover:not(.btn):not(.badge) {
        color: #0078d7 !important;
    }
    
    /* Original styles */
    .logo {
        margin: auto;
        font-size: 20px;
        background: white;
        padding: 7px 11px;
        border-radius: 50%;
        color: #000000b3;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border: 2px solid #f8f9fa;
    }
    
    .topbar-logo {
        height: 36px;
        width: 36px;
        border-radius: 50%;
        border: 2px solid #f8f9fa;
        margin-right: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 2px;
        background-color: white;
        object-fit: contain;
        display: inline-block;
        vertical-align: middle;
    }
    
    /* Windows 11 style topbar */
    .navbar {
        display: flex;
        align-items: center;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
    
    .navbar.bg-primary {
        background-color: #ffffff !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        height: 60px;
        z-index: 1050; /* Ensure topbar is above sidebar */
        display: flex;
        align-items: center;
    }
    
    .container-fluid {
        display: flex;
        align-items: center;
        width: 100%;
    }
    
    .app-title {
        font-size: 20px;
        font-weight: 600;
        color: #444444;
        transition: all 0.3s;
        text-decoration: none !important;
        display: flex;
        align-items: center;
    }
    
    .app-title:hover {
        color: #0078d7 !important;
        transform: scale(1.05);
    }
    
    /* Windows 11 style search */
    .search-form {
        display: flex;
        align-items: center;
        margin-left: 20px;
        position: relative;
    }
    
    .search-form input {
        border-radius: 20px;
        padding: 8px 15px 8px 35px;
        border: 1px solid #e0e0e0;
        outline: none;
        background-color: #f5f5f5;
        transition: all 0.3s;
        color: #444444;
        width: 200px;
        height: 38px; /* Fixed height for alignment */
    }
    
    .search-form .search-icon {
        position: absolute;
        left: 12px;
        color: #777;
        pointer-events: none;
    }
    
    .search-form input:focus {
        box-shadow: 0 0 0 2px #0078d7;
        width: 250px;
    }
    
    .search-form button {
        position: absolute;
        right: 5px;
        background: transparent;
        border: none;
        color: #0078d7;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .search-form input:focus + .search-icon + button {
        opacity: 1;
    }
    
    .search-shortcut {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background-color: #f0f0f0;
        color: #777;
        padding: 1px 6px;
        border-radius: 4px;
        font-size: 11px;
        pointer-events: none;
        opacity: 0.7;
    }
    
    .search-form input:focus + .search-icon + button + .search-shortcut {
        display: none;
    }
    
    /* User dropdown - UPDATED */
    .user-dropdown {
        background-color: transparent;
        border-radius: 20px;
        padding: 6px 15px; /* Adjusted padding for height */
        transition: background-color 0.3s;
        display: inline-flex;
        align-items: center;
        height: 38px; /* Match search input height */
        margin-right: 5px;
        line-height: 1.2; /* Adjusted line height */
        vertical-align: middle; /* Ensure vertical alignment */
        position: relative;
        top: 0; /* Align at the top */
    }
    
    .user-dropdown:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .user-name {
        color: #444444;
        font-weight: 500;
        line-height: 1; /* Adjusted line height for better alignment */
        display: inline-flex; /* Help with alignment */
        align-items: center;
        margin: 0; /* Remove any margin */
    }
    
    /* Dark mode styles for topbar */
    body.dark-mode .navbar.bg-primary {
        background-color: #1f1f1f !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    body.dark-mode .app-title {
        color: #ffffff;
    }
    
    body.dark-mode .app-title:hover {
        color: #3a96ff !important;
    }
    
    body.dark-mode .search-form input {
        background-color: #2d2d2d;
        border-color: #444444;
        color: #ffffff;
    }
    
    body.dark-mode .search-shortcut {
        background-color: #444;
        color: #aaa;
    }
    
    body.dark-mode .search-form .search-icon {
        color: #aaa;
    }
    
    /* Mobile responsive styles */
    @media (max-width: 768px) {
        .navbar.bg-primary {
            height: 55px; /* Slightly shorter on mobile */
        }
        
        .app-title {
            font-size: 18px;
            width: auto;
            margin-left: 40px; /* Make space for sidebar toggle button */
        }
        
        .topbar-logo {
            height: 32px;
            width: 32px;
        }
        
        .search-form {
            margin-left: 10px;
            max-width: 150px;
        }
        
        .search-form input {
            width: 100%;
            padding: 5px 10px;
            font-size: 14px;
        }
        
        .search-form button {
            padding: 5px 10px;
        }
        
        #menu-toggle {
            display: none; /* Hide menu toggle since we have the sidebar toggle */
        }
        
        .user-name {
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    }
    
    /* For very small screens */
    @media (max-width: 480px) {
        .search-form {
            display: none; /* Hide search on very small screens */
        }
        
        .app-title {
            font-size: 16px;
            margin-left: 35px; /* Adjust margin for very small screens */
        }
        
        .topbar-logo {
            height: 28px;
            width: 28px;
        }
    }
    
    /* iPhone SE specific adjustments */
    @media (max-width: 375px) {
        .navbar.bg-primary {
            height: 50px; /* Even shorter for very small screens */
        }
        
        .app-title {
            font-size: 14px !important;
            max-width: 120px;
            margin-left: 30px; /* Adjust margin for iPhone SE */
        }
        
        .topbar-logo {
            height: 24px;
            width: 24px;
        }
        
        .user-name {
            max-width: 90px;
            font-size: 13px;
        }
        
        .mobile-search-drawer .form-control {
            font-size: 14px;
        }
        
        .mobile-search-drawer .btn {
            padding: 0.25rem 0.5rem;
        }
    }
    
    /* Mobile search drawer - Windows 11 style */
    .mobile-search-drawer {
        background-color: var(--light-card, #ffffff);
        color: var(--light-text, #444444);
        border-top: 1px solid var(--light-border, #e0e0e0);
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.1);
        padding: 20px;
        z-index: 1050;
    }
    
    .mobile-search-drawer .form-group {
        position: relative;
        margin-bottom: 0;
    }
    
    .mobile-search-drawer .search-icon {
        position: absolute;
        left: 15px;
        top: 12px;
        color: #777;
        z-index: 10;
    }
    
    .mobile-search-drawer .form-control {
        border-radius: 20px;
        border-color: var(--light-border, #e0e0e0);
        background-color: var(--light-input, #ffffff);
        padding-left: 40px;
        height: 45px;
        font-size: 16px;
    }
    
    .mobile-search-drawer .search-actions {
        display: flex;
        margin-top: 15px;
    }
    
    .mobile-search-drawer .btn {
        flex: 1;
        border-radius: 20px;
        margin: 0 5px;
    }
    
    body.dark-mode .mobile-search-drawer {
        background-color: var(--dark-card);
        color: var(--dark-text);
        border-top-color: var(--dark-border);
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3);
    }
    
    body.dark-mode .mobile-search-drawer .form-control {
        background-color: var(--dark-input);
        color: var(--dark-text);
        border-color: var(--dark-border);
    }
    
    body.dark-mode .mobile-search-drawer .search-icon {
        color: #aaa;
    }
    
    /* Mobile action buttons */
    #mobile-search-btn,
    .mobile-scroll-top,
    .dark-mode-toggle {
        background-color: var(--light-primary, #0078d7);
    }
    
    body.dark-mode #mobile-search-btn,
    body.dark-mode .mobile-scroll-top,
    body.dark-mode .dark-mode-toggle {
        background-color: var(--dark-primary);
    }
    
    /* Dark mode toggle button - UPDATED */
    .dark-mode-toggle-btn {
        background: transparent;
        border: none;
        border-radius: 20px;
        width: 38px;
        height: 38px; /* Match search input height */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-left: 10px;
        margin-right: 8px;
        padding: 0; /* Remove padding */
        position: relative;
        top: 0; /* Align at the top */
    }
    
    .dark-mode-toggle-btn:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    body.dark-mode .dark-mode-toggle-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .dark-mode-toggle-btn i {
        font-size: 18px;
        color: var(--light-text, #444444);
    }
    
    body.dark-mode .dark-mode-toggle-btn i {
        color: var(--dark-text, #ffffff);
    }
    
    /* Updated topbar controls positioning */
    .topbar-controls {
        display: flex;
        align-items: center;
        height: 38px;
        margin-left: auto; /* Push to the far right */
        padding: 0;
    }

    .search-form {
        margin-left: 20px; /* Reset to original margin */
        margin-right: 0; /* Remove right margin */
    }

    /* Mobile responsive styles */
    @media (max-width: 768px) {
        .navbar.bg-primary {
            height: 55px; /* Slightly shorter on mobile */
        }
        
        .app-title {
            font-size: 18px;
            width: auto;
            margin-left: 40px; /* Make space for sidebar toggle button */
        }
        
        .topbar-logo {
            height: 32px;
            width: 32px;
        }
        
        .search-form {
            margin-left: 10px;
            max-width: 150px;
        }
        
        .search-form input {
            width: 100%;
            padding: 5px 10px;
            font-size: 14px;
        }
        
        .search-form button {
            padding: 5px 10px;
        }
        
        #menu-toggle {
            display: none; /* Hide menu toggle since we have the sidebar toggle */
        }
        
        .user-name {
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    }
    
    /* For very small screens */
    @media (max-width: 480px) {
        .search-form {
            display: none; /* Hide search on very small screens */
        }
        
        .app-title {
            font-size: 16px;
            margin-left: 35px; /* Adjust margin for very small screens */
        }
        
        .topbar-logo {
            height: 28px;
            width: 28px;
        }
    }
    
    /* iPhone SE specific adjustments */
    @media (max-width: 375px) {
        .navbar.bg-primary {
            height: 50px; /* Even shorter for very small screens */
        }
        
        .app-title {
            font-size: 14px !important;
            max-width: 120px;
            margin-left: 30px; /* Adjust margin for iPhone SE */
        }
        
        .topbar-logo {
            height: 24px;
            width: 24px;
        }
        
        .user-name {
            max-width: 90px;
            font-size: 13px;
        }
        
        .mobile-search-drawer .form-control {
            font-size: 14px;
        }
        
        .mobile-search-drawer .btn {
            padding: 0.25rem 0.5rem;
        }
    }
    
    /* Mobile search drawer - Windows 11 style */
    .mobile-search-drawer {
        background-color: var(--light-card, #ffffff);
        color: var(--light-text, #444444);
        border-top: 1px solid var(--light-border, #e0e0e0);
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.1);
        padding: 20px;
        z-index: 1050;
    }
    
    .mobile-search-drawer .form-group {
        position: relative;
        margin-bottom: 0;
    }
    
    .mobile-search-drawer .search-icon {
        position: absolute;
        left: 15px;
        top: 12px;
        color: #777;
        z-index: 10;
    }
    
    .mobile-search-drawer .form-control {
        border-radius: 20px;
        border-color: var(--light-border, #e0e0e0);
        background-color: var(--light-input, #ffffff);
        padding-left: 40px;
        height: 45px;
        font-size: 16px;
    }
    
    .mobile-search-drawer .search-actions {
        display: flex;
        margin-top: 15px;
    }
    
    .mobile-search-drawer .btn {
        flex: 1;
        border-radius: 20px;
        margin: 0 5px;
    }
    
    body.dark-mode .mobile-search-drawer {
        background-color: var(--dark-card);
        color: var(--dark-text);
        border-top-color: var(--dark-border);
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3);
    }
    
    body.dark-mode .mobile-search-drawer .form-control {
        background-color: var(--dark-input);
        color: var(--dark-text);
        border-color: var(--dark-border);
    }
    
    body.dark-mode .mobile-search-drawer .search-icon {
        color: #aaa;
    }
    
    /* Mobile action buttons */
    #mobile-search-btn,
    .mobile-scroll-top,
    .dark-mode-toggle {
        background-color: var(--light-primary, #0078d7);
    }
    
    body.dark-mode #mobile-search-btn,
    body.dark-mode .mobile-scroll-top,
    body.dark-mode .dark-mode-toggle {
        background-color: var(--dark-primary);
    }
    
    /* Dark mode toggle button - UPDATED */
    .dark-mode-toggle-btn {
        background: transparent;
        border: none;
        border-radius: 20px;
        width: 38px;
        height: 38px; /* Match search input height */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-left: 10px;
        margin-right: 8px;
        padding: 0; /* Remove padding */
        position: relative;
        top: 0; /* Align at the top */
    }
    
    .dark-mode-toggle-btn:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    body.dark-mode .dark-mode-toggle-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .dark-mode-toggle-btn i {
        font-size: 18px;
        color: var(--light-text, #444444);
    }
    
    body.dark-mode .dark-mode-toggle-btn i {
        color: var(--dark-text, #ffffff);
    }
    
    /* New - Header items alignment fix */
    .topbar-controls {
        display: flex;
        align-items: center;
        height: 38px;
        margin-left: auto;  /* Push to the right */
        padding: 0;
    }
    
    /* Additional top-level style rules for better alignment */
    .navbar-light .navbar-nav .nav-link,
    .navbar .dropdown-toggle,
    .navbar .btn,
    .navbar .form-control {
        display: flex;
        align-items: center;
    }
    
    /* Float right container adjustment */
    .navbar .float-right {
        display: flex;
        align-items: center;
        height: 100%;
    }
    
    /* New user button styling */
    .btn-new-user {
        height: 38px;
        display: flex;
        align-items: center;
        margin-left: 8px;
        border-radius: 20px;
        padding: 0.375rem 0.75rem;
        background-color: #007bff;
        border-color: #007bff;
        color: white;
        font-size: 0.875rem;
        transition: all 0.3s;
    }
    
    .btn-new-user:hover {
        background-color: #0069d9;
        border-color: #0062cc;
    }
    
    body.dark-mode .btn-new-user {
        background-color: #3a96ff;
        border-color: #3a96ff;
    }
    
    body.dark-mode .btn-new-user:hover {
        background-color: #1a86ff;
        border-color: #1a86ff;
    }
    
    /* Force topbar vertically aligned - This should be enough to fix it */
    .navbar > .container-fluid,
    .navbar > .container-fluid > .col-lg-12 {
        height: 100%;
        display: flex;
        align-items: center;
    }
    
    /* Additional alignment fixes for right side elements */
    .navbar .float-right {
        display: flex;
        align-items: center;
        height: 100%;
    }
    
    .topbar-controls {
        display: flex;
        align-items: center;
        height: 38px;
        margin: 0;
        padding: 0;
    }
    
    /* Fix button alignment */
    .dark-mode-toggle-btn {
        background: transparent;
        border: none;
        border-radius: 20px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-left: 10px;
        margin-right: 8px;
        padding: 0;
    }
    
    /* Fix dropdown alignment */
    .dropdown.d-flex.align-items-center {
        height: 38px;
        display: flex;
        align-items: center;
    }
    
    .user-dropdown {
        background-color: transparent;
        border-radius: 20px;
        padding: 6px 15px;
        transition: background-color 0.3s;
        display: inline-flex;
        align-items: center;
        height: 38px;
        margin-right: 0;  /* Remove right margin */
        line-height: 1.2;
        vertical-align: middle;
    }
</style>  

<!-- Update the navbar structure -->
<nav class="navbar navbar-light fixed-top bg-primary" style="padding:0;min-height: 3.5rem">
  <div class="container-fluid mt-2 mb-2">
      <div class="col-lg-12 d-flex align-items-center"> 
          <div class="d-flex align-items-center flex-grow-1">
              <!-- Menu Button and Logo -->
              <button id="menu-toggle" class="btn btn-light btn-sm mr-3 d-none d-md-block">
                  <i class="fa fa-bars"></i> Menu
              </button>
              <a href="index.php?page=home" class="app-title text-decoration-none">
                 <b>AutoSched</b>
              </a>
              
              <!-- Search Form - Keep in original position -->
              <form class="search-form ml-3" action="search.php" method="GET">
                  <input type="text" name="query" placeholder="Search..." autocomplete="off">
                  <i class="fa fa-search search-icon"></i>
                  <button type="submit"><i class="fa fa-arrow-right"></i></button>
                  <span class="search-shortcut">S</span>
              </form>
          </div>
          
          <!-- Controls at far right -->
          <div class="topbar-controls">
              <button class="dark-mode-toggle-btn" id="topbar-dark-mode">
                  <i id="desktop-dark-mode-icon" class="fas fa-moon"></i>
              </button>
              
              <div class="dropdown">
                  <a href="#" class="dropdown-toggle user-name user-dropdown" id="account_settings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <?php 
                      // Add cache-busting timestamp for profile image
                      $profileTimestamp = isset($_SESSION['profile_timestamp']) ? $_SESSION['profile_timestamp'] : time();
                      ?>
                      <img src="<?php echo isset($_SESSION['login_profile_image']) && !empty($_SESSION['login_profile_image']) ? 'assets/uploads/'.$_SESSION['login_profile_image'] : 'assets/uploads/default.png'; ?>?v=<?php echo $profileTimestamp; ?>" 
                           class="rounded-circle mr-2" style="width: 24px; height: 24px; object-fit: cover;">
                      <?php echo isset($_SESSION['login_name']) ? $_SESSION['login_name'] : (isset($_SESSION['login_username']) ? $_SESSION['login_username'] : 'Guest'); ?>
                  </a>
                  <div class="dropdown-menu" aria-labelledby="account_settings" style="left: -2.5em;">
                      <a class="dropdown-item" href="javascript:void(0)" id="manage_my_account"><i class="fa fa-cog"></i> Manage Account</a>
                      <a class="dropdown-item" href="javascript:void(0)" id="logout_button"><i class="fa fa-power-off"></i> Logout</a>
                  </div>
              </div>
          </div>
      </div>
  </div>
</nav>

<!-- Mobile Search Button - Windows 11 style -->
<div class="d-md-none position-fixed" style="bottom: 70px; right: 20px; z-index: 1000;">
    <button class="btn btn-primary rounded-circle shadow" id="mobile-search-btn" style="width: 45px; height: 45px;">
        <i class="fa fa-search"></i>
    </button>
</div>

<!-- Mobile Search Drawer - Windows 11 style -->
<div class="mobile-search-drawer position-fixed d-md-none" style="bottom: -100px; left: 0; right: 0; transition: bottom 0.3s;">
    <form action="search.php" method="GET">
        <div class="form-group">
            <i class="fa fa-search search-icon"></i>
            <input type="text" name="query" class="form-control" placeholder="Search..." autocomplete="off">
        </div>
        <div class="search-actions">
            <button type="submit" class="btn btn-primary"><i class="fa fa-arrow-right mr-2"></i> Search</button>
            <button type="button" class="btn btn-secondary" id="close-search"><i class="fa fa-times mr-2"></i> Close</button>
        </div>
    </form>
</div>

<!-- Logout Confirmation Modal - Windows 11 style -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 50%; width: 400px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to log out?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
        <a href="ajax.php?action=logout" class="btn btn-primary">Yes</a>
      </div>
    </div>
  </div>
</div>

<script>
  $('#manage_my_account').click(function() {
    uni_modal("Manage Account", "manage_user.php?id=<?php echo isset($_SESSION['login_id']) ? $_SESSION['login_id'] : ''; ?>&mtype=own");
  });

  $('#logout_button').click(function(){
    $('#logoutModal').modal('show');
  });
  
  // Mobile search drawer
  $('#mobile-search-btn').click(function() {
    $('.mobile-search-drawer').css('bottom', '0');
  });
  
  $('#close-search').click(function() {
    $('.mobile-search-drawer').css('bottom', '-100px');
  });
  
  // Initialize dark mode toggle button
  $('#topbar-dark-mode').on('click', function() {
    if (document.body.classList.contains('dark-mode')) {
        // Switch to light mode
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
        $('#desktop-dark-mode-icon').removeClass('fa-sun').addClass('fa-moon');
        $('#dark-mode-icon').removeClass('fa-sun').addClass('fa-moon');
        $('#dark-mode-text').text('Dark Mode');
        
        // Force apply light mode styles immediately
        setTimeout(function() {
            document.querySelectorAll('.navbar.bg-primary').forEach(navbar => {
                navbar.style.backgroundColor = '#ffffff';
                navbar.style.setProperty('background-color', '#ffffff', 'important');
            });
            
            document.querySelectorAll('.navbar a, .navbar .dropdown-toggle, .navbar .text-white, .navbar .app-title, .navbar .user-name').forEach(element => {
                element.style.color = '#444444';
                element.style.setProperty('color', '#444444', 'important');
            });
            
            console.log('Forced light mode styles after toggle');
        }, 50);
    } else {
        // Switch to dark mode
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
        $('#desktop-dark-mode-icon').removeClass('fa-moon').addClass('fa-sun');
        $('#dark-mode-icon').removeClass('fa-moon').addClass('fa-sun');
        $('#dark-mode-text').text('Light Mode');
        
        // Force apply dark mode styles immediately
        setTimeout(function() {
            document.querySelectorAll('.navbar.bg-primary').forEach(navbar => {
                navbar.style.backgroundColor = '#1f1f1f';
                navbar.style.setProperty('background-color', '#1f1f1f', 'important');
            });
            
            document.querySelectorAll('.navbar a, .navbar .dropdown-toggle, .navbar .text-white, .navbar .app-title, .navbar .user-name').forEach(element => {
                element.style.color = '#ffffff';
                element.style.setProperty('color', '#ffffff', 'important');
            });
            
            console.log('Forced dark mode styles after toggle');
        }, 50);
    }
  });

  // Add dark/light mode fix script
  document.addEventListener('DOMContentLoaded', function() {
    // Fix light mode issue in topbar
    if (!document.body.classList.contains('dark-mode')) {
        document.querySelectorAll('.navbar.bg-primary').forEach(navbar => {
            navbar.style.backgroundColor = '#ffffff';
            navbar.style.color = '#444444';
        });
        
        document.querySelectorAll('.app-title').forEach(title => {
            title.style.color = '#444444';
            title.style.setProperty('color', '#444444', 'important');
        });
        
        document.querySelectorAll('.user-name').forEach(name => {
            name.style.color = '#444444';
            name.style.setProperty('color', '#444444', 'important');
        });
        
        document.querySelectorAll('.search-form input').forEach(input => {
            input.style.backgroundColor = '#f5f5f5';
            input.style.borderColor = '#e0e0e0';
            input.style.color = '#444444';
        });
        
        // Add more specific styles for all navbar text
        document.querySelectorAll('.navbar a, .navbar .dropdown-toggle, .navbar .text-white, .navbar .navbar-text').forEach(element => {
            element.style.setProperty('color', '#444444', 'important');
        });
        
        // Force-apply styles to the entire navbar
        document.head.insertAdjacentHTML('beforeend', `
            <style id="force-light-mode">
                body:not(.dark-mode) .navbar.bg-primary, 
                body:not(.dark-mode) .navbar.navbar-light.fixed-top.bg-primary {
                    background-color: #ffffff !important;
                }
                
                body:not(.dark-mode) .navbar.bg-primary *:not(.btn):not(.btn-primary):not(.btn-light):not(.btn-secondary):not(.badge),
                body:not(.dark-mode) .navbar.navbar-light a:not(.btn),
                body:not(.dark-mode) .navbar .app-title,
                body:not(.dark-mode) .navbar .user-name,
                body:not(.dark-mode) .navbar .dropdown-toggle {
                    color: #444444 !important;
                }
                
                body:not(.dark-mode) .navbar a:hover:not(.btn) {
                    color: #0078d7 !important;
                }
            </style>
        `);
        
        console.log('Applied enhanced light mode on page load');
    }
    
    // Watch for theme changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class' && 
                (mutation.target.classList.contains('dark-mode') || 
                 !mutation.target.classList.contains('dark-mode'))) {
                
                if (!document.body.classList.contains('dark-mode')) {
                    // Light mode
                    document.querySelectorAll('.navbar.bg-primary').forEach(navbar => {
                        navbar.style.backgroundColor = '#ffffff';
                        navbar.style.color = '#444444';
                    });
                    
                    document.querySelectorAll('.app-title').forEach(title => {
                        title.style.color = '#444444';
                        title.style.setProperty('color', '#444444', 'important');
                    });
                    
                    document.querySelectorAll('.user-name').forEach(name => {
                        name.style.color = '#444444';
                        name.style.setProperty('color', '#444444', 'important');
                    });
                    
                    document.querySelectorAll('.search-form input').forEach(input => {
                        input.style.backgroundColor = '#f5f5f5';
                        input.style.borderColor = '#e0e0e0';
                        input.style.color = '#444444';
                    });
                    
                    // Add more specific styles for all navbar text
                    document.querySelectorAll('.navbar a, .navbar .dropdown-toggle, .navbar .text-white, .navbar .navbar-text').forEach(element => {
                        element.style.setProperty('color', '#444444', 'important');
                    });
                    
                    // Update or add force-light-mode style
                    let styleElement = document.getElementById('force-light-mode');
                    if (!styleElement) {
                        document.head.insertAdjacentHTML('beforeend', `
                            <style id="force-light-mode">
                                body:not(.dark-mode) .navbar.bg-primary, 
                                body:not(.dark-mode) .navbar.navbar-light.fixed-top.bg-primary {
                                    background-color: #ffffff !important;
                                }
                                
                                body:not(.dark-mode) .navbar.bg-primary *:not(.btn):not(.btn-primary):not(.btn-light):not(.btn-secondary):not(.badge),
                                body:not(.dark-mode) .navbar.navbar-light a:not(.btn),
                                body:not(.dark-mode) .navbar .app-title,
                                body:not(.dark-mode) .navbar .user-name,
                                body:not(.dark-mode) .navbar .dropdown-toggle {
                                    color: #444444 !important;
                                }
                                
                                body:not(.dark-mode) .navbar a:hover:not(.btn) {
                                    color: #0078d7 !important;
                                }
                            </style>
                        `);
                    }
                    
                    console.log('Applied enhanced light mode via observer');
                } else {
                    // Dark mode
                    document.querySelectorAll('.navbar.bg-primary').forEach(navbar => {
                        navbar.style.backgroundColor = '#1f1f1f';
                        navbar.style.color = '#ffffff';
                    });
                    
                    document.querySelectorAll('.app-title').forEach(title => {
                        title.style.color = '#ffffff';
                    });
                    
                    document.querySelectorAll('.user-name').forEach(name => {
                        name.style.color = '#ffffff';
                    });
                    
                    document.querySelectorAll('.search-form input').forEach(input => {
                        input.style.backgroundColor = '#2d2d2d';
                        input.style.borderColor = '#444444';
                        input.style.color = '#ffffff';
                    });
                    
                    console.log('Applied dark mode via observer');
                }
            }
        });
    });
    
    observer.observe(document.body, { attributes: true });
  });
</script>