<?php include 'db_connect.php' ?>
<?php include 'header.php' ?>
<?php include 'topbar.php' ?>
<?php include 'navbar.php' ?>
<?php
// Check if viewing as faculty
$is_faculty_view = isset($_GET['faculty_id']) && !empty($_GET['faculty_id']);

if($is_faculty_view) {
    // Load faculty information and their schedules
    $faculty_id = $_GET['faculty_id'];
    
    // Get faculty details
    $faculty_qry = $conn->query("SELECT id, id_no, firstname, lastname, middlename, 
                                CONCAT(lastname, ', ', firstname, ' ', COALESCE(middlename, '')) AS full_name 
                                FROM faculty WHERE id = " . $faculty_id);
    
    if($faculty_qry->num_rows == 0) {
        die("Faculty not found.");
    }
    
    $faculty = $faculty_qry->fetch_assoc();
    
    // Get faculty schedules
    $schedule_qry = $conn->query("SELECT s.*, 
                    sub.subject AS subject_name,
                    c.course AS course_name, 
                    st.name AS strand_name, 
                    sec.name AS section_name,
                    sec.year_level AS year_level,
                    r.name AS location 
                    FROM schedules s 
                    LEFT JOIN subjects sub ON s.subject_id = sub.id
                    LEFT JOIN courses c ON s.course_id = c.id 
                    LEFT JOIN strands st ON s.strand_id = st.id 
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    LEFT JOIN rooms r ON s.room_id = r.id 
                    WHERE s.faculty_id = " . $faculty_id);
    
    // Define days of week
    $day_names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
}
else if(isset($_GET['id'])){
	$qry = $conn->query("SELECT s.*, 
                            CONCAT(f.lastname, ', ', f.firstname, ' ', COALESCE(f.middlename, '')) AS faculty_name, 
                            c.course AS course_name, 
                            st.name AS strand_name, 
                            sec.name AS section_name,
                            sec.year_level AS year_level,
                            sub.subject AS subject_name,
                            r.name AS location 
                     FROM schedules s 
                     LEFT JOIN faculty f ON s.faculty_id = f.id 
                     LEFT JOIN courses c ON s.course_id = c.id 
                     LEFT JOIN strands st ON s.strand_id = st.id 
                     LEFT JOIN sections sec ON s.section_id = sec.id
                     LEFT JOIN subjects sub ON s.subject_id = sub.id
                     LEFT JOIN rooms r ON s.room_id = r.id 
                     WHERE s.id = " . $_GET['id']);
	if ($qry->num_rows > 0) {
		foreach ($qry->fetch_assoc() as $k => $v) {
			$$k = $v;
		}
	} else {
		die("Schedule not found.");
	}
	
	// Format days of week for display
	$days_display = '';
	if(isset($dow) && !empty($dow)) {
		$days_arr = explode(',', $dow);
		$day_names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		$formatted_days = [];
		
		foreach($days_arr as $day_index) {
			if(isset($day_names[$day_index])) {
				$formatted_days[] = $day_names[$day_index];
			}
		}
		
		$days_display = implode(', ', $formatted_days);
	} else {
		$days_display = 'Not specified';
	}
	
	// Format year level display based on whether it's strand or department
	$year_level_display = '';
	if(isset($year_level) && !empty($year_level)) {
	    if(isset($strand_name) && !empty($strand_name)) {
	        // For SHS
	        $year_level_display = "Grade " . $year_level;
	    } else if(isset($course_name) && !empty($course_name)) {
	        // For College
	        $suffix = '';
	        if($year_level == 1) $suffix = 'st';
	        else if($year_level == 2) $suffix = 'nd';
	        else if($year_level == 3) $suffix = 'rd';
	        else $suffix = 'th';
	        
	        $year_level_display = $year_level . $suffix . " Year";
	    }
	}
}
else {
    die("No schedule specified.");
}

// Helper function to format days of week
function formatDaysOfWeek($dow) {
    global $day_names;
    $days_display = '';
    
    if(isset($dow) && !empty($dow)) {
        $days_arr = explode(',', $dow);
        $formatted_days = [];
        
        foreach($days_arr as $day_index) {
            if(isset($day_names[$day_index])) {
                $formatted_days[] = $day_names[$day_index];
            }
        }
        
        $days_display = implode(', ', $formatted_days);
    } else {
        $days_display = 'Not specified';
    }
    
    return $days_display;
}

// Helper function to format year level
function formatYearLevel($year_level, $is_shs = false) {
    if(!isset($year_level) || empty($year_level)) {
        return '';
    }
    
    if($is_shs) {
        // For SHS
        return "Grade " . $year_level;
    } else {
        // For College
        $suffix = '';
        if($year_level == 1) $suffix = 'st';
        else if($year_level == 2) $suffix = 'nd';
        else if($year_level == 3) $suffix = 'rd';
        else $suffix = 'th';
        
        return $year_level . $suffix . " Year";
    }
}

?>
<div class="container-fluid">
    <?php if($is_faculty_view): ?>
    <!-- Faculty Schedule View -->
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="m-0">Faculty Schedule: <?php echo ucwords($faculty['full_name']); ?></h5>
            <div class="action-buttons">
                <button class="btn btn-light btn-sm" onclick="printSchedule()">
                    <i class="fa fa-print"></i> Print
                </button>
                <button class="btn btn-secondary btn-sm" onclick="window.location.href='login.php'">
                    <i class="fa fa-sign-out"></i> Logout
                </button>
            </div>
        </div>
        <div class="card-body" id="schedule-container">
            <div class="faculty-info mb-4">
                <p><strong>ID Number:</strong> <?php echo $faculty['id_no']; ?></p>
                <p><strong>Name:</strong> <?php echo ucwords($faculty['full_name']); ?></p>
            </div>
            
            <?php if($schedule_qry->num_rows > 0): ?>
                <h5 class="border-bottom pb-2">Teaching Schedule</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Section</th>
                                <th>Department/Strand</th>
                                <th>Year/Grade Level</th>
                                <th>Days</th>
                                <th>Time</th>
                                <th>Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $schedule_qry->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['subject_name'] ?? 'N/A'; ?></td>
                                    <td><?php echo $row['section_name'] ?? 'N/A'; ?></td>
                                    <td><?php echo !empty($row['course_name']) ? $row['course_name'] : (!empty($row['strand_name']) ? $row['strand_name'] : 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                            $is_shs = !empty($row['strand_name']);
                                            echo formatYearLevel($row['year_level'], $is_shs); 
                                        ?>
                                    </td>
                                    <td><?php echo formatDaysOfWeek($row['dow']); ?></td>
                                    <td>
                                        <?php 
                                        if(isset($row['time_from']) && isset($row['time_to'])) {
                                            echo date('h:i A', strtotime($row['time_from'])) . ' - ' . date('h:i A', strtotime($row['time_to']));
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $row['location'] ?? 'N/A'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No schedules found for this faculty.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Single Schedule View -->
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="m-0">Schedule Details</h5>
            <div class="action-buttons">
                <a href="manage_schedule.php?id=<?php echo $id ?>&standalone_view=true" class="btn btn-light btn-sm" id="edit">
                    <i class="fa fa-edit"></i> Edit
                </a>
                <button class="btn btn-danger btn-sm" type="button" id="delete_schedule" data-id="<?php echo $id ?>">
                    <i class="fa fa-trash"></i> Delete
                </button>
                <a href="print_schedule.php?id=<?php echo $id ?>" class="btn btn-info btn-sm" target="_blank">
                    <i class="fa fa-print"></i> Print
                </a>
                <a href="index.php?page=schedule" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Faculty:</strong> <?php echo isset($faculty_name) ? ucwords($faculty_name) : 'N/A'; ?></p>
                    <p><strong>Subject:</strong> <?php echo isset($subject_name) ? $subject_name : 'N/A'; ?></p>
                    <?php if(isset($course_name) && !empty($course_name)): ?>
                    <p><strong>Department:</strong> <?php echo $course_name; ?></p>
                    <?php endif; ?>
                    <?php if(isset($strand_name) && !empty($strand_name)): ?>
                    <p><strong>Strand:</strong> <?php echo $strand_name; ?></p>
                    <?php endif; ?>
                    <?php if(isset($year_level_display) && !empty($year_level_display)): ?>
                    <p><strong>Level:</strong> <?php echo $year_level_display; ?></p>
                    <?php endif; ?>
                    <p><strong>Section:</strong> <?php echo isset($section_name) ? $section_name : 'N/A'; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Room/Laboratory:</strong> <?php echo isset($location) ? $location : 'N/A'; ?></p>
                    <p><strong>Days of Week:</strong> <?php echo $days_display; ?></p>
                    <?php if(isset($month_from) && !empty($month_from)): ?>
                    <p><strong>Month From:</strong> <?php echo date('F Y', strtotime($month_from)); ?></p>
                    <?php endif; ?>
                    <?php if(isset($month_to) && !empty($month_to)): ?>
                    <p><strong>Month To:</strong> <?php echo date('F Y', strtotime($month_to)); ?></p>
                    <?php endif; ?>
                    <p><strong>Time:</strong> <?php echo isset($time_from) ? date('h:i A', strtotime($time_from)) : 'N/A'; ?> - <?php echo isset($time_to) ? date('h:i A', strtotime($time_to)) : 'N/A'; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<style>
	p{
		margin-bottom: 0.5rem;
	}
	/* Footer handling moved to schedule.php */
	.card-header h5 {
	    margin-bottom: 0;
	}
	
	/* Clean up the card styling for standalone view */
    .card {
        margin-top: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        background-color: #007bff !important;
        color: white !important;
    }
    
    /* Responsive styling for view_schedule.php */
    @media (max-width: 768px) {
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
        
        /* Make card header more mobile-friendly */
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 10px;
        }
        
        .card-header h5 {
            margin-bottom: 10px;
        }
        
        /* Improve action buttons on mobile */
        .action-buttons {
            margin-top: 10px;
            width: 100%;
            justify-content: flex-start;
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
	
	/* Fixed action buttons styling */
	.action-buttons {
	    display: flex;
	    flex-wrap: nowrap;
	    gap: 10px;
	    justify-content: flex-end;
	    min-width: 260px !important;
	}
	
	.action-buttons .btn {
	    position: relative;
	    min-width: 70px;
	    text-align: center;
	    padding: 6px 12px;
	    border-radius: 4px;
	    display: inline-flex;
	    align-items: center;
	    justify-content: center;
	    white-space: nowrap;
	    overflow: visible;
	}
	
	.action-buttons .btn i.fa {
	    display: inline-block !important;
	    margin-right: 5px;
	    position: relative;
	    top: 0;
	    left: 0;
	    font-size: 14px;
	}
	
	/* Override any Bootstrap icon styles that might be causing problems */
	.fa-edit:before, .fa-pencil-square-o:before {
	    content: "\f044" !important;
	    visibility: visible !important;
	    display: inline !important;
	}
	
	.fa-trash:before {
	    content: "\f1f8" !important;
	    visibility: visible !important;
	    display: inline !important;
	}
	
	.fa-times:before {
	    content: "\f00d" !important;
	    visibility: visible !important;
	    display: inline !important;
	}
	
	/* Responsive styles */
	@media (max-width: 767px) {
	    .card-header {
	        flex-direction: column;
	        align-items: flex-start;
	    }
	    
	    .action-buttons {
	        margin-top: 10px;
	        width: 100%;
	        justify-content: flex-end;
	    }
	    
	    .action-buttons .btn {
	        margin-top: 5px;
	        padding: 0.25rem 0.5rem;
	        font-size: 0.875rem;
	    }
	}
	
	/* Fullscreen styles */
	@media (min-width: 992px) {
	    .container-fluid {
	        padding: 0 15px;
	    }
	    
	    .card {
	        margin-bottom: 0;
	    }
	    
	    .row {
	        margin-left: -15px;
	        margin-right: -15px;
	    }
	    
	    .col-md-6 {
	        padding-left: 15px;
	        padding-right: 15px;
	    }
	}
	
	/* Fix for modal sizes */
	.modal-dialog.large {
	    max-width: 70%;
	}
	
	.modal-dialog.mid-large {
	    max-width: 60%;
	}
	
	/* Override for schedule form modal in manage_schedule */
	#uni_modal.manage-schedule-modal .modal-dialog {
	    max-width: 50%;
	    margin: 1.75rem auto;
	}
	
	/* Fix navigation buttons */
	.btn-navigation {
	    min-width: 40px !important;
	    height: 38px !important;
	    display: flex !important;
	    align-items: center !important;
	    justify-content: center !important;
	    padding: 0.375rem 0.75rem !important;
	    margin: 0 2px;
	    border-radius: 0.25rem !important;
	    overflow: visible !important;
	}
	
	.btn-navigation i {
	    font-size: 14px !important;
	    display: inline-block !important;
	    margin: 0 !important;
	}
	
	/* Navigation buttons for small screens */
	@media (max-width: 480px) {
	    .btn-navigation {
	        min-width: 32px !important;
	        height: 32px !important;
	        padding: 4px !important;
	        font-size: 12px !important;
	    }
	    
	    .nav-buttons {
	        display: flex;
	        flex-wrap: wrap;
	        justify-content: center;
	        gap: 5px;
	        margin: 10px 0;
	    }
	}
</style>
<script>
    // Function to print faculty schedule
    function printSchedule() {
        // Store the current body content
        var originalContent = document.body.innerHTML;
        
        // Get only the schedule container
        var printContent = document.getElementById('schedule-container').innerHTML;
        
        // Create a new window with just the content to print
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Faculty Schedule</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
        printWindow.document.write('<style>body { padding: 20px; } @media print { .no-print { display: none; } }</style>');
        printWindow.document.write('</head><body>');
        
        // Add a header with school name
        printWindow.document.write('<div class="text-center mb-4">');
        printWindow.document.write('<h2>AutoSched</h2>');
        printWindow.document.write('<h4>Faculty Schedule</h4>');
        printWindow.document.write('</div>');
        
        // Add the schedule content
        printWindow.document.write(printContent);
        
        // Add print button that only shows on screen, not when printing
        printWindow.document.write('<div class="mt-4 text-center no-print">');
        printWindow.document.write('<button class="btn btn-primary" onclick="window.print()">Print</button>');
        printWindow.document.write('</div>');
        
        printWindow.document.write('</body></html>');
        printWindow.document.close();
    }
    
    // Ensure Font Awesome is loaded
    if (typeof FontAwesome === 'undefined') {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css';
        document.head.appendChild(link);
    }
    
	// Function to debug uni_modal calls
	function debugUniModal(title, url, size) {
		console.log('Debug uni_modal call:', {title: title, url: url, size: size});
		return true;
	}
	
	// Ensure _conf function is available
	if (typeof _conf === 'undefined') {
		window._conf = function($msg='',$func='',$params = []){
			console.log('_conf called with:', {msg: $msg, func: $func, params: $params});
			
			// Create the function call string with proper quotes around string parameters
			var paramStr = $params.map(function(param) {
				if (typeof param === 'string') {
					return "'" + param + "'";
				}
				return param;
			}).join(',');
			
			var funcCall = $func + "(" + paramStr + ")";
			console.log('Function call will be:', funcCall);
			
			$('#confirm_modal #confirm').attr('onclick', funcCall);
			$('#confirm_modal .modal-body').html($msg);
			$('#confirm_modal').modal('show');
		}
	}

	$('#delete_schedule').click(function(){
		_conf("Are you sure to delete this schedule?","delete_schedule",[$(this).attr('data-id')])
	})
	
	function delete_schedule($id){
		// Store section ID in localStorage to use for refreshing after deletion
		var sectionId = '<?php echo isset($section_id) ? $section_id : ""; ?>';
		
		console.log('View schedule delete called with ID:', $id);
		start_load();
		$.ajax({
			url:'ajax.php?action=delete_schedule',
			method:'POST',
			data:{id:$id},
			dataType: 'json', // Explicitly expect JSON response
			success:function(resp){
				console.log("JSON delete response:", resp);
				
				if(resp.status === 1) {
					alert_toast(resp.message || "Schedule successfully deleted",'success');
					
					// Notify other pages about the deletion
					if (sectionId) {
						notifyScheduleUpdate(sectionId);
					}
					
					setTimeout(function(){
						// If we came from student_info page, refresh the section
						if (document.referrer.includes('student_info.php') && sectionId) {
							location.href = 'index.php?page=student_info&refresh_section=' + sectionId;
						} else {
							location.href = 'index.php?page=schedule';
						}
					}, 1500);
				} else {
					alert_toast(resp.message || "Error deleting schedule", 'danger');
					console.error("Error deleting schedule:", resp);
					end_load();
				}
			},
			error: function(xhr, status, error) {
				console.log("AJAX error response:", xhr.responseText);
				try {
					// Try to parse error response as JSON
					var errorResp = JSON.parse(xhr.responseText);
					alert_toast(errorResp.message || "Error deleting schedule", 'danger');
				} catch(e) {
					// If not JSON, show generic error
					alert_toast("Error connecting to server: " + error, 'danger');
				}
				console.error("AJAX error details:", {
					status: status, 
					error: error,
					responseText: xhr.responseText
				});
				end_load();
			}
		});
	}

	// Function to notify student info page about schedule updates
	function notifyScheduleUpdate(sectionId) {
		// Create and dispatch a custom event
		var event = new CustomEvent('schedule-updated', {
			detail: {
				section_id: sectionId,
				timestamp: new Date().getTime()
			},
			bubbles: true,
			cancelable: true
		});
		
		// Dispatch the event
		document.dispatchEvent(event);
		
		console.log('Notified about schedule update for section:', sectionId);
	}

	// Ensure parent window has uni_modal function
	if (window.parent && !window.parent.uni_modal) {
		console.log('Adding uni_modal to parent window');
		window.parent.uni_modal = function(title, url, size = '') {
			console.log('Parent window uni_modal called:', title, url, size);
			// Implementation copied from schedule.php
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
						
						$('#uni_modal').modal('show');
					} catch (e) {
						console.error('Error showing modal:', e);
						alert("Error in modal content");
					}
				}
			});
		};
	}
</script>

<!-- Confirm Modal for Delete -->
<div class="modal fade" id="confirm_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
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

