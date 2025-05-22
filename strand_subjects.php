<?php include('db_connect.php');?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4><b>Strand Subjects List</b></h4>
                <button class="btn btn-primary btn-sm float-right" id="new_strand_subject"><i class="fa fa-plus"></i> New Strand Subject</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Subject</th>
                                <th class="text-center">Units</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Strand</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            $strand_subjects = $conn->query("SELECT s.* FROM strand_subjects s ORDER BY subject ASC");
                            while($row = $strand_subjects->fetch_assoc()):
                                // Get strands for this subject
                                $strand_query = $conn->query("SELECT ss.strand_id, st.name FROM subject_strands ss 
                                                            JOIN strands st ON ss.strand_id = st.id
                                                            WHERE ss.subject_id = " . $row['id']);
                                $strands = [];
                                $strand_ids = [];
                                while($strand = $strand_query->fetch_assoc()) {
                                    $strands[] = $strand['name'];
                                    $strand_ids[] = $strand['strand_id'];
                                }
                                $strand_list = implode(', ', $strands);
                                $strand_id_list = implode(',', $strand_ids);
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $i++ ?></td>
                                <td class="text-center"><?php echo $row['subject'] ?></td>
                                <td class="text-center"><?php echo $row['units'] ?></td>
                                <td class="text-center"><?php echo $row['subject_type'] ?></td>
                                <td class="text-center"><?php echo !empty($strand_list) ? $strand_list : 'N/A' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary edit_strand_subject" type="button" 
                                        data-id="<?php echo $row['id'] ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger delete_strand_subject" type="button" 
                                        data-id="<?php echo $row['id'] ?>">Delete</button>
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

<script>
    $(document).ready(function() {
        $('table').DataTable();
    });
    
    $('#new_strand_subject').click(function() {
        uni_modal("New Strand Subject", "manage_strand_subject.php");
    });
    
    $('.edit_strand_subject').click(function() {
        uni_modal("Edit Strand Subject", "manage_strand_subject.php?id=" + $(this).attr('data-id'));
    });
    
    $('.delete_strand_subject').click(function() {
        _conf("Are you sure to delete this strand subject?", "delete_strand_subject", [$(this).attr('data-id')]);
    });
    
    function delete_strand_subject(id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_strand_subject',
            method: 'POST',
            data: {id: id},
            dataType: 'json', // Explicitly expect JSON
            success: function(resp) {
                if (resp.status === 0) {
                    alert_toast(resp.message || "Failed to delete subject", 'danger');
                } else {
                    alert_toast("Data successfully deleted", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                }
                end_load();
            },
            error: function(xhr, status, error) {
                try {
                    var errorResp = JSON.parse(xhr.responseText);
                    alert_toast(errorResp.message || "An error occurred", 'danger');
                } catch (e) {
                    alert_toast("An error occurred: " + error, 'danger');
                }
                end_load();
            }
        });
    }
</script>
