<?php include('db_connect.php');?>

<head>
    <link rel="stylesheet" href="assets/css/datatable-fullwidth.css">
</head>

<div class="container-fluid p-0">
    <div class="card">
        <div class="card-header">
            <h4><b>Department Subjects</b></h4>
            <button class="btn btn-primary btn-sm float-right" id="new_subject">
                <i class="fa fa-plus"></i> New Subject
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="subject-table">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">#</th>
                            <th class="text-center" width="25%">Subject</th>
                            <th class="text-center" width="10%">Units</th>
                            <th class="text-center" width="15%">Type</th>
                            <th class="text-center" width="25%">Department</th>
                            <th class="text-center" width="20%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                                    <?php 
                                    $i = 1;
                                    // Try using GROUP_CONCAT first
                                    $subject_query = "SELECT s.*, 
                                                   GROUP_CONCAT(c.course SEPARATOR ', ') as department_list,
                                                   GROUP_CONCAT(sd.department_id SEPARATOR ',') as department_ids
                                                   FROM subjects s 
                                                   LEFT JOIN subject_departments sd ON s.id = sd.subject_id
                                                   LEFT JOIN courses c ON sd.department_id = c.id
                                                   GROUP BY s.id 
                                                   ORDER BY s.id ASC";
                                    $subject = $conn->query($subject_query);
                                    
                                    // If the GROUP_CONCAT query fails, use a fallback approach
                                    if (!$subject) {
                                        // Fallback to simple query if GROUP_CONCAT is not supported
                                        $fallback_query = "SELECT * FROM subjects ORDER BY id ASC";
                                        $subject = $conn->query($fallback_query);
                                        
                                        if (!$subject) {
                                            echo "<tr><td colspan='6'>Error: " . $conn->error . "</td></tr>";
                                        } else {
                                            // Manual approach for each subject
                                            while ($row = $subject->fetch_assoc()):
                                                // Get departments for this subject
                                                $dept_query = $conn->query("SELECT sd.department_id, c.course 
                                                                        FROM subject_departments sd 
                                                                        JOIN courses c ON sd.department_id = c.id
                                                                        WHERE sd.subject_id = " . $row['id']);
                                                
                                                $departments = [];
                                                $department_ids = [];
                                                
                                                if($dept_query && $dept_query->num_rows > 0) {
                                                    while($dept = $dept_query->fetch_assoc()) {
                                                        $departments[] = $dept['course'];
                                                        $department_ids[] = $dept['department_id'];
                                                    }
                                                }
                                                
                                                $department_list = implode(', ', $departments);
                                                $department_ids = implode(',', $department_ids);
                                                ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $i++ ?></td>
                                                    <td class="text-center"><?php echo $row['subject'] ?></td>
                                                    <td class="text-center"><?php echo $row['units'] ?></td>
                                                    <td class="text-center"><?php echo $row['type'] ?></td>
                                                    <td class="text-center"><?php echo !empty($department_list) ? $department_list : 'N/A' ?></td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-primary edit_subject" type="button" 
                                                            data-id="<?php echo $row['id'] ?>" 
                                                            data-subject="<?php echo $row['subject'] ?>"
                                                            data-units="<?php echo $row['units'] ?>" 
                                                            data-department-id="<?php echo htmlspecialchars($department_ids) ?>"
                                                            data-type="<?php echo $row['type'] ?>">Edit</button>
                                                        <button class="btn btn-sm btn-danger delete_subject" type="button" 
                                                            data-id="<?php echo $row['id'] ?>">Delete</button>
                                                    </td>
                                                </tr>
                                            <?php endwhile;
                                        }
                                    } else {
                                        // Use the GROUP_CONCAT results
                                        while ($row = $subject->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td class="text-center"><?php echo $row['subject'] ?></td>
                                        <td class="text-center"><?php echo $row['units'] ?></td>
                                        <td class="text-center"><?php echo $row['type'] ?></td>
                                        <td class="text-center"><?php echo !empty($row['department_list']) ? $row['department_list'] : 'N/A' ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary edit_subject" type="button" 
                                                data-id="<?php echo $row['id'] ?>" 
                                                data-subject="<?php echo $row['subject'] ?>"
                                                data-units="<?php echo $row['units'] ?>" 
                                                data-department-id="<?php echo htmlspecialchars($row['department_ids']) ?>"
                                                data-type="<?php echo $row['type'] ?>">Edit</button>
                                            <button class="btn btn-sm btn-danger delete_subject" type="button" 
                                                data-id="<?php echo $row['id'] ?>">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endwhile; } ?>
                                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        initializeDataTable('#subject-table', {
            scrollX: true,
            fixedHeader: true,
            autoWidth: false,
            responsive: true
        });
    });
    
    $('#new_subject').click(function() {
        uni_modal("New Subject", "manage_subject.php");
    });

    $('.edit_subject').click(function() {
        var id = $(this).data('id');
        uni_modal("Edit Subject", "manage_subject.php?id=" + id);
    });

    $('.delete_subject').click(function() {
        _conf("Are you sure to delete this subject?", "delete_subject", [$(this).data('id')]);
    });

    function delete_subject(id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_subject',
            method: 'POST',
            data: {id: id},
            success: function(resp) {
                try {
                    // Try to parse response as JSON first
                    var response = JSON.parse(resp);
                    if (response.status === 0) {
                        alert_toast(response.message, 'danger');
                    } else {
                        alert_toast("Data successfully deleted", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                } catch (e) {
                    // If not JSON, handle the original response
                    if (resp == 1) {
                        alert_toast("Data successfully deleted", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast("An error occurred", 'danger');
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

<style>
/* This will ensure your table fits the full width */
.table-responsive {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

.table {
    width: 100% !important;
    margin-bottom: 0 !important;
}

/* Fix column widths */
#subject-table th,
#subject-table td {
    white-space: normal;
    word-break: break-word;
    vertical-align: middle !important;
}

/* Ensure action buttons are properly aligned */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 5px;
    flex-wrap: nowrap;
}

.btn {
    margin: 2px;
}

/* Fix card padding */
.card-body {
    padding: 15px !important;
}

/* Force full width for DataTables wrapper */
.dataTables_wrapper {
    width: 100% !important;
    margin: 0 !important;
}

.dataTables_wrapper .row {
    margin: 0 !important;
    width: 100% !important;
}

</style>