<?php include('db_connect.php'); ?>
<?php
$faculty = $conn->query("SELECT f.*, d.designation, CONCAT(f.lastname, ', ', f.firstname, ' ', COALESCE(f.middlename,'')) as name 
                        FROM faculty f 
                        LEFT JOIN designations d ON f.designation_id = d.id 
                        ORDER BY f.lastname ASC");
if (!$faculty) {
    die("Query Failed: " . $conn->error); // Debugging line to check for query errors
}
?>
<div class="container-fluid">
<style>
	input[type=checkbox]
{
  /* Double-sized Checkboxes */
  -ms-transform: scale(1.5); /* IE */
  -moz-transform: scale(1.5); /* FF */
  -webkit-transform: scale(1.5); /* Safari and Chrome */
  -o-transform: scale(1.5); /* Opera */
  transform: scale(1.5);
  padding: 10px;
}
</style>
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				
			</div>
		</div>
		<div class="row">
			<!-- FORM Panel -->

			<!-- Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>faculty List</b>
						<span class="">

							<button class="btn btn-primary btn-block btn-sm col-sm-2 float-right" type="button" id="new_faculty">
					<i class="fa fa-plus"></i> New</button>
				</span>
					</div>
					<div class="card-body">
    <table class="table table-bordered table-condensed table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">#</th>
                <th style="width: 10%;">Image</th>
                <th style="width: 15%;">ID No</th>
                <th style="width: 35%;">Name</th>
                <th style="width: 20%;">Designation</th>
                <th style="width: 15%;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            while($row = $faculty->fetch_assoc()):
            ?>
            <tr>
                <td class="text-center"><?php echo $i++ ?></td>
                <td class="text-center">
                    <?php if(!empty($row['image'])): ?>
                        <img src="assets/uploads/<?php echo $row['image'] ?>" class="faculty-img">
                    <?php else: ?>
                        <img src="assets/uploads/default.png" class="faculty-img">
                    <?php endif; ?>
                </td>
                <td><?php echo $row['id_no'] ?></td>
                <td><?php echo ucwords($row['name']) ?></td>
                <td><?php echo $row['designation'] ?></td>
                <td class="text-center">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary edit_faculty" 
                                type="button" data-id="<?php echo $row['id'] ?>">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete_faculty" 
                                type="button" data-id="<?php echo $row['id'] ?>">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
				</div>
			</div>
			<!-- Table Panel -->
		</div>
	</div>	

</div>
<style>
    td {
        vertical-align: middle !important;
        padding: 5px !important;
    }
    td p {
        margin: unset;
    }
    .faculty-img {
        max-width: 50px;
        max-height: 50px;
        object-fit: cover;
        border-radius: 50%;
    }
    .btn-group .btn {
        padding: 3px 8px;
        margin: 0 2px;
    }
    .table td, .table th {
        font-size: 14px;
    }
    .table-condensed td, .table-condensed th {
        padding: 5px;
    }
</style>
<script>
	$(document).ready(function(){
		$('table').dataTable()
	})
	$('#new_faculty').click(function(){
		uni_modal("New Entry","manage_faculty.php",'mid-large')
	})
	$('.view_faculty').click(function(){
		uni_modal("Faculty Details","view_faculty.php?id="+$(this).attr('data-id'),'')
		
	})
	$('.edit_faculty').click(function(){
		uni_modal("Edit Teachers Staff","manage_faculty.php?id="+$(this).attr('data-id'),'mid-large')
		
	})
	$('.delete_faculty').click(function(){
		_conf("Are you sure to delete this topic?","delete_faculty",[$(this).attr('data-id')],'mid-large')
	})

	function delete_faculty($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_faculty',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				try {
					// Try to parse as JSON first
					var response = JSON.parse(resp);
					if(response.status === 0) {
						alert_toast(response.message, 'error');
					} else {
						alert_toast("Data successfully deleted", 'success');
						setTimeout(function(){
							location.reload()
						}, 1500);
					}
				} catch(e) {
					// If not JSON, handle as before
					if(resp == 1){
						alert_toast("Data successfully deleted", 'success');
						setTimeout(function(){
							location.reload()
						}, 1500);
					} else {
						alert_toast("An error occurred", 'error');
					}
				}
				end_load();
			},
			error: function(xhr, status, error) {
				alert_toast("An error occurred: " + error, 'error');
				end_load();
			}
		})
	}
</script>