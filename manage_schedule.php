<?php include 'db_connect.php' ?>
<?php include 'header.php' ?>
<?php include 'topbar.php' ?>
<?php include 'navbar.php' ?>
<?php
if(isset($_GET['id'])){
$qry = $conn->query("SELECT s.*, 
                      CONCAT(f.firstname, ' ', f.lastname) AS faculty_name, 
                      c.course AS course_name, 
                      st.name AS strand_name, 
                      sec.name AS section_name,
                      sec.year_level AS year_level_from_section,
                      sub.subject AS subject_name,
                      r.name AS room_name
                FROM schedules s 
                LEFT JOIN faculty f ON s.faculty_id = f.id 
                LEFT JOIN courses c ON s.course_id = c.id 
                LEFT JOIN strands st ON s.strand_id = st.id 
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN subjects sub ON s.subject_id = sub.id
                LEFT JOIN rooms r ON s.room_id = r.id 
                WHERE s.id = " . $_GET['id']);

foreach($qry->fetch_array() as $k => $val){
	$$k=$val;
}
if(!empty($repeating_data)){
$rdata= json_decode($repeating_data);
	foreach($rdata as $k => $v){
		 $$k = $v;
	}
	$dow_arr = isset($dow) ? explode(',',$dow) : '';
	// var_dump($start);
}

// Use the year level from section if not set in the schedule
if(!isset($year_level) && isset($year_level_from_section)) {
    $year_level = $year_level_from_section;
}

// Determine schedule_type based on strand_id or course_id
if(isset($strand_id) && $strand_id > 0) {
    $schedule_type = 'shs';
    $department_id = $course_id; // For consistency in form
} else if(isset($course_id) && $course_id > 0) {
    $schedule_type = 'college';
    $department_id = $course_id; // For consistency in form
} else {
    $schedule_type = '';
}

// Add this to fix handling of dow when editing
if(!empty($dow) && !isset($dow_arr)) {
    $dow_arr = explode(',', $dow);
}
}

// Check if we're in edit mode
$edit_mode = isset($_GET['id']);

// Set default schedule type if not in edit mode or not set
if(!isset($schedule_type)) {
    $schedule_type = '';
}
?>
<style>
.form-group {
    display: block !important;
    margin-bottom: 15px;
}
.field-readonly {
    background-color: #f8f9fa;
    cursor: not-allowed;
}
.info-display {
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    background-color: #f8f9fa;
    min-height: 38px;
    line-height: 1.5;
    display: block;
    width: 100%;
}

/* Better spacing for select2 elements */
.select2-container--default .select2-selection--single {
    height: 38px !important;
    display: flex;
    align-items: center;
}

/* Make the form more compact */
.container-fluid {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 15px;
}

/* Clean up the card styling for standalone view */
.card {
    margin-top: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #007bff !important;
}

/* Responsive styling */
@media (max-width: 768px) {
    .form-group {
        margin-bottom: 10px;
    }
    
    .container-fluid {
        padding: 10px;
    }
    
    .card {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    
    .card-body {
        padding: 15px 10px;
    }
    
    .row {
        margin-left: -5px;
        margin-right: -5px;
    }
    
    .col-md-6 {
        padding-left: 5px;
        padding-right: 5px;
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px;
    }
    
    .card-header h5 {
        margin-bottom: 10px;
    }
    
    .action-buttons {
        margin-top: 10px;
        width: 100%;
        justify-content: flex-start;
    }
    
    .select2-container {
        width: 100% !important;
    }
}

/* Make buttons stack on very small screens */
@media (max-width: 480px) {
    .action-buttons {
        flex-direction: column;
        gap: 5px;
    }
    
    .action-buttons .btn {
        width: 100%;
        margin-bottom: 5px;
    }
}
</style>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4><?php echo isset($id) ? "Update Schedule" : "New Schedule" ?></h4>
						<div class="text-muted small mt-1">
							Please fill in all required fields to <?php echo isset($id) ? "update" : "create" ?> a schedule. After saving, you will be redirected to the schedules list.
						</div>
					</div>
					<div class="card-body">
						<form id="manage-schedule">
							<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
								<!-- Faculty -->
							<div class="form-group">
								<label for="faculty_id">Faculty</label>
								<select name="faculty_id" id="faculty_id" class="form-control select2" required>
									<option value="">Select Faculty</option>
									<?php 
									$faculty = $conn->query("SELECT f.*, CONCAT(f.lastname, ', ', f.firstname) as name FROM faculty f ORDER BY f.lastname ASC");
									while($row = $faculty->fetch_assoc()):
									?>
									<option value="<?php echo $row['id'] ?>" <?php echo isset($faculty_id) && $faculty_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
								<!-- Schedule Type -->
							<div class="form-group">
								<label for="schedule_type">Schedule Type</label>
								<select name="schedule_type" id="schedule_type" class="form-control select2" required>
									<option value="">Select Type</option>
									<option value="college" <?php echo isset($schedule_type) && $schedule_type == 'college' ? 'selected' : '' ?>>College</option>
									<option value="shs" <?php echo isset($schedule_type) && $schedule_type == 'shs' ? 'selected' : '' ?>>Senior High School</option>
								</select>
							</div>
								<!-- Student Type -->
							<div class="form-group">
								<label>Student Type</label>
								<select name="student_type" id="student_type" class="form-control" required>
									<option value="">Select Type</option>
									<option value="college">College</option>
									<option value="shs">Senior High</option>
								</select>
							</div>

							<div id="college_fields" style="display:none;">
								<div class="form-group">
									<label>Department</label>
									<select name="department" id="department" class="form-control">
										<option value="">Select Department</option>
										<?php 
										$courses = $conn->query("SELECT * FROM courses ORDER BY course ASC");
										while($row = $courses->fetch_assoc()):
										?>
										<option value="<?php echo $row['id'] ?>"><?php echo $row['course'] ?></option>
										<?php endwhile; ?>
									</select>
								</div>
								<div class="form-group">
									<label>Year Level</label>
									<select name="year_level" id="year_level" class="form-control">
										<option value="">Select Year Level</option>
										<option value="1">1st Year</option>
										<option value="2">2nd Year</option>
										<option value="3">3rd Year</option>
									</select>
								</div>
								<div class="form-group">
									<label>Section</label>
									<select name="college_section" id="college_section" class="form-control">
										<option value="">Select Section</option>
									</select>
								</div>
							</div>

							<div id="shs_fields" style="display:none;">
								<div class="form-group">
									<label>Strand</label>
									<select name="strand" id="strand" class="form-control">
										<option value="">Select Strand</option>
										<?php 
										$strands = $conn->query("SELECT * FROM strands ORDER BY code ASC");
										while($row = $strands->fetch_assoc()):
										?>
										<option value="<?php echo $row['id'] ?>"><?php echo $row['code'] ?></option>
										<?php endwhile; ?>
									</select>
								</div>
								<div class="form-group">
									<label>Grade Level</label>
									<select name="grade_level" id="grade_level" class="form-control">
										<option value="">Select Grade Level</option>
										<option value="11">Grade 11</option>
										<option value="12">Grade 12</option>
									</select>
								</div>
								<div class="form-group">
									<label>Section</label>
									<select name="shs_section" id="shs_section" class="form-control">
										<option value="">Select Section</option>
									</select>
								</div>
							</div>
								<!-- Subject -->
							<div class="form-group">
								<label for="subject_id">Subject</label>
								<select name="subject_id" id="subject_id" class="form-control select2" required>
									<option value="">Select Subject</option>
									<?php 
									if(isset($subject_id)):
									$subjects = $conn->query("SELECT * FROM subjects WHERE id = $subject_id");
									while($row = $subjects->fetch_assoc()):
									?>
									<option value="<?php echo $row['id'] ?>" selected><?php echo $row['subject'] ?></option>
									<?php 
									endwhile;
									endif;
									?>
								</select>
							</div>
								<!-- Room -->
							<div class="form-group">
								<label for="room_id">Room/Laboratory</label>
								<select name="room_id" id="room_id" class="form-control select2" required>
									<option value="">Select Room</option>
									<?php 
									$rooms = $conn->query("SELECT * FROM rooms ORDER BY name ASC");
									while($row = $rooms->fetch_assoc()):
									?>
									<option value="<?php echo $row['id'] ?>" <?php echo isset($room_id) && $room_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['name'] ?> (<?php echo $row['type'] ?>)</option>
									<?php endwhile; ?>
								</select>
							</div>
								<!-- Weekly Schedule Options -->
							<div class="form-group">
								<div class="custom-control custom-checkbox">
									<input type="checkbox" class="custom-control-input" id="is_repeating" name="is_repeating" <?php echo isset($is_repeating) && $is_repeating ? 'checked' : '' ?>>
									<label class="custom-control-label" for="is_repeating">Repeating Schedule</label>
								</div>
							</div>
							<div id="dow_group">
								<div class="form-group">
									<label>Days of Week</label>
									<div class="d-flex flex-wrap mb-2">
										<div class="custom-control custom-checkbox mr-3 mb-2" style="min-width: 100px;">
											<input type="checkbox" class="custom-control-input day-checkbox" id="day_0" name="dow[]" value="0" <?php echo isset($dow_arr) && in_array('0', $dow_arr) ? 'checked' : '' ?>>
											<label class="custom-control-label" for="day_0">Sunday</label>
										</div>
										<div class="custom-control custom-checkbox mr-3 mb-2" style="min-width: 100px;">
											<input type="checkbox" class="custom-control-input day-checkbox" id="day_1" name="dow[]" value="1" <?php echo isset($dow_arr) && in_array('1', $dow_arr) ? 'checked' : '' ?>>
											<label class="custom-control-label" for="day_1">Monday</label>
										</div>
										<div class="custom-control custom-checkbox mr-3 mb-2" style="min-width: 100px;">
											<input type="checkbox" class="custom-control-input day-checkbox" id="day_2" name="dow[]" value="2" <?php echo isset($dow_arr) && in_array('2', $dow_arr) ? 'checked' : '' ?>>
											<label class="custom-control-label" for="day_2">Tuesday</label>
										</div>
										<div class="custom-control custom-checkbox mr-3 mb-2" style="min-width: 100px;">
											<input type="checkbox" class="custom-control-input day-checkbox" id="day_3" name="dow[]" value="3" <?php echo isset($dow_arr) && in_array('3', $dow_arr) ? 'checked' : '' ?>>
											<label class="custom-control-label" for="day_3">Wednesday</label>
										</div>
										<div class="custom-control custom-checkbox mr-3 mb-2" style="min-width: 100px;">
											<input type="checkbox" class="custom-control-input day-checkbox" id="day_4" name="dow[]" value="4" <?php echo isset($dow_arr) && in_array('4', $dow_arr) ? 'checked' : '' ?>>
											<label class="custom-control-label" for="day_4">Thursday</label>
										</div>
										<div class="custom-control custom-checkbox mr-3 mb-2" style="min-width: 100px;">
											<input type="checkbox" class="custom-control-input day-checkbox" id="day_5" name="dow[]" value="5" <?php echo isset($dow_arr) && in_array('5', $dow_arr) ? 'checked' : '' ?>>
											<label class="custom-control-label" for="day_5">Friday</label>
										</div>
										<div class="custom-control custom-checkbox mr-3 mb-2" style="min-width: 100px;">
											<input type="checkbox" class="custom-control-input day-checkbox" id="day_6" name="dow[]" value="6" <?php echo isset($dow_arr) && in_array('6', $dow_arr) ? 'checked' : '' ?>>
											<label class="custom-control-label" for="day_6">Saturday</label>
										</div>
									</div>
									<div id="selected_days_display" class="badge badge-primary p-2 mt-2" style="display:none;">
										Selected days: <span id="selected_days_text"></span>
									</div>
								</div>
							</div>
							<div id="month_range">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="month_from">Month From</label>
											<input type="month" name="month_from" id="month_from" class="form-control" value="<?php echo isset($month_from) ? $month_from : '' ?>">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="month_to">Month To</label>
											<input type="month" name="month_to" id="month_to" class="form-control" value="<?php echo isset($month_to) ? $month_to : '' ?>">
										</div>
									</div>
								</div>
							</div>
								<!-- Time Range -->
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="time_from">Time From</label>
										<input type="time" name="time_from" id="time_from" class="form-control" value="<?php echo isset($time_from) ? $time_from : '' ?>" required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="time_to">Time To</label>
										<input type="time" name="time_to" id="time_to" class="form-control" value="<?php echo isset($time_to) ? $time_to : '' ?>" required>
									</div>
								</div>
							</div>
							<!-- Hidden year_level field that will be set by JavaScript -->
							<input type="hidden" name="year_level" id="actual_year_level" value="<?php echo isset($year_level) ? $year_level : '' ?>">
							<div class="form-group text-right">
								<button class="btn btn-primary mr-2" type="submit">Save</button>
								<button type="button" class="btn btn-secondary" id="cancel-btn">Cancel</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
// Add debugging and fix any potentially missing elements
document.addEventListener('DOMContentLoaded', function() {
    console.log('Schedule form initialized');
    
    // Ensure course_id and strand_id hidden fields exist
    if (document.getElementById('manage-schedule')) {
        if (!document.querySelector('input[name="course_id"]')) {
            var courseField = document.createElement('input');
            courseField.type = 'hidden';
            courseField.name = 'course_id';
            courseField.value = '';
            document.getElementById('manage-schedule').appendChild(courseField);
            console.log('Added missing course_id field');
        }
        
        if (!document.querySelector('input[name="strand_id"]')) {
            var strandField = document.createElement('input');
            strandField.type = 'hidden';
            strandField.name = 'strand_id';
            strandField.value = '';
            document.getElementById('manage-schedule').appendChild(strandField);
            console.log('Added missing strand_id field');
        }
    }
});

$(document).ready(function(){
	// Don't hide modal footer in standalone view
	if ($('#uni_modal .modal-footer').length && window.location.href.indexOf('standalone_view=true') === -1) {
		$('#uni_modal .modal-footer').hide();
	}
	
	// Initialize Select2
	if ($('.select2').length) {
		$('.select2').select2({
			placeholder: "Please select here",
			width: "100%"
		});
	}
	
	// Handle day selection
	$(document).on('change', '.day-checkbox', function() {
		updateSelectedDaysDisplay();
	});
	
	// Function to update selected days display
	function updateSelectedDaysDisplay() {
		const selectedDays = [];
		const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		
		// Check each day checkbox
		$('.day-checkbox:checked').each(function() {
			const dayIndex = $(this).val();
			selectedDays.push(dayNames[dayIndex]);
		});
		
		// Update display
		if(selectedDays.length > 0 && $('#selected_days_text').length && $('#selected_days_display').length) {
			$('#selected_days_text').text(selectedDays.join(', '));
			$('#selected_days_display').show();
		} else if ($('#selected_days_display').length) {
			$('#selected_days_display').hide();
		}
	}
	
	// Initialize selected days display
	updateSelectedDaysDisplay();
	
	// Check if we're in edit mode
	var isEditMode = $('input[name="id"]').val() ? true : false;

	// Handle schedule type change
	$('#schedule_type').change(function(){
		var type = $(this).val();
		if(type === 'college'){
			$('#college_fields').show();
			$('#shs_fields').hide();
			
			// Update actual year level with college year
			$('#actual_year_level').val($('#college_year').val());
			
			// Reset SHS fields
			if(!isEditMode) {
				$('#strand_id').val('').trigger('change');
			}
		} else if(type === 'shs'){
			$('#college_fields').hide();
			$('#shs_fields').show();
			
			// Update actual year level with grade level
			$('#actual_year_level').val($('#grade_level').val());
			
			// Reset college fields
			if(!isEditMode) {
				$('#department_id').val('').trigger('change');
			}
		} else {
			$('#college_fields').show();
			$('#shs_fields').show();
		}
	});

	// Handle student type change
	$('#student_type').change(function(){
		$('#college_fields, #shs_fields').hide();
		let type = $(this).val();
		if(type == 'college') {
			$('#college_fields').show();
		} else if(type == 'shs') {
			$('#shs_fields').show();
		}
	});

	// Handle repeating checkbox
	function toggleRepeatingFields(){
		if($('#is_repeating').is(':checked')){
			$('#dow_group, #month_range').show();
		} else {
			$('#dow_group, #month_range').hide();
		}
	}
	
	$('#is_repeating').change(toggleRepeatingFields);
	toggleRepeatingFields();
	
	// Dedicated handlers for subject loading
	$('#department_id').change(function(){
		var dept_id = $('#department_id').val();
		if(dept_id) {
			// If in edit mode and current subject exists, show notification
			if(isEditMode && $('#subject_id').val()) {
				alert_toast('Department changed - please verify subject selection', 'info');
			}
			// If department ID is selected, load subjects
			loadSubjects('college');
		}
	});
	
	$('#strand_id').change(function(){
		var strand_id = $('#strand_id').val();
		if(strand_id) {
			// If in edit mode and current subject exists, show notification
			if(isEditMode && $('#subject_id').val()) {
				alert_toast('Strand changed - please verify subject selection', 'info');
			}
			// If strand ID is selected, load subjects
			loadSubjects('shs');
		}
	});
	
	// Load college sections when department and year level are selected
	$('#department, #year_level').change(function(){
		let dept = $('#department').val();
		let year = $('#year_level').val();
		
		if(dept && year) {
			$.ajax({
				url: 'ajax.php?action=get_college_sections',
				method: 'POST',
				data: {
					department_id: dept,
					year_level: year
				},
				success: function(response){
					$('#college_section').html(response);
				}
			});
		}
	});

	// Load SHS sections when strand and grade level are selected
	$('#strand, #grade_level').change(function(){
		let strand = $('#strand').val();
		let grade = $('#grade_level').val();
		
		if(strand && grade) {
			$.ajax({
				url: 'ajax.php?action=get_shs_sections',
				method: 'POST',
				data: {
					strand_id: strand,
					grade_level: grade
				},
				success: function(response){
					$('#shs_section').html(response);
				}
			});
		}
	});
	
	// Load sections based on department selection 
	$('#department_id, #college_year').change(function(){
		var dept_id = $('#department_id').val();
		var year = $('#college_year').val();
		
		if(dept_id && year){
			// Show loading indicator
			$('#section_id').html('<option value="">Loading sections...</option>').prop('disabled', true);
			
			$.ajax({
				url: 'ajax.php?action=get_college_sections',
				method: 'POST',
				data: {department_id: dept_id, year_level: year},
				dataType: 'json', // Explicitly expect JSON
				success: function(resp){
					try {
						var opts = '<option value="">Select Section</option>';
						var section_id = <?php echo isset($section_id) ? $section_id : 0; ?>;
						
						// Check if we received an error response
						if(resp.status === 0 && resp.msg) {
							console.error("Server error:", resp.msg);
							$('#section_id').html('<option value="">Error: ' + resp.msg + '</option>').prop('disabled', true);
							return;
						}
						
						// Handle array response
						if(Array.isArray(resp)){
							if(resp.length === 0) {
								$('#section_id').html('<option value="">No sections found</option>').prop('disabled', true);
								return;
							}
							resp.forEach(function(section){
								var selected = (section.id == section_id) ? 'selected' : '';
								opts += `<option value="${section.id}" ${selected}>${section.name}</option>`;
							});
							$('#section_id').html(opts).prop('disabled', false);
						} else {
							console.error("Unexpected response format:", resp);
							$('#section_id').html('<option value="">Error loading sections</option>').prop('disabled', true);
						}
					} catch(e) {
						console.error("Error handling response:", e);
						$('#section_id').html('<option value="">Error processing sections</option>').prop('disabled', true);
					}
				},
				error: function(xhr, status, error) {
					console.error("AJAX error:", status, error);
					if(xhr.responseText) {
						console.error("Response:", xhr.responseText.substring(0, 200) + "...");
					}
					$('#section_id').html('<option value="">Error loading sections</option>').prop('disabled', true);
				}
			});
		}
	});
	
	// Load sections based on strand selection
	$('#strand_id, #grade_level').change(function(){
		var strand_id = $('#strand_id').val();
		var grade = $('#grade_level').val();
		
		if(strand_id && grade){
			// Show loading indicator
			$('#section_id').html('<option value="">Loading sections...</option>').prop('disabled', true);
			
			$.ajax({
				url: 'ajax.php?action=get_sections',
				method: 'POST',
				data: {strand_id: strand_id, grade_level: grade},
				dataType: 'json', // Explicitly expect JSON
				success: function(resp){
					try {
						var opts = '<option value="">Select Section</option>';
						var section_id = <?php echo isset($section_id) ? $section_id : 0; ?>;
						
						// Check if we received an error response
						if(resp.status === 0) {
							console.error("Server error:", resp.msg || 'Unknown error');
							$('#section_id').html('<option value="">Error loading sections</option>').prop('disabled', true);
							return;
						}
						
						// Handle success response
						if(resp.status === 1 && Array.isArray(resp.data)){
							if(resp.data.length === 0) {
								$('#section_id').html('<option value="">No sections found</option>').prop('disabled', true);
								return;
							}
							
							resp.data.forEach(function(section){
								var selected = (section.id == section_id) ? 'selected' : '';
								opts += `<option value="${section.id}" ${selected}>${section.name}</option>`;
							});
							
							$('#section_id').html(opts).prop('disabled', false);
						} else {
							console.error("Unexpected response format:", resp);
							$('#section_id').html('<option value="">Error loading sections</option>').prop('disabled', true);
						}
					} catch(e) {
						console.error("Error handling response:", e);
						$('#section_id').html('<option value="">Error processing sections</option>').prop('disabled', true);
					}
				},
				error: function(xhr, status, error) {
					console.error("AJAX error:", status, error);
					if(xhr.responseText) {
						console.error("Response:", xhr.responseText.substring(0, 200) + "...");
					}
					$('#section_id').html('<option value="">Error loading sections</option>').prop('disabled', true);
				}
			});
		}
	});
	
	// Function to load subjects based on department or strand
	function loadSubjects(type, selectedSubjectId = 0) {
		var filter_id = '';
		var action = '';
		
		if(type === 'college') {
			filter_id = $('#department_id').val();
			action = 'get_department_subjects';
			if(!filter_id) {
				return;
			}
		} else if(type === 'shs') {
			filter_id = $('#strand_id').val();
			action = 'get_strand_subjects';
			if(!filter_id) {
				return;
			}
		} else {
			return;
		}
		
		// Store current selection if any
		var currentSubjectId = $('#subject_id').val();
		
		// Show loading indicator
		$('#subject_id').html('<option value="">Loading subjects...</option>').prop('disabled', true);
		
		$.ajax({
			url: 'ajax.php?action=' + action,
			method: 'POST',
			data: type === 'college' ? { department_id: filter_id } : { strand_id: filter_id },
			dataType: 'json', // Explicitly expect JSON response
			success: function(resp) {
				try {
					var opts = '<option value="">Select Subject</option>';
					var subject_id = currentSubjectId || <?php echo isset($subject_id) ? $subject_id : 0; ?>;
					
					// Check if response indicates an error
					if(resp.status === 0 && resp.message) {
						console.error("Server error:", resp.message);
						$('#subject_id').html('<option value="">Error: ' + resp.message + '</option>').prop('disabled', true);
						return;
					}
					
					// Handle regular array response
					if(Array.isArray(resp)) {
						if(resp.length === 0) {
							$('#subject_id').html('<option value="">No subjects found</option>').prop('disabled', true);
							return;
						}
						
						resp.forEach(function(subject) {
							var selected = (subject.id == subject_id) ? 'selected' : '';
							opts += `<option value="${subject.id}" ${selected}>${subject.subject}</option>`;
						});
						
						$('#subject_id').html(opts).prop('disabled', false);
					} else {
						console.error("Unexpected response format:", resp);
						$('#subject_id').html('<option value="">Error loading subjects</option>').prop('disabled', true);
					}
				} catch(e) {
					console.error("Error handling response:", e);
					$('#subject_id').html('<option value="">Error processing subjects</option>').prop('disabled', true);
				}
			},
			error: function(xhr, status, error){
				console.error("AJAX error:", status, error);
				if(xhr.responseText) {
					console.error("Response:", xhr.responseText);
					try {
						var jsonResponse = JSON.parse(xhr.responseText);
						alert_toast("Error: " + (jsonResponse.message || jsonResponse.msg || error), 'danger');
					} catch(e) {
						alert_toast("Error: Could not connect to server - " + error, 'danger');
					}
				} else {
					alert_toast("Error: Could not connect to server", 'danger');
				}
				end_load();
			}
		});
	}
	
	// Apply initial schedule type logic on page load
	$('#schedule_type').trigger('change');
	
	// In edit mode, we need to load sections and subjects on page load
	if(isEditMode) {
		var scheduleType = $('#schedule_type').val();
		
		if(scheduleType === 'college') {
			// Trigger the change event to load sections and subjects
			if($('#department_id').val() && $('#college_year').val()) {
				setTimeout(function() {
					$('#department_id, #college_year').trigger('change');
				}, 300);
			}
		} else if(scheduleType === 'shs') {
			// Trigger the change event to load sections and subjects
			if($('#strand_id').val() && $('#grade_level').val()) {
				setTimeout(function() {
					$('#strand_id, #grade_level').trigger('change');
				}, 300);
			}
		}
	}
	
	// Form submission
	$('#manage-schedule').submit(function(e){
		e.preventDefault();
		
		// Ensure proper IDs are set based on schedule type
		var scheduleType = $('#schedule_type').val();
		
		// Reset any duplicate fields first
		$('input[name="strand_id"]').remove();
		
		if(scheduleType === 'college') {
            // Set the actual year level from the college year field
            $('#actual_year_level').val($('#college_year').val());
            
            // Ensure department_id is mapped to course_id
            var departmentId = $('#department_id').val();
            if(departmentId) {
                // Create a hidden input for course_id if it doesn't exist
                if($('input[name="course_id"]').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'course_id',
                        value: departmentId
                    }).appendTo('#manage-schedule');
                } else {
                    $('input[name="course_id"]').val(departmentId);
                }
            }
            
            // Clear strand_id value
            $('#strand_id').val('');
            
        } else if(scheduleType === 'shs') {
            // Set the actual year level from the grade level field
            $('#actual_year_level').val($('#grade_level').val());
            
            // Clear course_id value
            if($('input[name="course_id"]').length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'course_id',
                    value: ''
                }).appendTo('#manage-schedule');
            } else {
                $('input[name="course_id"]').val('');
            }
        }
		
		// Validate time
		var timeFrom = $('#time_from').val();
		var timeTo = $('#time_to').val();
		if(timeFrom && timeTo && timeFrom >= timeTo) {
			alert_toast('End time must be after start time.', 'danger');
			return;
		}
		
		// Collect days of week for repeating schedules
		var dow = [];
		if($('#is_repeating').is(':checked')) {
			$('.day-checkbox:checked').each(function() {
				dow.push($(this).val());
			});
			
			if(dow.length === 0) {
				alert_toast('Please select at least one day of the week for repeating schedules.', 'warning');
				end_load();
				return;
			}
			
			// Validate month range for repeating schedules
			var monthFrom = $('#month_from').val();
			var monthTo = $('#month_to').val();
			
			if(monthFrom && monthTo && monthFrom > monthTo) {
				alert_toast('End month must be after start month for repeating schedules.', 'warning');
				return;
			}
			
			// Require month range for repeating schedules
			if(!monthFrom || !monthTo) {
				alert_toast('Please specify both start and end months for repeating schedules.', 'warning');
				return;
			}
		}
		
		// Get section ID for refreshing student info page if needed
		var sectionId = $('#section_id').val();
		
		// Check if we came from student_info via localStorage
		var fromStudentInfo = localStorage.getItem('last_edited_section') !== null;
		
		// Show loading indicator
		start_load();
		
		// Log form data for debugging
        console.log('Form data:', $(this).serialize());
        
        // Log form fields
        console.log('Schedule Type:', scheduleType);
        console.log('Faculty:', $('#faculty_id').val());
        console.log('Department ID:', $('#department_id').val());
        console.log('Strand ID:', $('#strand_id').val());
        console.log('Year Level (College):', $('#college_year').val());
        console.log('Year Level (SHS):', $('#grade_level').val());
        console.log('Section ID:', $('#section_id').val());
        console.log('Subject ID:', $('#subject_id').val());
        console.log('Room ID:', $('#room_id').val());
        console.log('Is Repeating:', $('#is_repeating').is(':checked'));
        console.log('DOW:', dow);
        console.log('Time From:', timeFrom);
        console.log('Time To:', timeTo);
		
		$.ajax({
			url: 'ajax.php?action=save_schedule',
			method: 'POST',
			data: $(this).serialize(),
			dataType: 'json', // Explicitly expect JSON response
			success: function(resp){
				try {
					// Log the raw response for debugging
					console.log('Raw save response:', resp);
					
					// Handle empty response case
					if (!resp) {
						// No response means success in some cases
						alert_toast("Schedule saved successfully (no response)", 'success');
						
						// Create and dispatch event to notify about schedule update
						if (sectionId) {
						    // Try to send notification via custom event
						    try {
						        var event = new CustomEvent('schedule-updated', {
						            detail: {
						                section_id: sectionId,
						                timestamp: new Date().getTime()
						            },
						            bubbles: true,
						            cancelable: true
						        });
						        document.dispatchEvent(event);
						        console.log('Notified about schedule update for section:', sectionId);
						    } catch(e) {
						        console.error('Failed to dispatch schedule update event:', e);
						    }
						}
						
						// Check if we need to return to different pages
						var urlParams = new URLSearchParams(window.location.search);
						var scheduleId = <?php echo isset($id) ? $id : 0; ?>;
						var fromStandaloneView = urlParams.get('standalone_view') === 'true';
						var fromUpdatePage = urlParams.get('from_update') === 'true';

						console.log('From standalone view:', fromStandaloneView);
						console.log('From update page:', fromUpdatePage);

						setTimeout(function() {
						    if (fromStandaloneView && scheduleId) {
							    // Return to view_schedule as a standalone page
							    console.log('Returning to standalone view_schedule with ID:', scheduleId);
							    window.location.href = 'view_schedule.php?id=' + scheduleId;
						    } else if (fromUpdatePage) {
							    // Return to update_schedule page
							    console.log('Returning to update_schedule');
							    window.location.href = 'update_schedule.php';
						    } else if (!returnToViewSchedule()) {
							    // If not returning to view_schedule modal, go to regular schedule page
							    window.location.href = 'index.php?page=schedule';
						    }
						}, 1500);
						return;
					}
					
					// Since we're using dataType:'json', jQuery already parsed the JSON response
					if(resp.status === 'success' || resp.status === 1) {
						alert_toast("Schedule saved successfully", 'success');
						
						// Create and dispatch event to notify about schedule update
						if (sectionId) {
						    // Try to send notification via custom event
						    try {
						        var event = new CustomEvent('schedule-updated', {
						            detail: {
						                section_id: sectionId,
						                timestamp: new Date().getTime()
						            },
						            bubbles: true,
						            cancelable: true
						        });
						        document.dispatchEvent(event);
						        console.log('Notified about schedule update for section:', sectionId);
						    } catch(e) {
						        console.error('Failed to dispatch schedule update event:', e);
						    }
						}
						
						// Check if we need to return to different pages
						var urlParams = new URLSearchParams(window.location.search);
						var scheduleId = <?php echo isset($id) ? $id : 0; ?>;
						var fromStandaloneView = urlParams.get('standalone_view') === 'true';
						var fromUpdatePage = urlParams.get('from_update') === 'true';

						console.log('From standalone view:', fromStandaloneView);
						console.log('From update page:', fromUpdatePage);

						setTimeout(function() {
						    if (fromStandaloneView && scheduleId) {
							    // Return to view_schedule as a standalone page
							    console.log('Returning to standalone view_schedule with ID:', scheduleId);
							    window.location.href = 'view_schedule.php?id=' + scheduleId;
						    } else if (fromUpdatePage) {
							    // Return to update_schedule page
							    console.log('Returning to update_schedule');
							    window.location.href = 'update_schedule.php';
						    } else if (!returnToViewSchedule()) {
							    // If not returning to view_schedule modal, go to regular schedule page
							    window.location.href = 'index.php?page=schedule';
						    }
						}, 1500);
					} else if(resp.status === 'conflict') {
						alert_toast(resp.message || "Schedule conflict detected", 'danger');
						end_load();
					} else if(resp.status === 'error' || resp.status === 0) {
						alert_toast(resp.msg || resp.message || "An error occurred", 'danger');
						end_load();
					} else {
						alert_toast("Unable to save schedule", 'danger');
						console.error("Unexpected response format:", resp);
						end_load();
					}
				} catch(e) {
					// Error in handling the response
					console.error("Error handling response:", e);
					console.error("Raw response:", resp);
					alert_toast("An error occurred while saving the schedule", 'danger');
					end_load();
				}
			},
			error: function(xhr, status, error){
				console.error("AJAX error:", status, error);
				if(xhr.responseText) {
					console.error("Response:", xhr.responseText);
					try {
						var jsonResponse = JSON.parse(xhr.responseText);
						alert_toast("Error: " + (jsonResponse.message || jsonResponse.msg || error), 'danger');
					} catch(e) {
						alert_toast("Error: Could not connect to server - " + error, 'danger');
					}
				} else {
					alert_toast("Error: Could not connect to server", 'danger');
				}
				end_load();
			}
		});
	});
	
	// Update actual year level when college year changes
	$('#college_year').change(function() {
		if($('#schedule_type').val() === 'college') {
			$('#actual_year_level').val($(this).val());
		}
	});
	
	// Update actual year level when grade level changes
	$('#grade_level').change(function() {
		if($('#schedule_type').val() === 'shs') {
			$('#actual_year_level').val($(this).val());
		}
	});
});

