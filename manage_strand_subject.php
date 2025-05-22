<?php include 'db_connect.php' ?>

<?php
if(isset($_GET['id'])){
    $subject = $conn->query("SELECT * FROM strand_subjects WHERE id = ".$_GET['id']);
    if($subject->num_rows > 0){
        foreach($subject->fetch_array() as $k => $v){
            $meta[$k] = $v;
        }
    }
    
    // Get strand IDs
    $strand_ids = array();
    $strand_qry = $conn->query("SELECT strand_id FROM subject_strands WHERE subject_id = ".$_GET['id']);
    while($row = $strand_qry->fetch_assoc()){
        $strand_ids[] = $row['strand_id'];
    }
}
?>

<head>
    <link rel="stylesheet" href="assets/css/form-styles.css">
</head>

<div class="container-fluid">
    <form action="" id="manage-strand-subject">
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
            <select name="subject_type" class="form-control" required>
                <option value="">Select Type</option>
                <option value="Core" <?php echo isset($meta['subject_type']) && $meta['subject_type'] == 'Core' ? 'selected' : '' ?>>Core</option>
                <option value="Applied" <?php echo isset($meta['subject_type']) && $meta['subject_type'] == 'Applied' ? 'selected' : '' ?>>Applied</option>
                <option value="Specialized" <?php echo isset($meta['subject_type']) && $meta['subject_type'] == 'Specialized' ? 'selected' : '' ?>>Specialized</option>
            </select>
        </div>
        <div class="form-group">
            <label class="control-label">Strand</label>
            <select name="strand_id[]" class="form-control select2" multiple="multiple">
                <?php 
                $strands = $conn->query("SELECT * FROM strands ORDER BY name ASC");
                while($row = $strands->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" <?php echo isset($strand_ids) && in_array($row['id'], $strand_ids) ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <small class="text-muted">You can select multiple strands</small>
        </div>
                <!-- Form buttons moved to uni_modal footer -->
    </form>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select strands",
            width: '100%'
        });
        
        // Add the buttons to uni_modal footer
        $('.modal-footer').addClass('display');
        $('.modal-footer').html('<button type="submit" form="manage-strand-subject" class="btn btn-primary">Save</button><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>');
    });
    
    $('#manage-strand-subject').submit(function(e) {
        e.preventDefault();
        
        // Get form values
        var subject = $('[name="subject"]').val().trim();
        var units = $('[name="units"]').val().trim();
        var subject_type = $('[name="subject_type"]').val();
        
        // Validate empty fields
        if(subject === '' || units === '' || subject_type === '') {
            $('#msg').html('<div class="alert alert-danger">Please fill in all required fields</div>');
            return false;
        }
        
        // Validate that units is a positive number
        if(isNaN(units) || parseInt(units) <= 0) {
            $('#msg').html('<div class="alert alert-danger">Units must be a positive number</div>');
            return false;
        }
        
        start_load();
        
        // Get selected strands as array
        var selectedStrands = $(this).find("[name='strand_id[]']").val();
        
        // Create a FormData object to handle arrays properly
        var formData = new FormData($(this)[0]);
        
        $.ajax({
            url: 'ajax.php?action=save_strand_subject',
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
                    if (resp == 1) {
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
