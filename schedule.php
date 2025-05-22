<?php include('db_connect.php');?>
<?php
// Make sure we're connected to the database
if(!$conn) {
    die("Database connection failed");
}

// Get schedules with appropriate error handling
try {
    $schedules = $conn->query("SELECT s.*, sub.subject AS subject_name 
                            FROM schedules s 
                            LEFT JOIN subjects sub ON s.subject_id = sub.id 
                            ORDER BY s.id ASC");
    
    if (!$schedules) {
        throw new Exception("Query Failed: " . $conn->error);
    }
} catch (Exception $e) {
    die("Error loading schedules: " . $e->getMessage());
}
?>


<div class="container-fluid">
	
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Schedule</b>
						<div class="float:right">
                            <a href="print_schedule.php?all=1" class="btn btn-info btn-sm" target="_blank">
                                <i class="fa fa-print"></i> Print All Schedules
                            </a>
                            <button class="btn btn-primary btn-sm" id="new_schedule">
					            <i class="fa fa-plus"></i> New Entry
				            </button>
                        </div>
					</div>
					<div class="card-body">
						<div class="row">
							<label for="" class="control-label col-md-2 offset-md-2">View Schedule of:</label>
							<div class="col-md-4">
							<select name="faculty_id" id="faculty_id" class="custom-select select2">
								<option value=""></option>
							<?php 
								$faculty = $conn->query("SELECT *,concat(lastname,', ',firstname,' ',middlename) as name FROM faculty order by concat(lastname,', ',firstname,' ',middlename) asc");
								while($row= $faculty->fetch_array()):
							?>
								<option value="<?php echo $row['id'] ?>"><?php echo ucwords($row['name']) ?></option>
							<?php endwhile; ?>
							
							</select>
							</div>
							<div class="col-md-2">
							    <button type="button" id="print_faculty_schedule" class="btn btn-info btn-sm">
							        <i class="fa fa-print"></i> Print Selected Faculty
							    </button>
							</div>
						</div>
						<hr>
						<div id="calendar"></div>
					</div>
				</div>
			</div>
			<!-- Table Panel -->
		</div>
	</div>	

</div>

<!-- Modal Container -->
<div class="modal fade" id="uni_modal" role='dialog'>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
	
	td{
		vertical-align: middle !important;
	}
	td p{
		margin: unset
	}
	img{
		max-width:100px;
		max-height: 150px;
	}
	.avatar {
	    display: flex;
	    border-radius: 100%;
	    width: 100px;
	    height: 100px;
	    align-items: center;
	    justify-content: center;
	    border: 3px solid;
	    padding: 5px;
	}
	.avatar img {
	    max-width: calc(100%);
	    max-height: calc(100%);
	    border-radius: 100%;
	}
		input[type=checkbox]
{
  /* Double-sized Checkboxes */
  -ms-transform: scale(1.5); /* IE */
  -moz-transform: scale(1.5); /* FF */
  -webkit-transform: scale(1.5); /* Safari and Chrome */
  -o-transform: scale(1.5); /* Opera */
  transform: scale(1.5);
  padding: 10px;
}
a.fc-daygrid-event.fc-daygrid-dot-event.fc-event.fc-event-start.fc-event-end.fc-event-past {
    cursor: pointer;
}
a.fc-timegrid-event.fc-v-event.fc-event.fc-event-start.fc-event-end.fc-event-past {
    cursor: pointer;
}

/* Fix for action buttons to ensure text is fully visible */
.action-buttons {
    display: flex;
    flex-wrap: nowrap;
    justify-content: center;
    gap: 5px;
    min-width: 160px !important; /* Ensure enough width for the buttons */
}

.action-buttons .btn {
    width: auto;
    min-width: 70px; /* Minimum width for each button */
    padding: 4px 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    overflow: visible;
}

.action-buttons .btn i {
    margin-right: 5px;
    font-size: 12px;
    display: inline-block !important;
}

/* Ensure the action column is wide enough */
table th:last-child,
table td:last-child {
    min-width: 160px !important;
    width: auto !important;
}

/* Fix for font awesome icons */
.fa {
    display: inline-block !important;
    font: normal normal normal 14px/1 FontAwesome !important;
    font-size: inherit !important;
    text-rendering: auto !important;
    -webkit-font-smoothing: antialiased !important;
    -moz-osx-font-smoothing: grayscale !important;
}

/* Ensure font awesome is loaded */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

/* Responsive Calendar Styles */
@media (max-width: 768px) {
    .container-fluid {
        padding: 10px;
    }
    
    .card {
        margin-bottom: 15px;
    }
    
    .card-body {
        padding: 15px 10px;
    }
    
    /* Responsive calendar container */
    #calendar {
        font-size: 14px;
    }
    
    /* Adjust the faculty dropdown for mobile */
    .row label.control-label {
        text-align: left;
        margin-bottom: 5px;
    }
    
    .row .col-md-4 {
        width: 100%;
    }
    
    /* Make the buttons stack and smaller on mobile */
    .float-right {
        float: none !important;
        margin-top: 10px;
        display: block;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Calendar event adjustments for mobile */
    .fc-daygrid-event {
        padding: 2px 4px !important;
        font-size: 0.85em !important;
    }
    
    .fc-toolbar-title {
        font-size: 1.2em !important;
    }
    
    .fc-toolbar {
        flex-direction: column;
    }
    
    .fc-toolbar-chunk {
        margin-bottom: 10px;
    }
}

/* Specific fixes for extra small screens */
@media (max-width: 480px) {
    .card-header {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 10px;
    }
    
    .card-header span {
        margin-top: 10px;
        width: 100%;
    }
    
    .card-header .btn {
        width: 100%;
    }
    
    /* Make calendar buttons more accessible on mobile */
    .fc-toolbar button {
        padding: 0.25rem 0.5rem !important;
    }
    
    /* View dropdown for mobile */
    .select2-container {
        width: 100% !important;
    }
    
    /* Adjust layout for faculty selector */
    .col-md-2.offset-md-2 {
        margin-left: 0;
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* Fix for calendar navigation buttons - prevent duplication */
.fc-button-group {
    display: flex !important;
    gap: 2px;
}

/* Reset navigation buttons */
.fc-prev-button,
.fc-next-button {
    min-width: 30px !important;
    min-height: 30px !important;
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    overflow: hidden !important;
    font-size: 1em !important;
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
}

/* Hide the default icons completely */
.fc-icon {
    display: none !important;
}

/* Custom arrows using ::before pseudo-element */
.fc-prev-button::before {
    content: "<" !important;
    display: inline-block !important;
    font-weight: bold !important;
    color: white !important;
}

.fc-next-button::before {
    content: ">" !important;
    display: inline-block !important;
    font-weight: bold !important;
    color: white !important;
}

/* Hide any other content that might appear */
.fc-prev-button *,
.fc-next-button * {
    display: none !important;
}

/* Modal fixes for teacher schedule views */
.modal-dialog.mid-large {
    max-width: 700px;
}

/* Clean up the card styling for standalone view */
.card {
    margin-top: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0,0,0,0.125);
    border-radius: 0.25rem;
}

.card-header {
    background-color: #007bff !important;
    color: white !important;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.125);
    border-top-left-radius: calc(0.25rem - 1px);
    border-top-right-radius: calc(0.25rem - 1px);
}

.card-header h5, .card-header b {
    color: white !important;
    margin-bottom: 0;
}

/* Responsive styling for view_schedule.php */
@media (max-width: 768px) {
    /* Make modals more mobile-friendly */
    .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100% - 1rem) !important;
    }
    
    .modal-content {
        border-radius: 0.25rem;
    }
    
    .modal-header {
        padding: 0.75rem 1rem;
    }
    
    .modal-body {
        padding: 1rem;
        max-height: calc(100vh - 170px);
        overflow-y: auto;
    }
    
    .modal-footer {
        padding: 0.75rem 1rem;
        display: flex !important;
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
</style>
<script>
	
	$('#new_schedule').click(function(){
		uni_modal('New Schedule','manage_schedule.php','mid-large')
	})
	$('.view_alumni').click(function(){
		uni_modal("Bio","view_alumni.php?id="+$(this).attr('data-id'),'mid-large')
		
	})
	$('.delete_alumni').click(function(){
		_conf("Are you sure to delete this alumni?","delete_alumni",[$(this).attr('data-id')])
	})
	
	function delete_alumni($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_alumni',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
	document.addEventListener('DOMContentLoaded', function() {
	    var calendarEl = document.getElementById('calendar');
	    var windowWidth = window.innerWidth;
	    
	    // Use different initial view based on screen size
	    var initialView = windowWidth < 768 ? 'timeGridDay' : 'dayGridMonth';
	    
	    var calendar = new FullCalendar.Calendar(calendarEl, {
	        headerToolbar: {
	            left: 'prev,next today',
	            center: 'title',
	            right: 'dayGridMonth,timeGridWeek,timeGridDay'
	        },
	        initialView: initialView,
	        height: 'auto',
	        buttonIcons: {
	            prev: 'chevron-left',
	            next: 'chevron-right'
	        },
	        navLinks: false, // Disable clickable day/week names to avoid extra controls
	        themeSystem: 'bootstrap',
	        // Responsive settings with improved handling
	        windowResize: function(view) {
	            var newWidth = window.innerWidth;
	            if (newWidth < 768) {
	                calendar.changeView('timeGridDay');
	            } else {
	                calendar.changeView('dayGridMonth');
	            }
	            // Force refresh to clean up rendering
	            setTimeout(function() {
	                calendar.updateSize();
	            }, 100);
	        },
	        events: function(fetchInfo, successCallback, failureCallback) {
	            var faculty_id = $('#faculty_id').val();
	            if (!faculty_id) {
	                successCallback([]);
	                return;
	            }

	            $.ajax({
	                url: 'ajax.php?action=get_teacher_schedule',
	                method: 'POST',
	                data: { faculty_id: faculty_id },
	                dataType: 'json',
	                success: function(response) {
	                    console.log('Schedule response:', response); // Debug
	                    
	                    // Clear any previous error messages
	                    $('#calendar-error').remove();
	                    
	                    if (response && Array.isArray(response)) {
	                        // Process each event to ensure it has the right properties
	                        var processedEvents = response.map(function(event) {
	                            // Make sure there's a URL or faculty context
	                            if (!event.url && faculty_id) {
	                                event.url = '#'; // Prevent default URL opening
	                            }
	                            return event;
	                        });
	                        successCallback(processedEvents);
	                    } else if (response && response.error) {
	                        console.error('Server error:', response.error);
	                        $('#calendar').before('<div id="calendar-error" class="alert alert-danger">' + response.error + '</div>');
	                        successCallback([]);
	                    } else {
	                        console.error('Invalid response format:', response);
	                        try {
	                            // Try to parse as JSON if it's a string
	                            if (typeof response === 'string') {
	                                var parsed = JSON.parse(response);
	                                if (Array.isArray(parsed)) {
	                                    successCallback(parsed);
	                                    return;
	                                }
	                            }
	                        } catch (e) {
	                            console.error('JSON parse error:', e);
	                        }
	                        successCallback([]);
	                    }
	                },
	                error: function(xhr, status, error) {
	                    console.error('AJAX error:', error);
	                    console.error('Status:', status);
	                    console.error('Response:', xhr.responseText);
	                    alert('Failed to load schedule. Please try again later.');
	                    failureCallback();
	                }
	            });
	        },
	        eventClick: function(info) {
	            var event = info.event;
	            var props = event.extendedProps;
	            // Pass faculty_id to maintain context when viewing schedule
	            var faculty_id = $('#faculty_id').val();
	            uni_modal('View Schedule Details','view_schedule.php?id=' + event.id + '&faculty_id=' + faculty_id + '&from_faculty=true', 'mid-large');
	        },
	        eventDidMount: function(info) {
	            // Add tooltip with description
	            $(info.el).tooltip({
	                title: info.event.extendedProps.description || info.event.title,
	                placement: 'top',
	                trigger: 'hover',
	                container: 'body'
	            });
	        },
	        // Responsive settings for different screen sizes
	        views: {
	            dayGridMonth: {
	                dayMaxEventRows: window.innerWidth < 768 ? 2 : 6 // Show fewer events per day on mobile
	            },
	            timeGrid: {
	                dayMinWidth: 50 // Minimum width for columns in timeGrid view
	            }
	        }
	    });
	    calendar.render();

	    $('#faculty_id').change(function() {
	        calendar.refetchEvents();
	    });
	    
	    // Handle window resize to adjust calendar
	    $(window).resize(function() {
	        calendar.updateSize();
	    });
	});
	$('#uni_modal').on('hidden.bs.modal', function() {
	    $(this).find(':focus').blur(); // Remove focus from the modal
	});

	// Add this to ensure the modal is properly initialized
	function uni_modal(title, url, size = '') {
	    console.log('uni_modal called with:', { title, url, size });
	    
	    // Ensure the URL is properly formatted with parameters
	    if (url.includes('?') && !url.includes('&return_to_view=') && url.includes('manage_schedule.php')) {
	        url += '&return_to_view=true';
	        console.log('Added return_to_view parameter to URL:', url);
	    }
	    
	    // If this is a view_schedule request from a faculty calendar, ensure it has the faculty context
	    if (url.includes('view_schedule.php') && !url.includes('from_faculty=') && $('#faculty_id').val()) {
	        var faculty_id = $('#faculty_id').val();
	        url += (url.includes('?') ? '&' : '?') + 'faculty_id=' + faculty_id + '&from_faculty=true';
	        console.log('Added faculty context to URL:', url);
	    }
	    
	    $.ajax({
	        url: url,
	        error: function(xhr, status, error) {
	            console.error('Modal error:', error);
	            console.error('Status:', status);
	            alert("An error occurred while loading content");
	        },
	        success: function(html) {
	            try {
	                if (!html || typeof html !== 'string') {
	                    console.error('Invalid HTML content:', html);
	                    alert("Invalid content received");
	                    return;
	                }
	                
	                // Remove any accidental CSS text from HTML before inserting
	                html = html.replace(/\/\* Remove these modal-specific styles \*\/[\s\S]*?}/g, '');
	                
	                $('#uni_modal .modal-title').html(title);
	                $('#uni_modal .modal-body').html(html);
	                
	                // Apply size if specified
	                if (size != '') {
	                    $('#uni_modal .modal-dialog').removeClass('modal-md').addClass(size);
	                } else {
	                    $('#uni_modal .modal-dialog').removeClass('large mid-large').addClass('modal-md');
	                }
	                
	                // Handle different modal types
	                if (url.includes('manage_schedule.php')) {
	                    // Hide close button when editing schedule
	                    $('#uni_modal .close').hide();
	                } else {
	                    // For all other views, show the close button
	                    $('#uni_modal .close').show();
	                }
	                
	                // Always show the footer - fixes the display issue
	                $('#uni_modal .modal-footer').css('display', 'flex');
	                
	                // Clean up any remaining CSS text that might display
	                setTimeout(function() {
	                    var modalBody = $('#uni_modal .modal-body');
	                    var bodyContent = modalBody.html();
	                    
	                    // Check for CSS comment patterns
	                    if(bodyContent && bodyContent.includes('/*')) {
	                        // Find and remove the CSS text block
	                        bodyContent = bodyContent.replace(/\/\*[\s\S]*?\*\//g, '');
	                        // Remove any standalone CSS rules that might have been missed
	                        bodyContent = bodyContent.replace(/(#uni_modal|\.card|\.row|\.col-md-6|@media)[\s\S]*?{[\s\S]*?}/g, '');
	                        modalBody.html(bodyContent);
	                        console.log('Cleaned up CSS text in modal body');
	                    }
	                }, 100);
	                
	                $('#uni_modal').modal('show');
	                console.log('Modal displayed successfully');
	            } catch (e) {
	                console.error('Error showing modal:', e);
	                alert("Error in modal content");
	            }
	        }
	    });
	}

	// Auto-open view_schedule modal if view_id parameter is present
	$(document).ready(function() {
	    // Check for view_id parameter in URL
	    var urlParams = new URLSearchParams(window.location.search);
	    var viewId = urlParams.get('view_id');
	    
	    // Fix CSS for modal footer
	    $('<style type="text/css">')
	        .text(`
	            /* Remove problematic modal footer styles */
	            #uni_modal .modal-footer { 
	                display: flex !important; 
	            }
	            
	            /* Card header text color */
	            .card-header {
	                color: white !important;
	            }
	        `)
	        .appendTo('head');
	    
	    // Listen for modal show event to fix footer display
	    $(document).on('shown.bs.modal', '#uni_modal', function() {
	        $('#uni_modal .modal-footer').css('display', 'flex');
	        
	        // Fix for CSS text displaying in the modal body
	        var modalBody = $('#uni_modal .modal-body');
	        var textContent = modalBody.text();
	        if(textContent.includes('/* Remove these modal-specific styles */')) {
	            // CSS text is showing up in the modal, remove it
	            var cleanedContent = modalBody.html().replace(/\/\* Remove these modal-specific styles \*\/[\s\S]*?}/g, '');
	            modalBody.html(cleanedContent);
	            console.log('Removed accidentally displayed CSS from modal');
	        }
	    });
	    
	        if (viewId) {
        console.log('Auto-opening view_schedule with ID:', viewId);
        // Open the view_schedule modal
        setTimeout(function() {
            uni_modal('View Schedule Details', 'view_schedule.php?id=' + viewId, 'mid-large');
            
            // Remove the parameter from URL without refreshing
            var newUrl = window.location.href.split('?')[0] + '?page=schedule';
            window.history.replaceState({}, document.title, newUrl);
        }, 500);
    }
    
    // Print faculty schedule button
    $('#print_faculty_schedule').click(function(){
        var faculty_id = $('#faculty_id').val();
        if(faculty_id === '') {
            alert('Please select a faculty first.');
            return;
        }
        window.open('print_schedule.php?faculty_id=' + faculty_id, '_blank');
    });
	});
</script>