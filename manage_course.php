<?php include 'db_connect.php' ?>
<?php
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM courses WHERE id = ".$_GET['id']);
    foreach($qry->fetch_array() as $k => $v){
        $$k = $v;
    }
}
?>
<head>
    <link rel="stylesheet" href="assets/css/form-styles.css">
</head>
<div class="container-fluid">
    <form action="" id="manage-course">
        <div id="msg"></div>
        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
        <div class="form-group">
            <label class="control-label">Department Code</label>
            <input type="text" class="form-control" name="course" value="<?php echo isset($course) ? $course : '' ?>" required>
        </div>
        <div class="form-group">
            <label class="control-label">Department Name</label>
            <textarea class="form-control" rows='2' name="description" required><?php echo isset($description) ? $description : '' ?></textarea>
        </div>
    </form>
</div>

<script>
    $('#manage-course').submit(function(e) {
        e.preventDefault();
        
        // Get form values
        var courseCode = $('[name="course"]').val().trim();
        var description = $('[name="description"]').val().trim();
        
        // Validate empty fields
        if(courseCode === '' || description === '') {
            $('#msg').html('<div class="alert alert-danger">Please fill in all required fields</div>');
            return false;
        }
        
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_course',
            data: $(this).serialize(),
            method: 'POST',
            dataType: 'json',
            success: function(resp) {
                if (resp.status == 1) {
                    alert_toast("Data successfully saved", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast(resp.message, 'danger');
                    $('#msg').html('<div class="alert alert-danger">' + resp.message + '</div>');
                    end_load();
                }
            },
            error: function() {
                alert_toast("An error occurred", 'danger');
                end_load();
            }
        });
    });
</script>