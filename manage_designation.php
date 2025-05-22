<?php include 'db_connect.php' ?>
<?php
if(isset($_GET['id'])){
    $designation = $conn->query("SELECT * FROM designations WHERE id = ".$_GET['id']);
    if($designation->num_rows > 0){
        foreach($designation->fetch_array() as $k => $v){
            $meta[$k] = $v;
        }
    }
}
?>

<div class="container-fluid">
    <form action="" id="manage-designation">
        <div id="msg"></div>
        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
        <div class="form-group">
            <label class="control-label">Designation</label>
            <input type="text" name="designation" class="form-control" value="<?php echo isset($meta['designation']) ? $meta['designation'] : '' ?>" required>
            <small class="text-muted">Examples: Full Time, Part Time, Department Head, etc.</small>
        </div>
        <div class="form-group text-right">
            <button class="btn btn-primary mr-2" type="submit">Save</button>
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
        </div>
    </form>
</div>

<script>
    $('#manage-designation').submit(function(e){
        e.preventDefault();
        
        // Get form values
        var designation = $('[name="designation"]').val();
        
        // Validate empty fields
        if(designation === '') {
            $('#msg').html('<div class="alert alert-danger">Please select a designation</div>');
            return false;
        }
        
        start_load();
        $.ajax({
            url:'ajax.php?action=save_designation',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            success:function(resp){
                if(resp==1){
                    alert_toast("Data successfully added",'success');
                    setTimeout(function(){
                        location.reload();
                    },1500);
                }
                else if(resp==2){
                    alert_toast("Data successfully updated",'success');
                    setTimeout(function(){
                        location.reload();
                    },1500);
                } 
                else if(resp==3) {
                    alert_toast("Designation already exists",'danger');
                    $('#msg').html('<div class="alert alert-danger">Designation already exists</div>');
                }
                else {
                    alert_toast("An error occurred",'danger');
                    $('#msg').html('<div class="alert alert-danger">An error occurred</div>');
                }
                end_load();
            },
            error: function() {
                alert_toast("An error occurred",'danger');
                $('#msg').html('<div class="alert alert-danger">An error occurred</div>');
                end_load();
            }
        });
    });
</script> 