// Add event handler for cancel button
document.addEventListener('DOMContentLoaded', function() {
    var cancelBtn = document.getElementById('cancel-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            console.log('Cancel button clicked');
            
            // Check if we came from different pages
            var urlParams = new URLSearchParams(window.location.search);
            var scheduleId = <?php echo isset($id) ? $id : 0; ?>;
            var fromStandaloneView = urlParams.get('standalone_view') === 'true';
            var fromUpdatePage = urlParams.get('from_update') === 'true';
            var fromFacultyView = urlParams.get('from_faculty') === 'true';
            
            console.log('From standalone view:', fromStandaloneView);
            console.log('From update page:', fromUpdatePage);
            console.log('From faculty view:', fromFacultyView);
            
            if (fromStandaloneView && scheduleId) {
                // Return to view_schedule as a standalone page
                console.log('Returning to standalone view_schedule with ID:', scheduleId);
                window.location.href = 'view_schedule.php?id=' + scheduleId + '&standalone_view=true';
            } else if (fromUpdatePage) {
                // Return to update_schedule page
                console.log('Returning to update_schedule');
                window.location.href = 'update_schedule.php';
            } else if (fromFacultyView) {
                // If coming from faculty view, go back to faculty page
                console.log('Returning to faculty view');
                var facultyId = urlParams.get('faculty_id');
                if (facultyId) {
                    window.location.href = 'index.php?page=faculty_view&id=' + facultyId;
                } else {
                    window.location.href = 'index.php?page=faculty';
                }
            } else {
                // Otherwise go to schedule page
                window.location.href = 'index.php?page=schedule';
            }
        });
    }
});

