<?php include 'db_connect.php' ?>

<?php
if(isset($_GET['id'])){
    $subject = $conn->query("SELECT * FROM subjects WHERE id = ".$_GET['id']);
    if($subject->num_rows > 0){
        foreach($subject->fetch_array() as $k => $v){
            $meta[$k] = $v;
        }
    }
    
    // Get department IDs
    $dept_ids = array();
    $dept_qry = $conn->query("SELECT department_id FROM subject_departments WHERE subject_id = ".$_GET['id']);
    while($row = $dept_qry->fetch_assoc()){
        $dept_ids[] = $row['department_id'];
    }
}
?>

<div class="container-fluid">
    <form action="" id="manage-subject">
        <div id="msg"></div>
        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
        <div class="form-group">
            <label class="control-label">Subject</label>
            <input type="text" class="form-control" name="subject" value="<?php echo isset($meta['subject']) ? $meta['subject'] : '' ?>" required>
        </div>
        <div class="form-group">
            <label class="control-label">Units</label>
            <input type="number" class="form-control" name="units" value="<?php echo isset($meta['units']) ? $meta['units'] : '' ?>" required>
        </div>
        <div class="form-group">
            <label class="control-label">Subject Type</label>
            <select name="type" class="form-control" required>
                <option value="Major" <?php echo isset($meta['type']) && $meta['type'] == 'Major' ? 'selected' : '' ?>>Major</option>
                <option value="Minor" <?php echo isset($meta['type']) && $meta['type'] == 'Minor' ? 'selected' : '' ?>>Minor</option>
            </select>
        </div>
        <div class="form-group">
            <label class="control-label">Department</label>
            <select name="department_id[]" class="form-control select2" multiple="multiple">
                <?php 
                $departments = $conn->query("SELECT * FROM courses ORDER BY course ASC");
                while($row = $departments->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" <?php echo isset($dept_ids) && in_array($row['id'], $dept_ids) ? 'selected' : '' ?>><?php echo $row['course'] ?></option>
                <?php endwhile; ?>
            </select>
            <small class="text-muted">You can select multiple departments</small>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select departments",
            width: '100%'
        });
    });
    
    $('#manage-subject').submit(function(e) {
        e.preventDefault();
        
        // Get form values
        var subject = $('[name="subject"]').val().trim();
        var units = $('[name="units"]').val().trim();
        var type = $('[name="type"]').val();
        
        // Validate empty fields
        if(subject === '' || units === '' || type === '') {
            $('#msg').html('<div class="alert alert-danger">Please fill in all required fields</div>');
            return false;
        }
        
        // Validate that units is a number greater than 0
        if(isNaN(units) || parseInt(units) <= 0) {
            $('#msg').html('<div class="alert alert-danger">Units must be a number greater than 0</div>');
            return false;
        }
        
        start_load();
        
        // Get selected departments as array
        var selectedDepts = $(this).find("[name='department_id[]']").val();
        
        // Create a FormData object to handle arrays properly
        var formData = new FormData($(this)[0]);
        
        $.ajax({
            url: 'ajax.php?action=save_subject',
            data: formData,
            method: 'POST',
            processData: false,
            contentType: false,
            success: function(resp) {
                try {
                    var result = JSON.parse(resp);
                    if (result.status == 1) {
                        alert_toast("Data successfully saved", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast(result.message || "An error occurred", 'danger');
                        $('#msg').html('<div class="alert alert-danger">' + (result.message || "An error occurred") + '</div>');
                    }
                } catch(e) {
                    if(resp == 1) {
                        alert_toast("Data successfully saved", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast("An error occurred", 'danger');
                        $('#msg').html('<div class="alert alert-danger">An error occurred</div>');
                    }
                }
                end_load();
            },
            error: function() {
                alert_toast("An error occurred", 'danger');
                $('#msg').html('<div class="alert alert-danger">An error occurred</div>');
                end_load();
            }
        });
    });
</script> 