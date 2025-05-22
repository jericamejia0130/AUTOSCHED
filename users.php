<?php 
include 'db_connect.php';
// Add a timestamp parameter to force a fresh query and avoid caching
$timestamp = time();
$qry = $conn->query("SELECT u.id, u.username, u.password, u.profile_image 
                     FROM users u 
                     WHERE u.type = 'admin'
                     ORDER BY u.username ASC");
?>

<head>
    <link rel="stylesheet" href="assets/css/datatable-fullwidth.css">
</head>

<div class="container-fluid p-0">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0"><b>Admin User List</b></h4>
            <button class="btn btn-primary btn-sm" id="new_user"><i class="fa fa-plus"></i> New Admin</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="user-table">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Profile</th>
                            <th class="text-center">Username</th>
                            <th class="text-center">Password</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $qry->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $row['id']; ?></td>
                            <td class="text-center">
                                <img src="<?php echo isset($row['profile_image']) && !empty($row['profile_image']) ? 'assets/uploads/'.$row['profile_image'].'?v='.time() : 'assets/uploads/default.png'; ?>" 
                                     class="img-thumbnail rounded-circle admin-image" alt="Admin Profile">
                            </td>
                            <td><?php echo $row['username']; ?></td>
                            <td>
                                <div class="input-group">
                                    <input type="password" class="form-control user-password" value="********" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text toggle-pwd" style="cursor: pointer;">
                                            <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <center>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary">Action</button>
                                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item edit_user" href="javascript:void(0)" data-id="<?php echo $row['id']; ?>">Edit</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item delete_user" href="javascript:void(0)" data-id="<?php echo $row['id']; ?>">Delete</a>
                                        </div>
                                    </div>
                                </center>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .card-header {
        padding: 0.75rem 1.25rem;
    }
    
    #user-table {
        width: 100% !important;
    }
    
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    
    /* Fix button alignment with table */
    .btn-sm {
        height: 38px;
        display: flex;
        align-items: center;
    }
    
    .user-password {
        background-color: #f8f9fa;
        cursor: default;
    }
    .input-group-text {
        height: 100%;
        display: flex;
        align-items: center;
    }
    .toggle-pwd {
        background-color: #e9ecef;
        border-color: #ced4da;
    }
    .toggle-pwd:hover {
        background-color: #d8dde2;
    }
    .admin-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border: 2px solid #ddd;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    .admin-image:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>

<script src="assets/js/datatable_init.js"></script>
<script>
    $(document).ready(function() {
        initializeDataTable('#user-table');
        
        // Password visibility toggle
        $(document).on('click', '.toggle-pwd', function() {
            var passwordInput = $(this).closest('.input-group').find('input');
            var icon = $(this).find('i');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
        
        // Refresh page when modal is hidden to show updated data
        $(document).on('hidden.bs.modal', '#uni_modal', function() {
            location.reload();
        });
    });

    $('#new_user').click(function(){
        uni_modal('New Admin User','manage_user.php?mtype=admin')
    })
    
    $('.edit_user').click(function(){
        var userId = $(this).attr('data-id');
        if (!userId) {
            alert_toast("Error: User ID not found", 'error');
            return;
        }
        // Add cache-busting timestamp to URL to avoid cached content
        uni_modal('Edit Admin User','manage_user.php?id=' + userId + '&mtype=admin&t=<?php echo $timestamp; ?>');
    })
    
    $('.delete_user').click(function(){
        var userId = $(this).attr('data-id');
        if (!userId) {
            alert_toast("Error: User ID not found", 'error');
            return;
        }
        _conf("Are you sure to delete this user?", "delete_user", [userId]);
    })
    
    function delete_user($id){
        if (!$id) {
            alert_toast("Error: Invalid user ID", 'error');
            return;
        }
        start_load()
        $.ajax({
            url:'ajax.php?action=delete_user',
            method:'POST',
            data:{id:$id},
            success:function(resp){
                if(resp==1){
                    alert_toast("Data successfully deleted",'success')
                    setTimeout(function(){
                        location.reload()
                    },1500)
                } else {
                    alert_toast("Error deleting user",'error')
                }
                end_load()
            },
            error: function(err){
                console.log(err)
                alert_toast("An error occurred",'error')
                end_load()
            }
        })
    }

    // Improved modal function with error handling
    function uni_modal(title, url, size = '') {
        if (!url) {
            console.error("URL is required for modal");
            return;
        }
        
        // Clear any existing modal content first
        $('#uni_modal .modal-title').html('');
        $('#uni_modal .modal-body').html('');
        
        start_load();
        $.ajax({
            url: url,
            error: function(err) {
                console.log(err);
                alert_toast("An error occurred loading the modal", 'error');
                end_load();
            },
            success: function(resp) {
                if (resp) {
                    $('#uni_modal .modal-title').html(title);
                    $('#uni_modal .modal-body').html(resp);
                    if (size) {
                        $('#uni_modal .modal-dialog').addClass(size);
                    } else {
                        $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md");
                    }
                    $('#uni_modal').modal({
                        show: true,
                        backdrop: 'static',
                        keyboard: false,
                        focus: true
                    });
                    
                    // Ensure the modal is fully initialized
                    setTimeout(function() {
                        $('#uni_modal').trigger('modal-loaded');
                    }, 100);
                    
                } else {
                    alert_toast("Error: Empty response from server", 'error');
                }
                end_load();
            }
        });
    }
</script>