// Add uni_modal function definition if it doesn't exist in this context
if (typeof uni_modal !== 'function') {
    function uni_modal(title, url, size = '') {
        console.log('Called uni_modal with:', title, url, size);
        
        // Add faculty ID to URLs if needed
        if (url.includes('view_schedule.php') || url.includes('manage_schedule.php')) {
            const urlParams = new URLSearchParams(window.location.search);
            const facultyId = urlParams.get('faculty_id');
            
            if (facultyId && !url.includes('faculty_id=')) {
                url += (url.includes('?') ? '&' : '?') + 'faculty_id=' + facultyId + '&from_faculty=true';
                console.log('Added faculty parameters to URL:', url);
            }
        }
        
        // When in iframe context, call parent's uni_modal
        if (window.parent && window.parent.uni_modal) {
            console.log('Using parent window uni_modal');
            window.parent.uni_modal(title, url, size);
        } else {
            // Direct implementation as fallback
            console.log('Using local uni_modal implementation');
            $.ajax({
                url: url,
                error: function(xhr, status, error) {
                    console.error('Modal error:', error);
                    alert("An error occurred while loading content");
                },
                success: function(html) {
                    try {
                        $('#uni_modal .modal-title').html(title);
                        $('#uni_modal .modal-body').html(html);
                        
                        // Apply size if specified
                        if (size != '') {
                            $('#uni_modal .modal-dialog').removeClass('modal-md').addClass(size);
                        } else {
                            $('#uni_modal .modal-dialog').removeClass('large mid-large').addClass('modal-md');
                        }
                        
                        // Don't hide modal footer in standalone view
                        if (window.location.href.indexOf('standalone_view=true') === -1 && 
                            window.location.href.indexOf('from_faculty=true') === -1) {
                            $('#uni_modal .modal-footer').hide();
                        } else {
                            $('#uni_modal .modal-footer').show();
                        }
                        
                        $('#uni_modal').modal('show');
                    } catch (e) {
                        console.error('Error showing modal:', e);
                        alert("Error displaying content");
                    }
                }
            });
        }
    }
}

