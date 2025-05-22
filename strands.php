<?php include 'db_connect.php'; ?>

<?php
// Fetch strands with optimized query
$strands = $conn->query("SELECT id, code, name FROM strands ORDER BY name ASC");
if (!$strands) {
    die("Query Failed: " . $conn->error); // Debugging line to check for query errors
}
?>

<head>
    <link rel="stylesheet" href="assets/css/datatable-fullwidth.css">
</head>

<div class="container-fluid p-0">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4><b>Strand List</b></h4>
                <button class="btn btn-primary btn-sm float-right" id="new_strand"><i class="fa fa-plus"></i> New Strand
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100" id="strand-table">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">#</th>
                                <th class="text-center" width="20%">Strand Code</th>
                                <th class="text-center" width="55%">Strand Name</th>
                                <th class="text-center" width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1; // Initialize the row counter
                            while ($row = $strands->fetch_assoc()):
                            ?>
                            <tr data-id="<?php echo $row['id']; ?>">
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($row['code']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary edit_strand" data-id="<?php echo $row['id']; ?>">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger delete_strand" data-id="<?php echo $row['id']; ?>">
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

<!-- Modal Container -->
<div class="modal fade" id="uni_modal" role="dialog">
    <div class="modal-dialog modal-md" role="document">
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

<!-- Confirmation Modal -->
<div class="modal fade" id="confirm_modal" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="delete_content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/datatable_init.js"></script>
<script>
    $(document).ready(function() {
        initializeDataTable('#strand-table');
    });

    $('#new_strand').click(function() {
        uni_modal("New Strand", "manage_strand.php", "modal-md");
    });

    $('.edit_strand').click(function() {
        uni_modal("Edit Strand", "manage_strand.php?id=" + $(this).attr('data-id'), "modal-md");
    });

    $('.delete_strand').click(function() {
        _conf("Are you sure to delete this strand?", "delete_strand", [$(this).attr('data-id')]);
    });

    function delete_strand(id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_strand',
            method: 'POST',
            data: {id: id},
            success: function(resp) {
                try {
                    // Try to parse response as JSON first
                    console.log("Response:", resp);
                    var response = JSON.parse(resp);
                    if (response.status === 0) {
                        alert_toast(response.message, 'danger');
                    } else {
                        alert_toast("Strand successfully deleted", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                } catch(e) {
                    console.error("JSON parse error:", e);
                    console.error("Response:", resp);
                    // If not JSON, handle the original response
                    if (resp.trim() == '1') {
                        alert_toast("Strand successfully deleted", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast("An error occurred while deleting", 'danger');
                    }
                }
                end_load();
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response:", xhr.responseText);
                alert_toast("An error occurred: " + error, 'danger');
                end_load();
            }
        });
    }

    function _conf(msg='', func='', params = []) {
        $('#confirm_modal #confirm').attr('onclick', func + "(" + params.join(',') + ")");
        $('#confirm_modal #delete_content').html(msg);
        $('#confirm_modal').modal('show');
    }

    function uni_modal(title, url, size = '') {
        console.log("Opening modal:", title, url, size);
        $('#uni_modal .modal-title').html(title);
        
        // Clear modal body first
        $('#uni_modal .modal-body').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Loading...</p></div>');
        
        // Set modal size before content loads
        $('#uni_modal .modal-dialog').removeClass('modal-sm modal-lg modal-md');
        if(size !== '') {
            $('#uni_modal .modal-dialog').addClass(size);
        } else {
            $('#uni_modal .modal-dialog').addClass('modal-md');
        }
        
        // Show modal with loading indicator
        $('#uni_modal').modal('show');
        
        // Load content after modal is shown
        setTimeout(function() {
            $('#uni_modal .modal-body').load(url, function(response, status, xhr) {
                if (status == "error") {
                    $('#uni_modal .modal-body').html("<div class='alert alert-danger'>Error loading content: " + xhr.status + " " + xhr.statusText + "</div>");
                    console.error("Error loading modal content:", xhr.status, xhr.statusText);
                }
            });
        }, 300);
    }
</script>

<style>
    td {
        vertical-align: middle !important;
    }
    .card {
        width: 100%;
    }
    .dataTables_wrapper {
        width: 100%;
    }
    .btn {
        margin: 2px;
    }
</style>
