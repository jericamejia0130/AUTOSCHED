<?php 
include('db_connect.php');
session_start();
if(!isset($_SESSION['login_id']) || $_SESSION['login_type'] !== 'Student'){
    header('location:login.php');
    exit;
}

// Get student information
$student_id = $_SESSION['login_id'];
$student_query = $conn->query("SELECT s.*, c.course, c.description as course_description, sec.name as section_name 
                              FROM students s 
                              LEFT JOIN courses c ON s.course_id = c.id 
                              LEFT JOIN sections sec ON s.section_id = sec.id 
                              WHERE s.user_id = {$student_id}");
$student = $student_query->num_rows > 0 ? $student_query->fetch_assoc() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <?php include('./header.php') ?>
    <style>
        body{
            background: #80808045;
        }
        .schedule-table th, .schedule-table td {
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }
        .schedule-table th {
            background-color: #f8f9fa;
        }
        .print-button {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="d-flex w-100 h-100">
        <nav id="sidebar" class="bg-dark">
            <div class="sidebar-list">
                <a href="student_page.php" class="nav-item nav-home">
                    <span class='icon-field'><i class="fa fa-home"></i></span> Home
                </a>
                <a href="#" class="nav-item nav-schedule" id="view_schedule">
                    <span class='icon-field'><i class="fa fa-calendar"></i></span> My Schedule
                </a>
                <a href="#" class="nav-item nav-profile">
                    <span class='icon-field'><i class="fa fa-user"></i></span> My Profile
                </a>
            </div>
        </nav>
        <div class="content w-100">
            <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top" style="padding:0">
                <div class="container-fluid">
                    <div class="col-md-4 float-left">
                        <button class="btn btn-dark d-md-none" id="menu-toggle"><i class="fa fa-bars"></i></button>
                        <div class="title">Student Dashboard</div>
                    </div>
                    <div class="col-md-8 float-right">
                        <div class="float-right">
                            <div class="dropdown mr-4">
                                <a href="#" class="text-dark dropdown-toggle" id="account_settings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $_SESSION['login_name'] ?> </a>
                                <div class="dropdown-menu" aria-labelledby="account_settings">
                                    <a class="dropdown-item" href="#" id="my_profile"><i class="fa fa-user"></i> My Profile</a>
                                    <a class="dropdown-item" href="#" id="logout_button"><i class="fa fa-power-off"></i> Sign Out</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            <main id="view-panel">
                <div class="container-fluid mt-3">
                    <div class="col-lg-12">
                        <div class="row">
                            <!-- Student Info Panel -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <b>Personal Information</b>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <dl>
                                                    <dt><b>Name</b></dt>
                                                    <dd><?php echo $_SESSION['login_name'] ?></dd>
                                                    <dt><b>Department</b></dt>
                                                    <dd><?php echo isset($student['course']) ? $student['course'] : 'Not Assigned' ?></dd>
                                                    <dt><b>Section</b></dt>
                                                    <dd><?php echo isset($student['section_name']) ? $student['section_name'] : 'Not Assigned' ?></dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Schedule Panel -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <b>My Class Schedule</b>
                                        <?php if(isset($student['section_id'])): ?>
                                        <button class="btn btn-sm btn-primary print-button" onclick="printSchedule(<?php echo $student['section_id']; ?>)">
                                            <i class="fa fa-print"></i> Print Schedule
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <?php if(isset($student['section_id']) && $student['section_id'] > 0): ?>
                                            <div id="schedule-container">
                                                <?php
                                                // Get the schedules for this student's section
                                                $schedules = $conn->query("SELECT s.*, 
                                                    sub.subject as subject_name,
                                                    CONCAT(f.lastname, ', ', f.firstname) as faculty_name,
                                                    r.name as room_name 
                                                    FROM schedules s 
                                                    LEFT JOIN subjects sub ON s.subject_id = sub.id
                                                    LEFT JOIN faculty f ON s.faculty_id = f.id
                                                    LEFT JOIN rooms r ON s.room_id = r.id
                                                    WHERE s.section_id = {$student['section_id']}
                                                    ORDER BY FIELD(s.dow, 0, 1, 2, 3, 4, 5, 6), s.time_from");
                                                
                                                if($schedules->num_rows > 0):
                                                ?>
                                                <table class="table table-bordered schedule-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Day</th>
                                                            <th>Time</th>
                                                            <th>Subject</th>
                                                            <th>Faculty</th>
                                                            <th>Room</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        while($row = $schedules->fetch_assoc()):
                                                            // Format days
                                                            $days_arr = explode(',', $row['dow']);
                                                            $day_names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                                            $formatted_days = [];
                                                            
                                                            foreach($days_arr as $day_index) {
                                                                if(isset($day_names[$day_index])) {
                                                                    $formatted_days[] = $day_names[$day_index];
                                                                }
                                                            }
                                                            
                                                            $days_display = implode(', ', $formatted_days);
                                                            
                                                            // Format time
                                                            $time_from = date('h:i A', strtotime($row['time_from']));
                                                            $time_to = date('h:i A', strtotime($row['time_to']));
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $days_display; ?></td>
                                                            <td><?php echo $time_from . ' - ' . $time_to; ?></td>
                                                            <td><?php echo $row['subject_name']; ?></td>
                                                            <td><?php echo $row['faculty_name']; ?></td>
                                                            <td><?php echo $row['room_name']; ?></td>
                                                        </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                                <?php else: ?>
                                                <div class="alert alert-info">No schedule found for your section.</div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                        <div class="alert alert-warning">You are not assigned to any section yet. Please contact your administrator.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="confirm_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                </div>
                <div class="modal-body">
                    <div id="delete_content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="uni_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="uni_modal_right" role='dialog'>
        <div class="modal-dialog modal-full-height  modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span class="fa fa-arrow-right"></span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewer_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
                <img src="" alt="">
            </div>
        </div>
    </div>
    
    <!-- Logout Confirmation Modal -->
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
</body>
<script>
    window.start_load = function(){
        $('body').prepend('<di id="preloader2"></di>')
    }
    window.end_load = function(){
        $('#preloader2').fadeOut('fast', function() {
            $(this).remove();
        })
    }
    window.viewer_modal = function($src = ''){
        start_load()
        var t = $src.split('.')
        t = t[1]
        if(t =='mp4'){
            var view = $("<video src='"+$src+"' controls autoplay></video>")
        }else{
            var view = $("<img src='"+$src+"' />")
        }
        $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove()
        $('#viewer_modal .modal-content').append(view)
        $('#viewer_modal').modal({
            show:true,
            backdrop:'static',
            keyboard:false,
            focus:true
        })
        end_load()  
    }
    window.uni_modal = function($title = '' , $url='',$size=""){
        start_load()
        $.ajax({
            url:$url,
            error:err=>{
                console.log()
                alert("An error occured")
            },
            success:function(resp){
                if(resp){
                    $('#uni_modal .modal-title').html($title)
                    $('#uni_modal .modal-body').html(resp)
                    if($size != ''){
                        $('#uni_modal .modal-dialog').addClass($size)
                    }else{
                        $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md")
                    }
                    $('#uni_modal').modal({
                        show:true,
                        backdrop:'static',
                        keyboard:false,
                        focus:true
                    })
                    end_load()
                }
            }
        })
    }
    window._conf = function($msg='',$func='',$params = []){
        $('#confirm_modal #confirm').attr('onclick',$func+"("+$params.join(',')+")")
        $('#confirm_modal .modal-body').html($msg)
        $('#confirm_modal').modal('show')
    }
    window.alert_toast= function($msg = 'TEST',$bg = 'success' ,$pos=''){
        var Toast = Swal.mixin({
            toast: true,
            position: $pos || 'top-end',
            showConfirmButton: false,
            timer: 5000
        });
        Toast.fire({
            icon: $bg,
            title: $msg
        })
    }

    $('.nav-item').each(function(){
        if($(this).hasClass('active'))
            $(this).removeClass('active')
        if($(this).attr('href') == "<?php echo isset($_GET['page']) ? $_GET['page'].'.php' : '' ?>"){
            $(this).addClass('active')
        }
    })

    // Add toggle functionality for mobile
    $('#menu-toggle').click(function(e) {
        e.preventDefault();
        $('#sidebar').toggleClass('d-none');
    });

    // Profile handling
    $('#my_profile').click(function() {
        uni_modal("My Profile", "manage_student_profile.php", "mid-large");
    });
    
    // Logout handling
    $('#logout_button').click(function() {
        $('#logoutModal').modal('show');
    });
    
    // View schedule
    $('#view_schedule').click(function() {
        // Just scroll to schedule section for now
        $('html, body').animate({
            scrollTop: $("#schedule-container").offset().top - 100
        }, 500);
    });
    
    // Function to print schedule
    function printSchedule(sectionId) {
        if(sectionId) {
            window.open('print_schedule.php?section_id='+sectionId, '_blank');
        } else {
            alert_toast('Section ID not found', 'error');
        }
    }
</script>
</html>