// Add debugging to check URL parameters
document.addEventListener('DOMContentLoaded', function() {
    // Debug URL parameters
    var urlParams = new URLSearchParams(window.location.search);
    var scheduleId = <?php echo isset($id) ? $id : 0; ?>;
    var returnToView = urlParams.get('return_to_view') === 'true';
    
    console.log('URL Parameters Debug:');
    console.log('- Full URL:', window.location.href);
    console.log('- return_to_view parameter:', urlParams.get('return_to_view'));
    console.log('- returnToView value:', returnToView);
    console.log('- scheduleId:', scheduleId);
    console.log('- Is in iframe:', window.top !== window.self);
    console.log('- Parent has uni_modal:', window.parent && typeof window.parent.uni_modal === 'function');
});

// Get return URL from query parameters if it exists
function getReturnUrl() {
    var urlParams = new URLSearchParams(window.location.search);
    var returnUrl = urlParams.get('return');
    
    // Default to schedule page if no return URL is specified
    if (!returnUrl) {
        return 'index.php?page=schedule';
    }
    
    return returnUrl;
}

// Redirect to the appropriate page after form submission or cancel
function redirectAfterAction(additionalDelay = 0) {
    var returnUrl = getReturnUrl();
    console.log('Redirecting to:', returnUrl);
    
    setTimeout(function() {
        window.location.href = returnUrl;
    }, 1500 + additionalDelay);
}

