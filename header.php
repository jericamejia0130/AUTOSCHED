<meta content="" name="descriptison">
<meta content="" name="keywords">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#2b2a2a">
<meta name="color-scheme" content="light dark">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
<link rel="stylesheet" href="assets/font-awesome/css/all.min.css">

<!-- Vendor CSS Files -->
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/icofont/icofont.min.css" rel="stylesheet">
<link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
<link href="assets/vendor/venobox/venobox.css" rel="stylesheet">
<link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
<link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
<link href="assets/vendor/owl.carousel/assets/owl.carousel.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
<link href="assets/DataTables/datatables.min.css" rel="stylesheet">
<link href="assets/css/jquery.datetimepicker.min.css" rel="stylesheet">
<link href="assets/fullcalendar/main.css" rel="stylesheet">
<link href="assets/css/select2.min.css" rel="stylesheet">

<!-- Template Main CSS Files -->
<link href="assets/css/style.css" rel="stylesheet">
<link type="text/css" rel="stylesheet" href="assets/css/jquery-te-1.4.0.css">
<link href="assets/css/mobile-dark.css" rel="stylesheet">
<link href="assets/action-buttons.css" rel="stylesheet">
<link href="assets/css/responsive-tables.css" rel="stylesheet">
<link href="assets/css/force-full-width.css" rel="stylesheet">

<!-- Core Scripts -->
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/DataTables/datatables.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/jquery.easing/jquery.easing.min.js"></script>

<!-- Page Enhancement Scripts -->
<script src="assets/vendor/php-email-form/validate.js"></script>
<script src="assets/vendor/venobox/venobox.min.js"></script>
<script src="assets/vendor/waypoints/jquery.waypoints.min.js"></script>
<script src="assets/vendor/counterup/counterup.min.js"></script>
<script src="assets/vendor/owl.carousel/owl.carousel.min.js"></script>
<script src="assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="assets/js/select2.min.js"></script>
<script type="text/javascript" src="assets/js/jquery.datetimepicker.full.min.js"></script>
<script type="text/javascript" src="assets/font-awesome/js/all.min.js"></script>
<script type="text/javascript" src="assets/fullcalendar/main.js"></script>
<script type="text/javascript" src="assets/js/jquery-te-1.4.0.min.js" charset="utf-8"></script>

<!-- Custom Application Scripts -->
<script src="assets/js/admin.js"></script>
<script src="assets/js/mobile.js"></script>
<script src="assets/js/responsive-tables.js"></script>
<script src="assets/js/datatables-fix.js"></script>

<!-- Dark Mode Script -->
<script>
    $(document).ready(function() {
        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            $('#dark-mode-icon').removeClass('fa-moon').addClass('fa-sun');
        }
        
        // Add dark mode toggle button to body
        if (!$('.dark-mode-toggle').length) {
            $('body').append('<button class="dark-mode-toggle d-md-none floating-action-button"><i id="dark-mode-icon" class="fas fa-moon"></i></button>');
        }
        
        // Dark mode toggle functionality
        $(document).on('click', '.dark-mode-toggle', function() {
            if (document.body.classList.contains('dark-mode')) {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'disabled');
                $('.fa-sun').removeClass('fa-sun').addClass('fa-moon');
                $('#dark-mode-text').text('Dark Mode');
            } else {
                document.body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
                $('.fa-moon').removeClass('fa-moon').addClass('fa-sun');
                $('#dark-mode-text').text('Light Mode');
            }
        });
        
        // Handle system dark mode changes
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            // Set initial state based on system preference if no user preference is saved
            if (!localStorage.getItem('darkMode') && mediaQuery.matches) {
                document.body.classList.add('dark-mode');
                $('#dark-mode-icon').removeClass('fa-moon').addClass('fa-sun');
                $('#desktop-dark-mode-icon').removeClass('fa-moon').addClass('fa-sun');
                $('#dark-mode-text').text('Light Mode');
                localStorage.setItem('darkMode', 'enabled');
            }
            
            // Listen for changes in system preference
            mediaQuery.addEventListener('change', (e) => {
                // Only apply if user hasn't set a preference
                if (!localStorage.getItem('darkMode')) {
                    if (e.matches) {
                        document.body.classList.add('dark-mode');
                        $('.fa-moon').removeClass('fa-moon').addClass('fa-sun');
                        $('#dark-mode-text').text('Light Mode');
                    } else {
                        document.body.classList.remove('dark-mode');
                        $('.fa-sun').removeClass('fa-sun').addClass('fa-moon');
                        $('#dark-mode-text').text('Dark Mode');
                    }
                }
            });
        }
    });
</script>



