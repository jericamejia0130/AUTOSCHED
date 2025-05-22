<?php
include 'db_connect.php';
session_start();
if(!isset($_SESSION['login_id']) || $_SESSION['login_type'] !== 'Student')
    die('Access denied');

$qry = $conn->query("SELECT s.*, u.*, c.course, sec.name as section 
                     FROM students s 
                     LEFT JOIN users u ON s.user_id = u.id 
                     LEFT JOIN courses c ON s.course_id = c.id 
                     LEFT JOIN sections sec ON s.section_id = sec.id 
                     WHERE s.user_id = ".$_SESSION['login_id']);
$data = $qry->fetch_array();
?>
<div class="container-fluid">
    <form id="manage-profile">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?php echo $data['name'] ?>" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="<?php echo $data['username'] ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control">
            <small><i>Leave this blank if you dont want to change your password</i></small>
        </div>
        <div class="form-group">
            <label>Course: <?php echo $data['course'] ?></label>
        </div>
        <div class="form-group">
            <label>Section: <?php echo $data['section'] ?></label>
        </div>
    </form>
</div>

<script>
    $('#manage-profile').submit(function(e){
        e.preventDefault();
        start_load();
        $.ajax({
            url:'ajax.php?action=update_student_profile',
            method:'POST',
            data:$(this).serialize(),
            success:function(resp){
                if(resp == 1){
                    alert_toast("Profile successfully updated",'success');
                    setTimeout(function(){
                        location.reload();
                    },1000);
                }else{
                    alert_toast("An error occurred",'danger');
                }
                end_load();
            }
        });
    });
</script>