// Function to return to view_schedule
function returnToViewSchedule() {
    // Check if we should return to view_schedule
    var returnToViewId = localStorage.getItem('return_to_view');
    console.log('Return to view ID:', returnToViewId);
    
    if (returnToViewId) {
        // Clear the localStorage flag
        localStorage.removeItem('return_to_view');
        
        // Check if we're in a modal or standalone view
        var isInModal = $('#uni_modal').length && $('#uni_modal').is(':visible');
        
        if (isInModal) {
            // Close the current edit modal
            $('#uni_modal').modal('hide');
            
            // Wait for edit modal to fully close
            $('#uni_modal').on('hidden.bs.modal', function (e) {
                // Remove event handler to prevent multiple triggers
                $('#uni_modal').off('hidden.bs.modal');
                
                // Open view_schedule in a new modal
                console.log('Returning to view_schedule with ID:', returnToViewId);
                setTimeout(function() {
                    uni_modal('View Schedule Details', 'view_schedule.php?id=' + returnToViewId, 'mid-large');
                }, 300);
            });
        } else {
            // In standalone view, just redirect
            console.log('Redirecting to standalone view_schedule with ID:', returnToViewId);
            window.location.href = 'view_schedule.php?id=' + returnToViewId + '&standalone_view=true';
        }
        
        return true;
    }
    
    return false;
}
</script>