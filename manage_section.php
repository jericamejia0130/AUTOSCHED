<?php include 'db_connect.php'; ?>

<?php
if(isset($_GET['id'])){
    $section = $conn->query("SELECT * FROM sections WHERE id = ".$_GET['id'])->fetch_assoc();
}
?>

<div class="container-fluid">
    <form id="manage-section">
        <div id="msg"></div>
        <div id="loader" style="display:none;">
            <div class="d-flex justify-content-center align-items-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <span class="ml-2">Checking for duplicate sections...</span>
            </div>
        </div>
        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
        
        <div class="form-group">
            <label>Level</label>
            <select name="level_type" id="level_type" class="form-control" required>
                <option value="">Select Level</option>
                <option value="College" <?php echo isset($section) && ($section['year_level'] <= 3 && $section['year_level'] > 0) ? 'selected' : '' ?>>College</option>
                <option value="SHS" <?php echo isset($section) && ($section['year_level'] >= 11) ? 'selected' : '' ?>>Senior High School</option>
            </select>
        </div>

        <div id="college_fields" style="display:none;">
            <div class="form-group">
                <label>Department</label>
                <select name="course_id" id="course_id" class="form-control">
                    <option value="">Select Department</option>
                    <?php 
                    $courses = $conn->query("SELECT * FROM courses ORDER BY course ASC");
                    while($row = $courses->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($section) && $section['course_id'] == $row['id'] ? 'selected' : '' ?>>
                        <?php echo $row['course'] ?> - <?php echo $row['description'] ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <select name="year_level_college" id="year_level_college" class="form-control">
                    <option value="">Select Year Level</option>
                    <option value="1" <?php echo isset($section) && $section['year_level'] == 1 ? 'selected' : '' ?>>1st Year</option>
                    <option value="2" <?php echo isset($section) && $section['year_level'] == 2 ? 'selected' : '' ?>>2nd Year</option>
                    <option value="3" <?php echo isset($section) && $section['year_level'] == 3 ? 'selected' : '' ?>>3rd Year</option>
                </select>
            </div>
        </div>

        <div id="shs_fields" style="display:none;">
            <div class="form-group">
                <label>Strand</label>
                <select name="strand_id" id="strand_id" class="form-control">
                    <option value="">Select Strand</option>
                    <?php 
                    $strands = $conn->query("SELECT * FROM strands ORDER BY code ASC");
                    while($row = $strands->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($section) && $section['strand_id'] == $row['id'] ? 'selected' : '' ?>>
                        <?php echo $row['code'] ?> - <?php echo $row['name'] ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Grade Level</label>
                <select name="year_level_shs" id="year_level_shs" class="form-control">
                    <option value="">Select Grade Level</option>
                    <option value="11" <?php echo isset($section) && $section['year_level'] == 11 ? 'selected' : '' ?>>Grade 11</option>
                    <option value="12" <?php echo isset($section) && $section['year_level'] == 12 ? 'selected' : '' ?>>Grade 12</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Section Name</label>
            <input type="text" name="name" id="section_name" class="form-control" required value="<?php echo isset($section) ? $section['name'] : '' ?>">
        </div>

        <div class="form-group">            <label>Advisory Teacher</label>            <select name="faculty_id" id="faculty_id" class="form-control">                <option value="">Select Advisory Teacher</option>                <?php                 $faculty = $conn->query("SELECT id, CONCAT(lastname, ', ', firstname, ' ', middlename) as name FROM faculty ORDER BY lastname ASC");                while($row = $faculty->fetch_assoc()):                ?>                <option value="<?php echo $row['id'] ?>" <?php echo isset($section) && $section['faculty_id'] == $row['id'] ? 'selected' : '' ?>>                    <?php echo $row['name'] ?>                </option>                <?php endwhile; ?>            </select>        </div>        <div class="form-group">            <label>Section Code <small class="text-muted">(Used for student login)</small></label>            <div class="input-group">                <input type="password" name="section_code" id="section_code" class="form-control" value="<?php echo isset($section) && isset($section['section_code']) ? $section['section_code'] : '' ?>">                <div class="input-group-append">                    <span class="input-group-text toggle-password" style="cursor: pointer;">                        <i class="fa fa-eye-slash" aria-hidden="true"></i>                    </span>                </div>            </div>            <small class="form-text text-muted">This code will be used by students to access their class schedule.</small>        </div>
    </form>
</div>

<script>
$(document).ready(function(){
    // Clear any previous error message
    $('#msg').html('');
    
    // Add a form submission flag to prevent duplicate submissions
    var formSubmitted = false;
    
    // Password visibility toggle
    $('.toggle-password').click(function() {
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
    
    // Show/hide fields based on level type
    $('#level_type').change(function(){
        var type = $(this).val();
        $('#college_fields, #shs_fields').hide();
        $('#course_id, #strand_id, #year_level_college, #year_level_shs').prop('required', false);
        
        if(type == 'College'){
            $('#college_fields').show();
            $('#course_id, #year_level_college').prop('required', true);
        } else if(type == 'SHS'){
            $('#shs_fields').show();
            $('#strand_id, #year_level_shs').prop('required', true);
        }
    });

    // Trigger change event on load if level is selected
    if($('#level_type').val() != ''){
        $('#level_type').trigger('change');
    }
});

// Submit the form when the modal's save button is clicked
$('#submit').click(function(e){
    // Prevent multiple submissions by checking if button is already disabled
    if($(this).attr('disabled') === 'disabled') {
        return false;
    }
    // Disable the button to prevent multiple clicks
    $(this).attr('disabled', 'disabled');
    
    $('#manage-section').submit();
});

// Variable to store timeout IDs and submission status
var timeoutId = null;
var isSubmitting = false;

$('#manage-section').submit(function(e){
    e.preventDefault();
    
    // Prevent multiple submissions
    if(isSubmitting) {
        console.log('Form is already being submitted');
        return false;
    }
    
    // Set submission flag
    isSubmitting = true;
    
    // Reset UI state
    $('#msg').html('');
    
    // Custom loading message
    $('#loader').show();
    start_load();
    
    // Get the selected level type and appropriate year level
    var levelType = $('#level_type').val();
    var yearLevel = '';
    var errors = [];
    
    // Make sure we include the ID if this is an edit operation
    var sectionId = $('input[name="id"]').val();
    console.log("Section ID for edit: ", sectionId);
    
    // Validate all required fields
    if(levelType === '') {
        errors.push("Please select level type");
    } else if(levelType === 'College') {
        yearLevel = $('#year_level_college').val();
        if($('#course_id').val() === '') {
            errors.push("Please select department");
        }
        if(yearLevel === '') {
            errors.push("Please select year level");
        }
    } else if(levelType === 'SHS') {
        yearLevel = $('#year_level_shs').val();
        if($('#strand_id').val() === '') {
            errors.push("Please select strand");
        }
        if(yearLevel === '') {
            errors.push("Please select grade level");
        }
    }
    
    if($('#section_name').val() === '') {
        errors.push("Please enter section name");
    }
    
    // Display errors if any
    if(errors.length > 0) {
        end_load();
        $('#loader').hide();
        $('#msg').html('<div class="alert alert-danger">' + errors.join('<br>') + '</div>');
        // Re-enable the submit button
        $('#submit').removeAttr('disabled');
        isSubmitting = false;
        return false;
    }
    
    // Prepare the form data
    var formData = $(this).serialize();
    formData += '&year_level=' + yearLevel;
    
    // Debug log for form submission
    console.log("Form submission - ID:", sectionId, "Data:", formData);
    
    // Clear any existing timeout before creating a new one
    if(timeoutId) {
        clearTimeout(timeoutId);
    }
    
    // Create a timeout to handle unresponsive server
    timeoutId = setTimeout(function() {
        $('#loader').html('<div class="alert alert-warning">The request is taking longer than expected. Please wait...</div>');
    }, 5000);
    
    $.ajax({
        url: 'ajax.php?action=save_section',
        method: 'POST',
        data: formData,
        cache: false, // Prevent caching of the request
        success: function(resp){
            // Clear the timeout
            if(timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            console.log("Raw response:", resp);
            
            try {
                // Try to parse as JSON
                var response;
                
                // Handle different response formats
                if (typeof resp === 'object') {
                    response = resp;
                } else if (typeof resp === 'string') {
                    if (!resp || resp.trim() === '') {
                        throw new Error("Empty response");
                    }
                    response = JSON.parse(resp);
                } else {
                    throw new Error("Unexpected response type: " + (typeof resp));
                }
                
                console.log("Parsed response:", response);
                
                // Handle successful case
                if(response.status == 1){
                    alert_toast(response.msg, 'success');
                    $('#msg').html('<div class="alert alert-success">' + response.msg + '</div>');
                    
                    // Determine appropriate tab based on level type
                    var levelType = $('#level_type').val();
                    var tab = levelType === 'SHS' ? 'shs' : 'college';
                    
                    // Close modal and redirect after a delay
                    setTimeout(function() {
                        $('#uni_modal').modal('hide');
                        window.location.href = 'index.php?page=sections&tab=' + tab + '&refresh=true&t=' + new Date().getTime();
                    }, 1000);
                } 
                // Handle error case
                else {
                    $('#loader').hide();
                    $('#msg').html('<div class="alert alert-danger">' + (response.msg || "An error occurred.") + '</div>');
                    alert_toast(response.msg || "An error occurred.", 'danger');
                    end_load(); // Important: end loading here for error cases
                    // Re-enable the submit button on error
                    $('#submit').removeAttr('disabled');
                    isSubmitting = false;
                    return; // Exit function
                }
            } catch(e) {
                console.error("Error parsing response:", e);
                console.error("Invalid response:", resp);
                
                // For legacy non-JSON responses (e.g., just "1")
                if(resp && resp.trim() == '1') {
                    alert_toast("Data successfully saved.", 'success');
                    
                    // Determine appropriate tab based on level type
                    var levelType = $('#level_type').val();
                    var tab = levelType === 'SHS' ? 'shs' : 'college';
                    
                    // Close modal and redirect after a delay
                    setTimeout(function() {
                        $('#uni_modal').modal('hide');
                        window.location.href = 'index.php?page=sections&tab=' + tab + '&refresh=true&t=' + new Date().getTime();
                    }, 1000);
                } else {
                    $('#loader').hide();
                    $('#msg').html('<div class="alert alert-danger">Invalid response format: ' + e.message + '</div>');
                    alert_toast("An error occurred: " + e.message, 'danger');
                    end_load(); // Important: end loading here for error cases
                    // Re-enable the submit button on error
                    $('#submit').removeAttr('disabled');
                    isSubmitting = false;
                }
            }
        },
        error: function(xhr, status, error) {
            // Clear the timeout
            if(timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            console.error("AJAX Error:", status, error);
            $('#loader').hide();
            $('#msg').html('<div class="alert alert-danger">Server error: ' + error + '</div>');
            alert_toast("Server error: " + error, 'danger');
            end_load();
            // Re-enable the submit button on error
            $('#submit').removeAttr('disabled');
            isSubmitting = false;
        },
        complete: function() {
            // Ensure loading stops and isSubmitting is reset
            end_load();
            isSubmitting = false;
        }
    });
});
</script>