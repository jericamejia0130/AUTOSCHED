<?php
include 'db_connect.php';
session_start();

if(!isset($_SESSION['login_id']) || $_SESSION['login_type'] !== 'Student'){
    header('location:login.php');
    exit;
}

// Get student information
$student_id = $_SESSION['login_id'];
$student_query = $conn->query("SELECT s.*, u.name, u.username, u.id_no, 
                              c.course, c.description as course_description, 
                              sec.name as section_name 
                              FROM students s 
                              LEFT JOIN users u ON s.user_id = u.id 
                              LEFT JOIN courses c ON s.course_id = c.id 
                              LEFT JOIN sections sec ON s.section_id = sec.id 
                              WHERE s.user_id = {$student_id}");
$student = $student_query->num_rows > 0 ? $student_query->fetch_assoc() : [];
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form id="manage-student-profile">
                <input type="hidden" name="id" value="<?php echo $student_id ?>">
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo isset($student['name']) ? $student['name'] : '' ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($student['username']) ? $student['username'] : '' ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="id_no">ID Number</label>
                    <input type="text" name="id_no" id="id_no" class="form-control" value="<?php echo isset($student['id_no']) ? $student['id_no'] : '' ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" name="department" id="department" class="form-control" value="<?php echo isset($student['course']) ? $student['course'] . ' - ' . $student['course_description'] : 'Not Assigned' ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="section">Section</label>
                    <input type="text" name="section" id="section" class="form-control" value="<?php echo isset($student['section_name']) ? $student['section_name'] : 'Not Assigned' ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control">
                    <small class="text-muted">Leave blank if you don't want to change password</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#manage-student-profile').submit(function(e){
        e.preventDefault();
        
        // Validate password fields
        var newPass = $('#new_password').val();
        var confirmPass = $('#confirm_password').val();
        
        if(newPass !== '' && newPass !== confirmPass) {
            alert_toast('New password and confirm password do not match', 'error');
            return false;
        }
        
        start_load();
        
        $.ajax({
            url: 'ajax.php?action=update_student_password',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp){
                try {
                    var response = JSON.parse(resp);
                    if(response.status == 1){
                        alert_toast('Profile updated successfully', 'success');
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast(response.message || 'Error updating profile', 'error');
                    }
                } catch(e) {
                    console.error(e);
                    alert_toast('An error occurred', 'error');
                }
                end_load();
            },
            error: function(err){
                console.error(err);
                alert_toast('An error occurred', 'error');
                end_load();
            }
        });
    });
</script> 