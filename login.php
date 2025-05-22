<!DOCTYPE html>
<html lang="en">
<?php 
session_start(); 

// If already logged in, redirect to home
if(isset($_SESSION['login_id']) && $_SESSION['login_type'] == 'admin') {
    header("Location: index.php?page=home");
    exit;
}

include('./db_connect.php');

// Clear any existing session data
if(isset($_SESSION['login_id'])) {
    session_unset();
    session_destroy();
}

// Set session timeout
$timeout = 1800; // 30 minutes
if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
}

ob_start();
ob_end_flush();
?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>AutoSched</title>
  
  <?php include('./header.php'); ?>
  <!-- Add Bootstrap Toast CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Add utility JS -->
  <script src="assets/js/utils.js"></script>
</head>
<style>
	body {
		width: 100%;
		height: 100vh;
		margin: 0;
		padding: 0;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-content: flex-end;
	}
	.video-container {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		z-index: -1;
		overflow: hidden;
	}
	.video-container video {
		min-width: 100%;
		min-height: 100%;
		width: auto;
		height: auto;
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		object-fit: cover;
	}
	.video-overlay {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(0, 0, 0, 0.5);
		z-index: -1;
	}
	.login-container {
		width: 25%; /* Reduced from 30% */
		max-width: 350px; /* Reduced from 400px */
		margin-right: 10%;
		background: rgba(255, 255, 255, 0.98); /* Increased opacity for better visibility */
		border-radius: 10px;
		box-shadow: 0 4px 24px rgba(0,0,0,0.2);
		padding: 2rem 1.8rem 1.2rem 1.8rem; /* Reduced padding */
	}
	.login-logo {
		width: 80px; /* Reduced from 90px */
		height: 80px; /* Reduced from 90px */
		margin-bottom: 0.8rem; /* Reduced margin */
	}
	.login-title {
		font-size: 1.2rem; /* Reduced from 1.3rem */
	}
	.login-subtitle {
		font-size: 1rem; /* Reduced from 1.1rem */
		margin-bottom: 1rem; /* Reduced margin */
	}
	.login-form-title {
		font-size: 1.1rem;
		font-weight: 600;
		margin-bottom: 0.5rem;
		color: #263238;
	}
	.form-group {
		text-align: left;
		margin-bottom: 0.8rem; /* Reduced margin */
	}
	.form-control {
		background-color: #ffffff !important;
		color: #333333 !important;
		border: 1px solid #ced4da !important;
		height: calc(2.2rem + 2px);
		padding: 0.375rem 0.75rem;
		font-size: 0.95rem;
	}

	.form-control:focus {
		border-color: #80bdff !important;
		box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
	}

	/* Add placeholder color */
	.form-control::placeholder {
		color: #6c757d !important;
		opacity: 0.8;
	}

	/* Make labels more visible */
	.form-group label {
		color: #2c3e50;
		font-weight: 600;
		font-size: 0.9rem;
		margin-bottom: 0.3rem;
	}

	.btn-primary {
		background: #1976d2;
		border: none;
		padding: 10px;
		font-weight: 600;
		transition: all 0.3s ease;
	}
	.btn-primary:hover {
		background: #1565c0;
		transform: translateY(-2px);
		box-shadow: 0 4px 8px rgba(0,0,0,0.1);
	}
	.remember-me {
		display: flex;
		align-items: center;
		margin-bottom: 1rem;
	}
	.remember-me input {
		margin-right: 0.5rem;
	}
	.forgot-link {
		display: block;
		margin-top: 1rem;
		color: #1976d2;
		font-size: 0.95rem;
		text-decoration: none;
	}
	.forgot-link:hover {
		text-decoration: underline;
	}
	.designed-by {
		margin-top: 1.5rem;
		font-size: 0.95rem;
		color: #888;
	}
	.no-video-message {
		display: none;
	}
	
	.site-title {
		position: absolute;
		top: 10%;
		left: 10%;
		color: #fff;
		text-shadow: 0 2px 4px rgba(0,0,0,0.5);
		max-width: 50%;
		z-index: 1;
		text-align: left;
	}
	
	.site-title h1 {
		font-size: 3rem;
		font-weight: 700;
		margin-bottom: 1rem;
	}
	
	.site-title p {
		font-size: 1.5rem;
		opacity: 0.9;
	}
	
	/* Media query for smaller screens */
	@media (max-width: 992px) {
		body {
			justify-content: center;
		}
		.login-container {
			width: 85%;
			max-width: 320px;
			margin: 0 auto;
		}
		.site-title {
			display: none;
		}
	}
	
	/* Style for user type selection */
	#user_type {
		background-color: #f8f9fa;
		border: 1px solid #ced4da;
		transition: all 0.3s;
	}
	
	#user_type:focus {
		border-color: #80bdff;
		box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
	}
