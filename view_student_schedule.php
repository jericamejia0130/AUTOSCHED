<?php
session_start(); // Make sure session is started

// Debug session information
if(isset($_GET['debug']) && $_GET['debug'] == 1) {
    echo "<h3>Session Debug</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

// Check if student is logged in via section code
if(!isset($_SESSION['student_section_id'])) {
    echo "<script>alert('Please login first.'); window.location.href='login.php';</script>";
    exit;
}

include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Student Schedule - AutoSched</title>
  
  <?php include('./header.php'); ?>
  <!-- Add FullCalendar -->
  <link href="assets/fullcalendar/main.css" rel="stylesheet">
  <script src="assets/fullcalendar/main.js"></script>
  
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
    }
    
    /* Top Navigation Bar */
    .topbar {
      background-color: #fff;
      border-bottom: 1px solid #e0e0e0;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .topbar-logo {
      display: flex;
      align-items: center;
    }
    
    .topbar-logo a {
      font-weight: bold;
      font-size: 18px;
      color: #333;
      text-decoration: none;
    }
    
    .menu-toggle {
      background: none;
      border: none;
      cursor: pointer;
      padding: 5px;
      margin-right: 15px;
    }
    
    .search-container {
      flex-grow: 1;
      max-width: 400px;
      margin: 0 15px;
      position: relative;
    }
    
    .search-container input {
      width: 100%;
      padding: 8px 15px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    
    .search-container .search-icon {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #aaa;
    }
    
    .user-menu {
      display: flex;
      align-items: center;
    }
    
    .user-menu .admin-dropdown {
      margin-left: 15px;
    }
    
    .dark-mode-toggle {
      background: none;
      border: none;
      color: #333;
      cursor: pointer;
      margin-right: 15px;
    }
    
    /* Sidebar */
    .sidebar {
      width: 200px;
      background-color: #fff;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      padding-top: 60px;
      border-right: 1px solid #e0e0e0;
      overflow-y: auto;
    }
    
    .sidebar-logo {
      text-align: center;
      padding: 20px 0;
    }
    
    .sidebar-logo img {
      width: 100px;
      height: 100px;
      object-fit: contain;
    }
    
    .sidebar-menu {
      list-style: none;
      padding: 0;
      margin: 20px 0;
    }
    
    .sidebar-menu li {
      padding: 10px 20px;
      display: flex;
      align-items: center;
    }
    
    .sidebar-menu li a {
      text-decoration: none;
      color: #333;
      display: flex;
      align-items: center;
      width: 100%;
    }
    
    .sidebar-menu li i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }
    
    .sidebar-menu li:hover {
      background-color: #f5f5f5;
    }
    
    .sidebar-menu li.active {
      background-color: #e3f2fd;
      border-left: 3px solid #2196F3;
    }
    
    .sidebar-menu li.active a {
      color: #2196F3;
    }
    
    /* Main Content */
    .main-content {
      margin-left: 200px;
      padding: 80px 20px 20px;
      min-height: calc(100vh - 100px);
    }
    
    .content-card {
      background-color: #fff;
      border-radius: 4px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
      margin-bottom: 20px;
      overflow: hidden;
    }
    
    .card-header {
      background-color: #2196F3;
      color: white;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .card-header h4 {
      margin: 0;
      font-weight: 500;
    }
    
    .card-header .btn {
      background-color: rgba(255, 255, 255, 0.1);
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s;
      font-size: 14px;
      display: flex;
      align-items: center;
    }
    
    .card-header .btn i {
      margin-right: 5px;
    }
    
    .card-header .btn:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }
    
    .card-body {
      padding: 20px;
    }
    
    /* View Selector */
    .view-selector {
      margin-bottom: 20px;
      display: flex;
      border-bottom: 1px solid #ddd;
    }
    
    .view-selector button {
      background: none;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      font-size: 14px;
      border-bottom: 2px solid transparent;
    }
    
    .view-selector button.active {
      border-bottom: 2px solid #2196F3;
      color: #2196F3;
      font-weight: 500;
    }
    
    /* Calendar View */
    #calendar {
      height: 650px;
    }
    
    /* Schedule Table */
    .schedule-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .schedule-table th {
      background-color: #f5f5f5;
      padding: 12px 15px;
      text-align: left;
      border-bottom: 2px solid #ddd;
      font-weight: 600;
    }
    
    .schedule-table td {
      padding: 10px 15px;
      border-bottom: 1px solid #ddd;
    }
    
    .schedule-table tr:hover {
      background-color: #f9f9f9;
    }
    
    .day-badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 12px;
      margin-right: 4px;
      color: white;
      font-size: 0.8rem;
    }
    
    .day-badge.sun { background-color: #fd7e14; }
    .day-badge.mon { background-color: #20c997; }
    .day-badge.tue { background-color: #6f42c1; }
    .day-badge.wed { background-color: #0dcaf0; }
    .day-badge.thu { background-color: #dc3545; }
    .day-badge.fri { background-color: #fd7e14; }
    .day-badge.sat { background-color: #6610f2; }
    
    /* Print Styles */
    @media print {
      .topbar, .sidebar, .view-selector, .no-print {
        display: none !important;
      }
      
      .main-content {
        margin-left: 0;
        padding: 0;
      }
      
      .content-card {
        box-shadow: none;
        border: 1px solid #ddd;
      }
      
      body {
        background-color: white;
      }
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-200px);
        transition: transform 0.3s;
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
      }
      
      .search-container {
        display: none;
      }
    }
  </style>
</head>
<body>
  <?php
  // Get section information
  $section_id = $_SESSION['student_section_id'];
  $query = $conn->query("SELECT s.*, 
                       c.course as department_name, c.description as department_desc,
                       st.code as strand_code, st.name as strand_name,
                       CONCAT(f.lastname, ', ', f.firstname, ' ', COALESCE(f.middlename, '')) as adviser_name
                       FROM sections s 
                       LEFT JOIN courses c ON s.course_id = c.id
                       LEFT JOIN strands st ON s.strand_id = st.id
                       LEFT JOIN faculty f ON s.faculty_id = f.id
                       WHERE s.id = $section_id");

  if($query->num_rows == 0) {
      echo "<div class='alert alert-danger'>Section not found.</div>";
      exit;
  }

  $section = $query->fetch_assoc();
  $level_type = $section['course_id'] ? 'College' : 'SHS';
  $year_level = $section['year_level'];
  
  // Format section name
  if($level_type == 'College') {
      $section_title = ucwords($section['department_name']) . " - " . $year_level . 
                      ($year_level == 1 ? 'st' : ($year_level == 2 ? 'nd' : ($year_level == 3 ? 'rd' : 'th'))) . 
                      " Year, Section " . $section['name'];
  } else {
      $section_title = $section['strand_code'] . " - Grade " . $year_level . ", Section " . $section['name'];
  }
  ?>

  <!-- Top Navigation Bar -->
  <div class="topbar">
    <div class="topbar-logo">
      <button class="menu-toggle">
        <i class="fa fa-bars"></i>
      </button>
      <a href="#">AutoSched</a>
    </div>
    
    <div class="search-container">
      <input type="text" placeholder="Search...">
      <span class="search-icon"><i class="fa fa-search"></i></span>
    </div>
    
    <div class="user-menu">
      <button class="dark-mode-toggle">
        <i class="fa fa-moon"></i>
      </button>
      <div class="admin-dropdown">
        <span>Student</span>
      </div>
    </div>
  </div>
  
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-logo">
      <img src="assets/img/logo.png" alt="Logo">
    </div>
    
    <ul class="sidebar-menu">
      <li><a href="#"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
      <li class="active"><a href="#"><i class="fa fa-calendar-alt"></i> Schedule</a></li>
      <li><a href="#"><i class="fa fa-building"></i> School Profile</a></li>
      <li><a href="#"><i class="fa fa-phone"></i> Contact</a></li>
      <li><a href="login.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </div>
  
  <!-- Main Content -->
  <div class="main-content">
    <div class="content-card">
      <div class="card-header">
        <h4><i class="fa fa-calendar-alt"></i> Schedule</h4>
        <div>
          <button class="btn" id="print-schedule"><i class="fa fa-print"></i> Print Schedule</button>
        </div>
      </div>
      
      <div class="card-body">
        <!-- Section Information -->
        <div class="section-info mb-4">
          <h4><?php echo $section_title; ?></h4>
          <?php if($section['adviser_name']): ?>
            <p><strong>Adviser:</strong> <span class="adviser-badge"><i class="fa fa-user-tie mr-1"></i><?php echo $section['adviser_name']; ?></span></p>
          <?php else: ?>
            <p><strong>Adviser:</strong> <span class="adviser-badge text-muted"><i class="fa fa-user-slash mr-1"></i>No adviser assigned</span></p>
          <?php endif; ?>
        </div>
        
        <!-- View Selector -->
        <div class="view-selector">
          <button id="list-view-btn" class="active"><i class="fa fa-list mr-2"></i>List View</button>
          <button id="calendar-view-btn"><i class="fa fa-calendar mr-2"></i>Calendar View</button>
        </div>
        
        <!-- List View -->
        <div id="list-view">
          <table class="schedule-table">
            <thead>
              <tr>
                <th><i class="fa fa-book mr-2"></i>Subject</th>
                <th><i class="fa fa-calendar-day mr-2"></i>Days</th>
                <th><i class="fa fa-clock mr-2"></i>Time</th>
                <th><i class="fa fa-door-open mr-2"></i>Room</th>
                <th><i class="fa fa-chalkboard-teacher mr-2"></i>Teacher</th>
              </tr>
            </thead>
            <tbody id="schedule-list">
              <tr>
                <td colspan="5" class="text-center">
                  <div class="spinner-border text-primary" role="status"></div>
                  <span class="ml-2">Loading schedule...</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Calendar View -->
        <div id="calendar-view" style="display:none;">
          <div id="calendar"></div>
        </div>
      </div>
    </div>
  </div>

  <script>
  $(document).ready(function(){
    // Toggle sidebar on mobile
    $('.menu-toggle').click(function(){
      $('.sidebar').toggleClass('active');
    });
    
    // Toggle between list and calendar views
    $('#list-view-btn').click(function(){
      $(this).addClass('active');
      $('#calendar-view-btn').removeClass('active');
      $('#list-view').show();
      $('#calendar-view').hide();
    });
    
    $('#calendar-view-btn').click(function(){
      $(this).addClass('active');
      $('#list-view-btn').removeClass('active');
      $('#list-view').hide();
      $('#calendar-view').show();
      
      if (!calendarInitialized) {
        initializeFullCalendar();
      }
    });
    
    // Print Schedule
    $('#print-schedule').click(function(){
      window.print();
    });
    
    // Load the schedules
    loadSectionSchedules();
    
    // Calendar variables
    let calendarInitialized = false;
    let calendar = null;
    
    // Initialize FullCalendar
    function initializeFullCalendar() {
      const calendarEl = document.getElementById('calendar');
      calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        allDaySlot: false,
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',
        slotDuration: '00:30:00',
        events: getCalendarEvents(),
        eventTimeFormat: {
          hour: '2-digit',
          minute: '2-digit',
          meridiem: 'short'
        },
        eventClick: function(info) {
          // Show event details when clicked
          alert(info.event.extendedProps.description);
        }
      });
      
      calendar.render();
      calendarInitialized = true;
    }
    
    function getCalendarEvents() {
      const sectionId = <?php echo $section_id; ?>;
      let events = [];
      
      $.ajax({
        url: 'ajax.php?action=get_section_schedules',
        method: 'POST',
        data: { section_id: sectionId },
        dataType: 'json',
        async: false, // Synchronous to ensure events are ready
        success: function(schedules) {
          if(Array.isArray(schedules)) {
            schedules.forEach(schedule => {
              if(!schedule.dow || !schedule.time_from || !schedule.time_to) return;
              
              const dowArray = schedule.dow.split(',').map(Number);
              const title = `${schedule.subject} (${schedule.room_name || 'No Room'})`;
              const description = `Subject: ${schedule.subject}
Room: ${schedule.room_name || 'Not assigned'}
Teacher: ${schedule.faculty_name || 'Not assigned'}`;
              
              // Format for FullCalendar weekly recurring events
              dowArray.forEach(dow => {
                events.push({
                  title: title,
                  daysOfWeek: [dow],
                  startTime: schedule.time_from,
                  endTime: schedule.time_to,
                  description: description,
                  backgroundColor: getSubjectColor(schedule.subject_id),
                  borderColor: getSubjectColor(schedule.subject_id)
                });
              });
            });
          }
        }
      });
      
      return events;
    }
    
    // Generate consistent colors based on subject_id
    function getSubjectColor(subjectId) {
      const colors = [
        '#4285F4', '#EA4335', '#FBBC05', '#34A853', // Google colors
        '#3498db', '#e74c3c', '#2ecc71', '#f39c12', // Flat UI colors
        '#9b59b6', '#1abc9c', '#d35400', '#c0392b'  // More colors
      ];
      
      // Use the subject ID to pick a color, or a default if no ID
      return colors[subjectId % colors.length] || '#007bff';
    }
    
    function loadSectionSchedules() {
      const sectionId = <?php echo $section_id; ?>;
      
      $.ajax({
        url: 'ajax.php?action=get_section_schedules',
        method: 'POST',
        data: { section_id: sectionId },
        dataType: 'json',
        success: function(schedules) {
          if(Array.isArray(schedules) && schedules.length > 0) {
            renderScheduleList(schedules);
          } else {
            $('#schedule-list').html('<tr><td colspan="5" class="text-center">No schedules available for this section.</td></tr>');
          }
        },
        error: function() {
          $('#schedule-list').html('<tr><td colspan="5" class="text-center text-danger">Error loading schedules. Please try again later.</td></tr>');
        }
      });
    }
    
    function renderScheduleList(schedules) {
      let html = '';
      
      schedules.forEach(schedule => {
        // Format days of week
        let days = '';
        if(schedule.dow) {
          const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
          const dayClasses = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
          const dayIndices = schedule.dow.split(',');
          days = dayIndices.map(index => {
            const dayIndex = parseInt(index);
            const dayClass = dayClasses[dayIndex];
            return `<span class="day-badge ${dayClass}">${dayNames[dayIndex].substring(0, 3)}</span>`;
          }).join(' ');
        } else {
          days = 'Not set';
        }
        
        // Format time
        const timeFrom = schedule.time_from ? formatTime(schedule.time_from) : '';
        const timeTo = schedule.time_to ? formatTime(schedule.time_to) : '';
        const timeSlot = timeFrom && timeTo ? `${timeFrom} - ${timeTo}` : 'Not set';
        
        html += `<tr>
          <td><strong>${schedule.subject || 'N/A'}</strong></td>
          <td>${days}</td>
          <td>${timeSlot}</td>
          <td>${schedule.room_name || 'N/A'}</td>
          <td>${schedule.faculty_name || 'Not assigned'}</td>
        </tr>`;
      });
      
      $('#schedule-list').html(html);
    }
    
    // Time formatting helper
    function formatTime(timeString) {
      try {
        const date = new Date(`2000-01-01T${timeString}`);
        return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
      } catch(e) {
        return timeString;
      }
    }
    
    // Dark mode toggle
    $('.dark-mode-toggle').click(function() {
      $('body').toggleClass('dark-mode');
      $(this).find('i').toggleClass('fa-moon fa-sun');
    });
  });
  </script>
</body>
</html> 