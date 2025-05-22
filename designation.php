<?php include('db_connect.php'); ?>
<?php
$designations = $conn->query("SELECT d.*, 
                        (SELECT COUNT(*) FROM faculty f WHERE f.designation_id = d.id) as faculty_count
                        FROM designations d 
                        ORDER BY d.designation ASC");
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4><b>Designation List</b></h4>
                <button class="btn btn-primary btn-sm float-right" id="new_designation"><i class="fa fa-plus"></i> New Designation</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Designation</th>
                                <th class="text-center">Faculty Count</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            while($row = $designations->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $i++ ?></td>
                                <td><?php echo $row['designation'] ?></td>
                                <td class="text-center"><?php echo $row['faculty_count'] ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info view_faculty" type="button" 
                                        data-id="<?php echo $row['id'] ?>" 
                                        data-designation="<?php echo $row['designation'] ?>">
                                        <i class="fa fa-users"></i> View Faculty
                                    </button>
                                    <button class="btn btn-sm btn-primary edit_designation" type="button" 
                                        data-id="<?php echo $row['id'] ?>">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger delete_designation" type="button" 
                                        data-id="<?php echo $row['id'] ?>">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uni_modal" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('table').DataTable();
    });
    
    $('#new_designation').click(function() {
        uni_modal("New Designation", "manage_designation.php");
    });
    
    $('.edit_designation').click(function() {
        uni_modal("Edit Designation", "manage_designation.php?id=" + $(this).attr('data-id'));
    });
    
    $('.delete_designation').click(function(){
        _conf("Are you sure to delete this designation?","delete_designation",[$(this).attr('data-id')])
    });

    function delete_designation($id){
        start_load()
        $.ajax({
            url:'ajax.php?action=delete_designation',
            method:'POST',
            data:{id:$id},
            success:function(resp){
                try {
                    // Try to parse response as JSON first
                    var response = JSON.parse(resp);
                    if (response.status === 0) {
                        alert_toast(response.message, 'danger');
                    } else {
                        alert_toast("Data successfully deleted", 'success');
                        setTimeout(function(){
                            location.reload()
                        },1500);
                    }
                } catch(e) {
                    // If not JSON, handle the original response
                    if(resp==1){
                        alert_toast("Data successfully deleted",'success')
                        setTimeout(function(){
                            location.reload()
                        },1500)
                    } else {
                        alert_toast("An error occurred",'danger')
                    }
                }
                end_load()
            },
            error: function(xhr, status, error) {
                alert_toast("An error occurred: " + error, 'danger');
                end_load();
            }
        })
    }

    $('.view_faculty').click(function(){
        var id = $(this).attr('data-id');
        var designation = $(this).attr('data-designation');
        start_load();
        $.ajax({
            url: 'ajax.php?action=get_faculty_by_designation',
            method: 'POST',
            data: {designation_id: id},
            dataType: 'html',
            success: function(response){
                end_load();
                var modalContent = `
                    <div class="container-fluid">
                        <div class="faculty-list">
                            ${response}
                        </div>
                    </div>
                `;
                $('#uni_modal .modal-title').html(designation + " Faculty Members");
                $('#uni_modal .modal-body').html(modalContent);
                $('#uni_modal .modal-dialog').addClass('large');
                $('#uni_modal').modal('show');
            },
            error: function(xhr, status, error){
                end_load();
                alert_toast("An error occurred while fetching data: " + error, 'danger');
            }
        });
    });

    function uni_modal(title, content, size = '') {
        $('#uni_modal .modal-title').html(title);
        $('#uni_modal .modal-body').html(content);
        if (size != '') {
            $('#uni_modal .modal-dialog').addClass(size);
        } else {
            $('#uni_modal .modal-dialog').removeClass('modal-lg modal-sm');
        }
        $('#uni_modal').modal('show');
    }
</script>

<style>
    td {
        vertical-align: middle !important;
    }
    .faculty-list {
        max-height: 70vh;
        overflow-y: auto;
    }
    .faculty-list table {
        width: 100%;
        margin-bottom: 0;
    }
    .faculty-list th, 
    .faculty-list td {
        padding: 8px;
        vertical-align: middle;
    }
    .modal-dialog.large {
        width: 80%;
        max-width: 800px;
    }
    .btn {
        margin: 2px;
    }
    .faculty-img {
        max-width: 50px;
        max-height: 50px;
        object-fit: cover;
        border-radius: 50%;
    }
</style>