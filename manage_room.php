<?php include 'db_connect.php' ?>
<?php
if(isset($_GET['id'])){
    $room = $conn->query("SELECT * FROM rooms WHERE id = ".$_GET['id']);
    if($room->num_rows > 0){
        foreach($room->fetch_array() as $k => $v){
            $meta[$k] = $v;
        }
    }
}
?>

<head>
    <link rel="stylesheet" href="assets/css/form-styles.css">
</head>

<div class="container-fluid">
    <form action="" id="manage-room">
        <div id="msg"></div>
        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
        <div class="form-group">
            <label for="name">Room Name</label>
            <input type="text" class="form-control" name="name" value="<?php echo isset($meta['name']) ? $meta['name'] : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <select class="form-control" name="type" required>
                <option value="">Select Room Type</option>
                <option value="Classroom" <?php echo isset($meta['type']) && $meta['type'] == 'Classroom' ? 'selected' : '' ?>>Classroom</option>
                <option value="Laboratory" <?php echo isset($meta['type']) && $meta['type'] == 'Laboratory' ? 'selected' : '' ?>>Laboratory</option>
            </select>
        </div>
        <div class="form-group">
            <label for="capacity">Room Capacity</label>
            <input type="number" class="form-control" name="capacity" value="<?php echo isset($meta['capacity']) ? $meta['capacity'] : '' ?>" required>
        </div>
    </form>
</div>

<script>
    $('#manage-room').submit(function(e) {
        e.preventDefault();
        
        // Get form values
        var name = $('[name="name"]').val().trim();
        var type = $('[name="type"]').val();
        var capacity = $('[name="capacity"]').val().trim();
        
        // Validate empty fields
        if(name === '' || type === '' || capacity === '') {
            $('#msg').html('<div class="alert alert-danger">Please fill in all required fields</div>');
            return false;
        }
        
        // Validate that capacity is a positive number
        if(isNaN(capacity) || parseInt(capacity) <= 0) {
            $('#msg').html('<div class="alert alert-danger">Capacity must be a positive number</div>');
            return false;
        }
        
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_room',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                try {
                    var result = JSON.parse(resp);
                    if (result.status == 1) {
                        alert_toast(result.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast(result.message, 'danger');
                        $('#msg').html('<div class="alert alert-danger">' + result.message + '</div>');
                    }
                } catch(e) {
                    if (resp == 1) {
                        alert_toast("Room saved successfully", 'success');
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