</style>

<body>
  <div class="video-container">
    <video autoplay muted loop id="background-video">
      <source src="assets/videos/background.mp4" type="video/mp4">
      <!-- Add additional source formats for better browser compatibility -->
      <source src="assets/videos/background.webm" type="video/webm">
      <div class="no-video-message">Your browser does not support the video tag.</div>
    </video>
    <div class="video-overlay"></div>
  </div>
  
  <div class="site-title">
    <h1>AutoSched</h1>
    <p>Smart Class Scheduling System</p>
  </div>

  <div class="login-container">
    <form id="login-form">
        <!-- Initial Role Selection -->
        <div id="initial_selection" class="form-group">
            <div class="btn-group-vertical w-100">
                <button type="button" class="btn btn-primary mb-2" id="admin_btn">Admin Login</button>
                <button type="button" class="btn btn-success mb-2" id="student_btn">Student Login</button>
                <button type="button" class="btn btn-info" id="faculty_btn">Faculty/Staff Login</button>
            </div>
        </div>

        <!-- Admin Login Fields (Hidden by default) -->
        <div id="admin_fields" style="display:none;">
            <div>
                <input type="hidden" name="role" value="admin">
                <input type="hidden" name="type" value="admin">
                <div class="form-group">
                    <label for="admin_username">Username</label>
                    <input type="text" name="username" id="admin_username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="admin_password" class="form-control" required>
                        <div class="input-group-append">
                            <span class="input-group-text toggle-password" style="cursor: pointer;">
                                <i class="fa fa-eye-slash" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" id="admin_login_btn" class="btn btn-primary btn-block">Login</button>
                    <button type="button" class="btn btn-secondary btn-block" id="admin_back">Back</button>
                </div>
            </div>
        </div>

        <!-- Faculty Login Fields (Hidden by default) -->
        <div id="faculty_fields" style="display:none;">
            <div>
                <input type="hidden" name="role" value="faculty">
                <div class="form-group">
                    <label for="faculty_id">Enter Your Employee ID</label>
                    <input type="text" name="faculty_id" id="faculty_id" class="form-control" required>
                    <div id="faculty_id_error" class="text-danger mt-1" style="display:none"></div>
                </div>
                <div class="form-group">
                    <button type="button" id="faculty_login_btn" class="btn btn-info btn-block">View Schedule</button>
                    <button type="button" class="btn btn-secondary btn-block" id="faculty_back">Back</button>
                </div>
            </div>
        </div>

        <!-- Student Type Selection (Hidden by default) -->
        <div id="student_type_fields" style="display:none;">
            <div class="btn-group-vertical w-100">
                <button type="button" class="btn btn-info mb-2" id="college_btn">College Student</button>
                <button type="button" class="btn btn-warning" id="shs_btn">Senior High Student</button>
            </div>
            <button type="button" class="btn btn-secondary btn-block mt-2" id="student_back">Back</button>
        </div>

        <!-- College Student Fields (Hidden by default) -->
        <div id="college_fields" style="display:none;">
            <div class="form-group">
                <label>Department</label>
                <select name="department" id="department" class="form-control" required></select>
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <select name="year_level" class="form-control" required>
                    <option value="">Select Year Level</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                </select>
            </div>
            <div class="form-group">
                <label>Section</label>
                <input type="text" name="section_name" id="section_name" class="form-control" placeholder="Type your section name" required>
                <input type="hidden" name="section" id="section" value="">
                <div id="section_matches" class="mt-2"></div>
            </div>
            <div class="form-group">
                <label>Section Code</label>
                <div class="input-group">
                    <input type="password" name="section_code" id="college_section_code" class="form-control" required>
                    <div class="input-group-append">
                        <span class="input-group-text toggle-password" style="cursor: pointer;">
                            <i class="fa fa-eye-slash" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
                <div id="college_code_error" class="text-danger mt-1" style="display:none"></div>
            </div>
            <div class="form-group">
                <button type="button" id="college_view_btn" class="btn btn-primary btn-block">View Schedule</button>
                <button type="button" class="btn btn-secondary btn-block college_back">Back</button>
            </div>
        </div>

        <!-- SHS Student Fields (Hidden by default) -->
        <div id="shs_fields" style="display:none;">
            <div class="form-group">
                <label>Strand</label>
                <select name="strand" id="strand" class="form-control" required></select>
            </div>
            <div class="form-group">
                <label>Grade Level</label>
                <select name="grade_level" class="form-control" required>
                    <option value="">Select Grade Level</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
            </div>
            <div class="form-group">
                <label>Section</label>
                <select name="shs_section" id="shs_section" class="form-control" required></select>
            </div>
            <div class="form-group">
                <label>Section Code</label>
                <div class="input-group">
                    <input type="password" name="shs_section_code" id="shs_section_code" class="form-control" required>
                    <div class="input-group-append">
                        <span class="input-group-text toggle-password" style="cursor: pointer;">
                            <i class="fa fa-eye-slash" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
                <div id="shs_code_error" class="text-danger mt-1" style="display:none"></div>
            </div>
            <div class="form-group">
                <button type="button" id="shs_view_btn" class="btn btn-primary btn-block">View Schedule</button>
                <button type="button" class="btn btn-secondary btn-block shs_back">Back</button>
            </div>
        </div>
    </form>
  </div>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

