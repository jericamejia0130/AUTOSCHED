<?php include 'db_connect.php' ?>

<head>
    <link rel="stylesheet" href="assets/css/datatable_styles.css">
</head>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4><b>Rooms & Laboratories</b></h4>
                <button class="btn btn-primary btn-sm float-right" id="new_room"><i class="fa fa-plus"></i> New Room</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Room Name</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $rooms = $conn->query("SELECT * FROM rooms ORDER BY id ASC");
                            while ($row = $rooms->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $i++ ?></td>
                                <td><?php echo $row['name'] ?></td>
                                <td><?php echo $row['type'] ?></td>
                                <td><?php echo $row['capacity'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit_room" data-id="<?php echo $row['id'] ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger delete_room" data-id="<?php echo $row['id'] ?>">Delete</button>
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

<script src="assets/js/datatable_init.js"></script>
<script>
    $(document).ready(function() {
        initializeDataTable('.table');
    });
    
    $('#new_room').click(function() {
        uni_modal("New Room", "manage_room.php");
    });
    
    $('.edit_room').click(function() {
        uni_modal("Edit Room", "manage_room.php?id=" + $(this).data('id'));
    });
    
    $('.delete_room').click(function() {
        _conf("Are you sure you want to delete this room?", "delete_room", [$(this).data('id')]);
    });
    
    function delete_room(id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_room',
            method: 'POST',
            data: { id: id },
            success: function(resp) {
                try {
                    // Try to parse response as JSON first
                    var response = JSON.parse(resp);
                    if (response.status === 0) {
                        alert_toast(response.message, 'danger');
                    } else {
                        alert_toast('Room deleted successfully!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                } catch (e) {
                    // If not JSON, handle the original response
                    if (resp == 1) {
                        alert_toast('Room deleted successfully!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast('An error occurred', 'danger');
                    }
                }
                end_load();
            },
            error: function(xhr, status, error) {
                alert_toast("An error occurred: " + error, 'danger');
                end_load();
            }
        });
    }
</script>