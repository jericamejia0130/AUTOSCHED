<?php include 'db_connect.php' ?>
<?php
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM faculty WHERE id = " . $_GET['id']);
    foreach ($qry->fetch_array() as $k => $v) {
        $$k = $v;
    }
}
?>
<head>
    <link rel="stylesheet" href="assets/css/form-styles.css">
</head>
<div class="container-fluid">
	<form action="" id="manage-faculty" enctype="multipart/form-data">
		<div id="msg"></div>
		<input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>" class="form-control">
		<div class="row form-group">
			<div class="col-md-4">
				<label class="control-label">ID No.</label>
				<input type="text" name="id_no" class="form-control" value="<?php echo isset($id_no) ? $id_no : '' ?>">
				<small><i>Leave this blank if you want to auto-generate the ID no.</i></small>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-4">
				<label class="control-label">Last Name</label>
				<input type="text" name="lastname" class="form-control" value="<?php echo isset($lastname) ? $lastname:'' ?>" required>
			</div>
			<div class="col-md-4">
				<label class="control-label">First Name</label>
				<input type="text" name="firstname" class="form-control" value="<?php echo isset($firstname) ? $firstname:'' ?>" required>
			</div>
			<div class="col-md-4">
				<label class="control-label">Middle Name</label>
				<input type="text" name="middlename" class="form-control" value="<?php echo isset($middlename) ? $middlename:'' ?>">
			</div>
		</div>
		
		<div class="form-group">
			<label for="designation" class="control-label">Designation</label>
			<select name="designation_id" id="designation" class="form-control" required>
				<option value="">Select Designation</option>
				<?php
				$designations = $conn->query("SELECT * FROM designations ORDER BY designation ASC");
				while ($row = $designations->fetch_assoc()):
				?>
				<option value="<?php echo $row['id'] ?>" <?php echo isset($designation_id) && $designation_id == $row['id'] ? 'selected' : '' ?>>
					<?php echo $row['designation'] ?>
				</option>
				<?php endwhile; ?>
			</select>
		</div>
		<div class="row form-group">
			<div class="col-md-4">
				<label class="control-label">Upload Image</label>
				<input type="file" name="image" class="form-control" accept="image/*">
			</div>
		</div>
	</form>
</div>

<script>
    $('#manage-faculty').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#msg').html('');
        var formData = new FormData(this);
        $.ajax({
            url: 'ajax.php?action=save_faculty',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(resp) {
                try {
                    // Try to parse as JSON first
                    var response = JSON.parse(resp);
                    if(response.status === 0) {
                        $('#msg').html('<div class="alert alert-danger">' + response.message + '</div>');
                    } else {
                        alert_toast("Data successfully saved", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                } catch(e) {
                    // If not JSON, handle as before
                    if (resp == 1) {
                        alert_toast("Data successfully saved", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else if (resp == 2) {
                        $('#msg').html('<div class="alert alert-danger">ID No already exists.</div>');
                    } else {
                        $('#msg').html('<div class="alert alert-danger">An error occurred.</div>');
                    }
                }
                end_load();
            },
            error: function(xhr, status, error) {
                $('#msg').html('<div class="alert alert-danger">An error occurred: ' + error + '</div>');
                end_load();
            }
        });
    });
</script>