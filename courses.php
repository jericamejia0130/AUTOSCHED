<?php include('db_connect.php');?>
<head>
    <link rel="stylesheet" href="assets/css/datatable-fullwidth.css">
</head>
<div class="container-fluid p-0">
    <div class="card">
        <div class="card-header">
            <h4><b>Department List</b></h4>
            <button class="btn btn-primary btn-sm float-right" id="new_course"><i class="fa fa-plus"></i> New Department</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="courses-table">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Department Code</th>
                            <th class="text-center">Department Name</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $course = $conn->query("SELECT * FROM courses order by id asc");
                        while($row=$course->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $i++ ?></td>
                            <td class="text-center"><?php echo $row['course'] ?></td>
                            <td class="text-center"><?php echo $row['description'] ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary edit_course" type="button" data-id="<?php echo $row['id'] ?>" data-course="<?php echo $row['course'] ?>" data-description="<?php echo $row['description'] ?>">Edit</button>
                                <button class="btn btn-sm btn-danger delete_course" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
    
    td{
        vertical-align: middle !important;
    }
</style>
<script src="assets/js/datatable-init.js"></script>
<script>
    $(document).ready(function() {
        initializeDataTable('#courses-table');
    });
    $('#new_course').click(function(){
        uni_modal("New Department","manage_course.php");
    })
    
    $('.edit_course').click(function(){
        uni_modal("Edit Department","manage_course.php?id="+$(this).attr('data-id'));
    })
    
    $('.delete_course').click(function(){
        _conf("Are you sure to delete this department?","delete_course",[$(this).attr('data-id')])
    })
    
    function delete_course($id){
        start_load()
        $.ajax({
            url:'ajax.php?action=delete_course',
            method:'POST',
            data:{id:$id},
            success:function(resp){
                try {
                    var response = JSON.parse(resp);
                    if(response.status === 0) {
                        alert_toast(response.message, 'danger');
                    } else {
                        alert_toast("Data successfully deleted", 'success');
                        setTimeout(function(){
                            location.reload()
                        },1500);
                    }
                } catch(e) {
                    if(resp == 1){
                        alert_toast("Data successfully deleted",'success')
                        setTimeout(function(){
                            location.reload()
                        },1500)
                    } else {
                        alert_toast("Failed to delete department",'danger')
                    }
                }
                end_load()
            },
            error: function(xhr, status, error) {
                alert_toast("An error occurred: " + error,'danger')
                end_load()
            }
        })
    }
</script>