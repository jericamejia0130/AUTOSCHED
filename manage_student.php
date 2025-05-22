<?php include 'db_connect.php';

if(isset($_GET['id'])){
    $student = $conn->query("SELECT * FROM students WHERE id = ".$_GET['id'])->fetch_array();
}
?>

<div class="container-fluid">
    <form id="manage-student">
        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
        
        <div class="form-group">
            <label for="user_id">Student</label>
            <select name="user_id" id="user_id" class="form-control" required>
                <option value="">Select Student</option>
                <?php 
                $users = $conn->query("SELECT * FROM users WHERE type='Student' ORDER BY name ASC");
                while($row = $users->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" <?php echo isset($student) && $student['user_id'] == $row['id'] ? 'selected' : '' ?>><?php echo ucwords($row['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Level</label>
            <select name="level_type" id="level_type" class="form-control" required>
                <option value="">Select Level</option>
                <option value="College">College</option>
                <option value="SHS">Senior High School</option>
            </select>
        </div>

        <div id="college_fields" style="display:none;">
            <div class="form-group">
                <label>Department</label>
                <select name="course_id" id="course_id" class="form-control">
                    <option value="">Select Department</option>
                    <?php 
                    $courses = $conn->query("SELECT * FROM courses ORDER BY course ASC");
                    while($row = $courses->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>"><?php echo $row['course'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div id="shs_fields" style="display:none;">
            <div class="form-group">
                <label>Strand</label>
                <select name="strand_id" id="strand_id" class="form-control">
                    <option value="">Select Strand</option>
                    <?php 
                    $strands = $conn->query("SELECT * FROM strands ORDER BY code ASC");
                    while($row = $strands->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>"><?php echo $row['code'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Section</label>
            <select name="section_id" id="section_id" class="form-control" required>
                <option value="">Select Section</option>
            </select>
        </div>
    </form>
</div>

<script>
$(document).ready(function(){
    $('#level_type').change(function(){
        var type = $(this).val();
        $('#college_fields, #shs_fields').hide();
        $('#course_id, #strand_id').val('');
        if(type == 'College'){
            $('#college_fields').show();
        } else if(type == 'SHS'){
            $('#shs_fields').show();
        }
    });

    $('#course_id').change(function(){
        loadSections('college');
    });

    $('#strand_id').change(function(){
        loadSections('shs');
    });

    function loadSections(type){
        var id = type == 'college' ? $('#course_id').val() : $('#strand_id').val();
        $.ajax({
            url: 'ajax.php?action=get_sections',
            method: 'POST',
            data: {
                type: type,
                id: id
            },
            success: function(resp){
                $('#section_id').html(resp);
            }
        });
    }
});

$('#manage-student').submit(function(e){
    e.preventDefault();
    start_load();
    $.ajax({
        url:'ajax.php?action=save_student',
        method:'POST',
        data:$(this).serialize(),
        success:function(resp){
            if(resp == 1){
                alert_toast("Data successfully saved.",'success');
                setTimeout(function(){
                    location.reload();
                },1000);
            }
        }
    });
});
</script>