</body>
<script>
$(document).ready(function(){
    // Handle initial button clicks
    $('#admin_btn').click(() => {
        $('#initial_selection').hide();
        $('#admin_fields').fadeIn();
        $('input[name="role"]').val('admin');
    });

    $('#student_btn').click(() => {
        $('#initial_selection').hide();
        $('#student_type_fields').fadeIn();
    });

    $('#faculty_btn').click(() => {
        $('#initial_selection').hide();
        $('#faculty_fields').fadeIn();
        $('input[name="role"]').val('faculty');
    });

    // Handle student type selection
    $('#college_btn').click(() => {
        $('#student_type_fields').hide();
        $('#college_fields').fadeIn();
        $('input[name="role"]').val('college');
        loadDepartments();
    });

    $('#shs_btn').click(() => {
        $('#student_type_fields').hide();
        $('#shs_fields').fadeIn();
        $('input[name="role"]').val('shs');
        loadStrands();
    });

    // Handle back buttons
    $('#admin_back, #student_back, #faculty_back').click(() => {
        $('.login-fields, #admin_fields, #faculty_fields, #student_type_fields').hide();
        $('#initial_selection').fadeIn();
    });

    $('.college_back').click(() => {
        $('#college_fields').hide();
        $('#student_type_fields').fadeIn();
    });

    $('.shs_back').click(() => {
        $('#shs_fields').hide();
        $('#student_type_fields').fadeIn();
    });
    
    // Password visibility toggle
    $(document).on('click', '.toggle-password', function() {
        const passwordInput = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        
        // Toggle password visibility
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    // Update login button click for admin login
    $('#admin_login_btn').click(function(){
        var username = $('#admin_username').val();
        var password = $('#admin_password').val();
        
        if(!username || !password) {
            alert("Please enter both username and password");
            return;
        }
        
        $.ajax({
            url:'ajax.php?action=login',
            method:'POST',
            data:{
                username: username,
                password: password,
                role: 'admin',
                type: 'admin'
            },
            success:function(resp){
                if(resp==1){
                    window.location.href = 'index.php?page=home';
                }else{
                    $('#admin_username').val('');
                    $('#admin_password').val('');
                    alert("Authentication failed. Please try again.");
                }
            }
        });
    });

    // Section name typing functionality
    $('#section_name').on('input', function() {
        const query = $(this).val().trim();
        const departmentId = $('#department').val();
        const yearLevel = $('[name="year_level"]').val();
        
        // Clear section ID when typing
        $('#section').val('');
        
        if(query.length > 1 && departmentId && yearLevel) {
            $.ajax({
                url: 'ajax.php?action=search_sections',
                method: 'POST',
                data: {
                    query: query,
                    department_id: departmentId,
                    year_level: yearLevel
                },
                success: function(response) {
                    $('#section_matches').html(response);
                    $('#section_matches').show();
                }
            });
        } else {
            $('#section_matches').hide();
        }
    });
    
    // Handle section selection from search results
    $(document).on('click', '.section-match', function() {
        const sectionId = $(this).data('id');
        const sectionName = $(this).text();
        
        $('#section_name').val(sectionName);
        $('#section').val(sectionId);
        $('#section_matches').hide();
        
        // Auto-fetch section code
        getSectionCode(sectionId, 'college');
        
        // Clear any previous error
        $('#college_code_error').hide();
    });

    // Faculty login button click handler
    $('#faculty_login_btn').click(function(){
        var faculty_id = $('#faculty_id').val();
        
        if(!faculty_id) {
            $('#faculty_id_error').text('Please enter your Employee ID').show();
            return;
        }
        
        $('#faculty_id_error').hide();
        $('#faculty_login_btn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...').prop('disabled', true);
        
        $.ajax({
            url: 'ajax.php?action=verify_faculty_id',
            method: 'POST',
            data: {
                faculty_id: faculty_id
            },
            dataType: 'json',
            success: function(response) {
                $('#faculty_login_btn').html('View Schedule').prop('disabled', false);
                
                if(response.status === 1) {
                    // Redirect to view_schedule.php with faculty ID
                    window.location.href = response.redirect_url || 'view_schedule.php?faculty_id=' + response.faculty_id;
                } else {
                    $('#faculty_id_error').text(response.message || 'Invalid Faculty ID').show();
                }
            },
            error: function(xhr, status, error) {
                $('#faculty_login_btn').html('View Schedule').prop('disabled', false);
                
                try {
                    const errorObj = JSON.parse(xhr.responseText);
                    if(errorObj.message) {
                        $('#faculty_id_error').text(errorObj.message).show();
                        return;
                    }
                } catch(e) {
                    // Not JSON, continue with generic error
                }
                
                $('#faculty_id_error').text('Error connecting to server. Please try again.').show();
            }
        });
    });
});

// Check if video can play
document.addEventListener('DOMContentLoaded', function() {
	var video = document.getElementById('background-video');
	
	// Error handler if video fails to load
	video.addEventListener('error', function() {
		document.body.style.background = '#f5f7fa';
	});
	
	// Handle browsers that don't support video
	if (video.canPlayType) {
		// Video can be played, do nothing special
	} else {
		document.body.style.background = '#f5f7fa';
	}
});

function loadLoginFields(role) {
    let fields = '';
    
    switch(role) {
        case 'admin':
            fields = `
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label> 
                    <input type="password" name="password" class="form-control" required>
                </div>
            `;
            break;
            
        case 'college':
            fields = `
                <div class="form-group">
                    <label>Department</label>
                    <select name="department" id="department" class="form-control" required>
                        <option value="">Select Department</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Year Level</label>
                    <select name="year_level" id="year_level" class="form-control" required>
                        <option value="">Select Year Level</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section</label>
                    <select name="section" id="section" class="form-control" required>
                        <option value="">Select Section</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section Code</label>
                    <input type="password" name="section_code" class="form-control" required>
                </div>
            `;
            loadDepartments();
            break;
            
        case 'shs':
            fields = `
                <div class="form-group">
                    <label>Strand</label>
                    <select name="strand" id="strand" class="form-control" required>
                        <option value="">Select Strand</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Grade Level</label>
                    <select name="grade_level" class="form-control" required>
                        <option value="">Select Grade Level</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section</label>
                    <select name="shs_section" id="shs_section" class="form-control" required>
                        <option value="">Select Section</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section Code</label>
                    <input type="password" name="shs_section_code" class="form-control" required>
                </div>
            `;
            loadStrands();
            break;
    }
    
    $('#login_fields').html(fields);
}

// Load departments for college login
function loadDepartments() {
    $.ajax({
        url: 'ajax.php?action=get_departments',
        method: 'GET',
        success: function(response) {
            $('#department').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading departments:', error);
            $('#department').html('<option value="">Error loading departments</option>');
        }
    });
}

// Load sections based on department selection
$(document).on('change', '#department, [name="year_level"]', function() {
    let dept = $('#department').val();
    let year = $('[name="year_level"]').val();
    
    if(dept && year) {
        $.ajax({
            url: 'ajax.php?action=get_department_sections',
            method: 'POST',
            data: {department_id: dept, year_level: year},
            success: function(response) {
                $('#section').html(response);
                
                // Get section code if only one section is available
                if ($('#section option').length == 2) {
                    let sectionId = $('#section option:eq(1)').val();
                    getSectionCode(sectionId, 'college');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading sections:', error);
                $('#section').html('<option value="">Error loading sections</option>');
            }
        });
    }
});

// Auto-fill section code when section is selected
$(document).on('change', '#section', function() {
    let sectionId = $(this).val();
    if(sectionId) {
        getSectionCode(sectionId, 'college');
    }
});

// Load strands for SHS login
function loadStrands() {
    $.ajax({
        url: 'ajax.php?action=get_strands',
        method: 'GET',
        success: function(response) {
            $('#strand').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading strands:', error);
            $('#strand').html('<option value="">Error loading strands</option>');
        }
    });
}

// Load sections based on strand and grade selection
$(document).on('change', '#strand, [name="grade_level"]', function() {
    let strand = $('#strand').val();
    let grade = $('[name="grade_level"]').val();
    
    if(strand && grade) {
        $.ajax({
            url: 'ajax.php?action=get_strand_sections',
            method: 'POST',
            data: {strand_id: strand, grade_level: grade},
            success: function(response) {
                $('#shs_section').html(response);
                
                // Get section code if only one section is available
                if ($('#shs_section option').length == 2) {
                    let sectionId = $('#shs_section option:eq(1)').val();
                    getSectionCode(sectionId, 'shs');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading sections:', error);
                $('#shs_section').html('<option value="">Error loading sections</option>');
            }
        });
    }
});

// Auto-fill section code when SHS section is selected
$(document).on('change', '#shs_section', function() {
    let sectionId = $(this).val();
    if(sectionId) {
        getSectionCode(sectionId, 'shs');
    }
});

// Function to get section code
function getSectionCode(sectionId, type) {
    if(!sectionId) return;
    
    $.ajax({
        url: 'ajax.php?action=get_section_code',
        method: 'POST',
        data: { section_id: sectionId },
        dataType: 'json',
        success: function(response) {
            if(response.status === 1 && response.section_code) {
                if(type === 'college') {
                    $('#college_section_code').val(response.section_code);
                } else {
                    $('#shs_section_code').val(response.section_code);
                }
            }
        }
    });
}

// Handle college student login
$('#college_view_btn').click(function() {
    const departmentId = $('#department').val();
    const yearLevel = $('[name="year_level"]').val();
    const sectionId = $('#section').val();
    const sectionName = $('#section_name').val();
    const sectionCode = $('#college_section_code').val();
    
    // Clear previous error
    $('#college_code_error').hide();
    
    // Validate all fields are filled
    if(!departmentId || !yearLevel || !sectionName || !sectionCode) {
        $('#college_code_error').text('Please fill in all fields').show();
        return;
    }
    
    // Validate section ID is set (should be set when selecting from dropdown)
    if(!sectionId) {
        $('#college_code_error').text('Please select a valid section from the suggestions').show();
        return;
    }
    
    console.log('Verifying section code:', {
        section_id: sectionId,
        section_code: sectionCode,
        type: 'college'
    });
    
    // Show loading indicator
    $('#college_view_btn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...').prop('disabled', true);
    
    // Verify section code
    $.ajax({
        url: 'ajax.php?action=verify_section_code',
        method: 'POST',
        data: {
            section_id: sectionId,
            section_code: sectionCode,
            type: 'college'
        },
        dataType: 'json',
        success: function(response) {
            console.log('Verification response:', response);
            
            // Reset button
            $('#college_view_btn').html('View Schedule').prop('disabled', false);
            
            if(response.status === 1) {
                // Use redirect URL from response if available
                window.location.href = response.redirect_url || 'view_student_schedule.php';
            } else {
                // Show error message
                $('#college_code_error').text(response.message || 'Invalid section code. Please try again.').show();
            }
        },
        error: function(xhr, status, error) {
            // Reset button
            $('#college_view_btn').html('View Schedule').prop('disabled', false);
            
            console.error('AJAX error:', status, error);
            
            // Try to parse response text as JSON
            try {
                const errorObj = JSON.parse(xhr.responseText);
                if(errorObj.message) {
                    $('#college_code_error').text(errorObj.message).show();
                    return;
                }
            } catch(e) {
                // Not JSON, continue with generic error
            }
            
            $('#college_code_error').text('Error connecting to server. Please try again.').show();
        }
    });
});

// Handle SHS student login
$('#shs_view_btn').click(function() {
    const strandId = $('#strand').val();
    const gradeLevel = $('[name="grade_level"]').val();
    const sectionId = $('#shs_section').val();
    const sectionCode = $('#shs_section_code').val();
    
    // Clear previous error
    $('#shs_code_error').hide();
    
    // Validate all fields are filled
    if(!strandId || !gradeLevel || !sectionId || !sectionCode) {
        $('#shs_code_error').text('Please fill in all fields').show();
        return;
    }
    
    console.log('Verifying SHS section code:', {
        section_id: sectionId,
        section_code: sectionCode,
        type: 'shs'
    });
    
    // Show loading indicator
    $('#shs_view_btn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...').prop('disabled', true);
    
    // Verify section code
    $.ajax({
        url: 'ajax.php?action=verify_section_code',
        method: 'POST',
        data: {
            section_id: sectionId,
            section_code: sectionCode,
            type: 'shs'
        },
        dataType: 'json',
        success: function(response) {
            console.log('SHS verification response:', response);
            
            // Reset button
            $('#shs_view_btn').html('View Schedule').prop('disabled', false);
            
            if(response.status === 1) {
                // Use redirect URL from response if available
                window.location.href = response.redirect_url || 'view_student_schedule.php';
            } else {
                // Show error message
                $('#shs_code_error').text(response.message || 'Invalid section code. Please try again.').show();
            }
        },
        error: function(xhr, status, error) {
            // Reset button
            $('#shs_view_btn').html('View Schedule').prop('disabled', false);
            
            console.error('AJAX error:', status, error);
            
            // Try to parse response text as JSON
            try {
                const errorObj = JSON.parse(xhr.responseText);
                if(errorObj.message) {
                    $('#shs_code_error').text(errorObj.message).show();
                    return;
                }
            } catch(e) {
                // Not JSON, continue with generic error
            }
            
            $('#shs_code_error').text('Error connecting to server. Please try again.').show();
        }
    });
});
</script>	
</html>