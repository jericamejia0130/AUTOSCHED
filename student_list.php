<?php include('db_connect.php');?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <b>Student List</b>
                <button class="btn btn-primary btn-sm float-right" id="new_student"><i class="fa fa-plus"></i> New Student</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover" id="student-list">
                    <thead>
                        <tr>
                            <th>ID No.</th>
                            <th>Name</th>
                            <th>Department/Strand</th>
                            <th>Section</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $students = $conn->query("SELECT s.*, u.name as student_name, u.id_no,
                            COALESCE(c.course, st.code) as program,
                            sec.name as section_name
                            FROM students s 
                            LEFT JOIN users u ON s.user_id = u.id
                            LEFT JOIN courses c ON s.course_id = c.id
                            LEFT JOIN strands st ON s.strand_id = st.id
                            LEFT JOIN sections sec ON s.section_id = sec.id
                            ORDER BY u.name ASC");
                        while($row = $students->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['id_no'] ?></td>
                            <td><?php echo ucwords($row['student_name']) ?></td>
                            <td><?php echo $row['program'] ?></td>
                            <td><?php echo $row['section_name'] ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit_student" data-id="<?php echo $row['id'] ?>">Edit</button>
                                <button class="btn btn-sm btn-danger delete_student" data-id="<?php echo $row['id'] ?>">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#student-list').dataTable();
        
        $('#new_student').click(function(){
            uni_modal("New Student","manage_student.php");
        });
        
        $('.edit_student').click(function(){
            uni_modal("Edit Student","manage_student.php?id="+$(this).attr('data-id'));
        });
        
        $('.delete_student').click(function(){
            _conf("Are you sure to delete this student?","delete_student",[$(this).attr('data-id')]);
        });
    });

    function delete_student($id){
        start_load();
        $.ajax({
            url:'ajax.php?action=delete_student',
            method:'POST',
            data:{id:$id},
            success:function(resp){
                if(resp==1){
                    alert_toast("Data successfully deleted",'success');
                    setTimeout(function(){
                        location.reload();
                    },1500);
                }
            }
        });
    }
</script>
