<?php 
include('db_connect.php');
session_start();

// Initialize $meta as an empty array to prevent undefined variable errors
$meta = array();

if(isset($_GET['id'])){
    $user = $conn->query("SELECT * FROM users where id =".$_GET['id']);
    foreach($user->fetch_array() as $k =>$v){
        $meta[$k] = $v;
    }
}
?>

<div class="container-fluid">
    <div id="msg"></div>
    
    <style>
        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            overflow: hidden;
            border-radius: 50%;
            border: 2px solid #ddd;
            cursor: pointer;
        }
        
        .profile-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: hidden;
            height: 0;
            transition: .3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .profile-image-container:hover .profile-image-overlay {
            height: 40px;
        }
        
        .upload-icon {
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin: 0;
        }
    </style>
    
    <form action="" id="manage-user" enctype="multipart/form-data">	
        <input type="hidden" name="id" value="<?php echo isset($meta['id']) ? $meta['id']: '' ?>">
        <!-- Always set type to Admin -->
        <input type="hidden" name="type" value="Admin">
        
        <!-- User Profile Container -->
        <div class="form-group text-center">
            <label for="profile_image">Profile Image</label>
            <div class="d-flex justify-content-center mb-3">
                <div class="profile-image-container">
                    <img src="<?php echo isset($meta['profile_image']) && !empty($meta['profile_image']) ? 'assets/uploads/'.$meta['profile_image'].'?v='.time() : 'assets/uploads/default.png'; ?>" 
                         id="profilePreview" class="rounded-circle" alt="Profile Image">
                    <div class="profile-image-overlay">
                        <label for="profile_image" class="upload-icon">
                            <i class="fa fa-camera"></i>
                        </label>
                    </div>
                </div>
            </div>
            <input type="file" name="profile_image" id="profile_image" class="form-control-file d-none" accept="image/*" onchange="previewImage(event)">
            <small class="form-text text-muted">Click the image to change your profile picture</small>
        </div>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($meta['username']) ? $meta['username']: '' ?>" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" <?php echo isset($meta['id']) ? '': 'required' ?>>
                <div class="input-group-append">
                    <span class="input-group-text toggle-pwd" style="cursor: pointer;">
                        <i class="fa fa-eye-slash" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
            <?php if(isset($meta['id'])): ?>
            <small><i>Leave this blank if you don't want to change the password.</i></small>
            <?php endif; ?>
        </div>
        
        <div id="student_fields" style="display: none;">
            <div class="form-group">
                <label for="course">Course</label>
                <select name="course_id" id="course" class="custom-select">
                    <option value="">Select Course</option>
                    <?php 
                    $courses = $conn->query("SELECT * FROM courses ORDER BY course ASC");
                    while($row = $courses->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>"><?php echo $row['course'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="section">Section</label>
                <select name="section_id" id="section" class="custom-select">
                    <option value="">Select Section</option>
                    <?php 
                    $sections = $conn->query("SELECT * FROM sections ORDER BY section ASC");
                    while($row = $sections->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>"><?php echo $row['section'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<script>
    // Preview the uploaded image
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('profilePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Webcam functionality
    if (typeof openCameraButton === 'undefined') {
        document.addEventListener('DOMContentLoaded', () => {
            const openCameraButton = document.getElementById('openCamera');
            const closeCameraButton = document.getElementById('closeCamera');
            const captureButton = document.getElementById('capture');
            const cameraContainer = document.getElementById('cameraContainer');
            const webcam = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');

            if (openCameraButton && closeCameraButton && captureButton && cameraContainer && webcam && canvas) {
                openCameraButton.addEventListener('click', () => {
                    cameraContainer.style.display = 'block';
                });

                closeCameraButton.addEventListener('click', () => {
                    cameraContainer.style.display = 'none';
                });

                captureButton.addEventListener('click', () => {
                    const context = canvas.getContext('2d');
                    context.drawImage(webcam, 0, 0, canvas.width, canvas.height);
                });
            }
        });
    }

    $(document).ready(function(){
        // Password visibility toggle - using document.on instead of direct binding
        $(document).on('click', '.toggle-pwd', function() {
            const passwordInput = $(this).closest('.input-group').find('input');
            const icon = $(this).find('i');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });

        $('#manage-user').submit(function(e){
            e.preventDefault();
            start_load()
            const formData = new FormData(this);
            $.ajax({
                url: 'ajax.php?action=save_user',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(resp) {
                    if (resp == 1) {
                        alert_toast("Data successfully saved", 'success');
                        setTimeout(function() {
                            // Close the modal
                            $('#uni_modal').modal('hide');
                            
                            // Check if user was editing their own account
                            const userId = '<?php echo isset($meta["id"]) ? $meta["id"] : "" ?>';
                            const loginId = '<?php echo isset($_SESSION["login_id"]) ? $_SESSION["login_id"] : "" ?>';
                            
                            if (userId === loginId) {
                                // If user edited their own account, refresh the entire page with a cache-busting parameter
                                // This ensures both the topbar and the users table are updated
                                const cacheBuster = new Date().getTime();
                                if (window.location.href.includes('index.php?page=users')) {
                                    window.location.href = 'index.php?page=users&t=' + cacheBuster;
                                } else {
                                    // If not already on users page, force redirect to it
                                    window.location.href = 'index.php?page=users&t=' + cacheBuster;
                                }
                            } else {
                                // If editing another user, always redirect to users page with cache-busting
                                const cacheBuster = new Date().getTime();
                                window.location.href = 'index.php?page=users&t=' + cacheBuster;
                            }
                        }, 1500);
                    } else if (resp == 2) {
                        alert_toast("Username already exists", 'error');
                        end_load();
                    } else {
                        alert_toast("An error occurred", 'error');
                        console.log("Server response:", resp);
                        end_load();
                    }
                },
                error: function(err) {
                    console.log(err);
                    alert_toast("An error occurred", 'error');
                    end_load();
                }
            });
        });
    });
</script>