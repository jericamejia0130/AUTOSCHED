<!-- filepath: c:\xampp\htdocs\schedulingmay1\admin\schedules.php -->
<?php
include_once 'db_connect.php';
include_once 'header.php';
include_once 'topbar.php';
include_once 'navbar.php'; // Use include_once to prevent duplication
?>

<style>
    /* Responsive table styles */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Ensure minimum width for better horizontal scrolling for smaller screens only */
    @media (max-width: 1200px) {
        .schedule-table {
            min-width: 1200px; /* Minimum width to ensure proper display on small screens */
        }
    }
    
    /* For full screen, allow the table to adapt to the container width */
    @media (min-width: 1201px) {
        .schedule-table {
            width: 100%;
        }
        
        /* Set maximum widths for each column to prevent stretching */
        .schedule-table th:nth-child(1),
        .schedule-table td:nth-child(1) {
            width: 3%; /* # */
        }
        .schedule-table th:nth-child(2),
        .schedule-table td:nth-child(2) {
            width: 10%; /* Faculty */
        }
        .schedule-table th:nth-child(3),
        .schedule-table td:nth-child(3) {
            width: 10%; /* Subject */
        }
        .schedule-table th:nth-child(4),
        .schedule-table td:nth-child(4),
        .schedule-table th:nth-child(5),
        .schedule-table td:nth-child(5) {
            width: 7%; /* Department/Strand */
        }
        .schedule-table th:nth-child(6),
        .schedule-table td:nth-child(6) {
            width: 5%; /* Section */
        }
        .schedule-table th:nth-child(7),
        .schedule-table td:nth-child(7) {
            width: 5%; /* Year Level */
        }
        .schedule-table th:nth-child(8),
        .schedule-table td:nth-child(8) {
            width: 7%; /* Room */
        }
        .schedule-table th:nth-child(9),
        .schedule-table td:nth-child(9) {
            width: 9%; /* Days of Week */
        }
        .schedule-table th:nth-child(10),
        .schedule-table td:nth-child(10),
        .schedule-table th:nth-child(11),
        .schedule-table td:nth-child(11) {
            width: 5%; /* Month From/To */
        }
        .schedule-table th:nth-child(12),
        .schedule-table td:nth-child(12) {
            width: 7%; /* Time */
        }
        .schedule-table th:nth-child(13),
        .schedule-table td:nth-child(13) {
            width: 120px; /* Actions - fixed width */
            min-width: 120px; /* Ensure minimum width */
        }
    }
    
    /* Set column widths to prevent squishing with text wrapping for longer content */
    .schedule-table th {
        white-space: nowrap;
        padding: 8px;
    }
    
    .schedule-table td {
        padding: 8px;
        /* Allow line breaks for long content in full screen */
        white-space: normal;
        word-break: break-word;
    }
    
    /* For small screens, prevent wrapping */
    @media (max-width: 1200px) {
        .schedule-table td {
            white-space: nowrap;
        }
    }
    
    /* Text ellipsis for very long content */
    .schedule-table td.truncate {
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    /* Action buttons container */
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
    .schedule-table th:last-child,
    .schedule-table td:last-child {
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
    
    /* Adjust padding on smaller screens */
    @media (max-width: 768px) {
        .card-body {
            padding: 0.75rem;
        }
    }
    
    /* Make more responsive especially for mobile */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 10px;
        }
        
        .card {
            margin-bottom: 15px;
        }
        
        .card-body {
            padding: 10px 5px;
        }
        
        /* Fix for small screens and mobile devices */
        .schedule-table {
            font-size: 14px;
        }
        
        /* Make action buttons stack on very small screens */
        @media (max-width: 480px) {
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .action-buttons .btn {
                width: 100%;
            }
        }
        
        /* Improve schedule table on mobile */
        .schedule-table th,
        .schedule-table td {
            padding: 6px 4px;
        }
    }
    
    /* Custom responsive table solution */
    @media (max-width: 640px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Ensure table header stays visible */
        .schedule-table thead {
            position: sticky;
            top: 0;
            background-color: #fff;
            z-index: 1;
        }
        
        /* Add shadow to indicate scrolling */
        .table-responsive::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 5px;
            background: linear-gradient(to right, rgba(0,0,0,0), rgba(0,0,0,0.1));
            pointer-events: none;
        }
    }

    /* Fix pagination buttons */
    .pagination {
        display: flex !important;
        justify-content: center !important;
        gap: 5px;
        margin-top: 20px;
    }

    .pagination .page-item {
        display: flex !important;
    }

    .pagination .page-link {
        min-width: 40px !important;
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0.375rem 0.75rem !important;
        border-radius: 0.25rem !important;
    }

    .pagination .page-link:focus {
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
    }

    /* Fix for smaller screens */
    @media (max-width: 480px) {
        .pagination {
            flex-wrap: wrap;
        }
        
        .pagination .page-link {
            min-width: 32px !important;
            height: 32px !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
        }
    }
</style>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row mb-4 mt-4">
            <div class="col-md-12">
                <h4 class="text-center">All Created Schedules</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>Schedules List</b>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover schedule-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Faculty</th>
                                        <th>Subject</th>
                                        <th>Department</th>
                                        <th>Strand</th>
                                        <th>Section</th>
                                        <th>Year Level</th>
                                        <th>Room</th>
                                        <th>Days of Week</th>
                                        <th>Month From</th>
                                        <th>Month To</th>
                                        <th>Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
$schedules = $conn->query("SELECT s.*, 
                                    CONCAT(f.lastname, ', ', f.firstname, ' ', f.middlename) AS faculty_name, 
                                    sub.subject AS subject_name, 
                                    c.course AS department_name, 
                                    st.name AS strand_name, 
                                    sec.name AS section_name,
                                    sec.year_level, 
                                    r.name AS room_name, 
                                    r.type AS room_type, 
                                    s.dow, 
                                    s.month_from, 
                                    s.month_to 
                             FROM schedules s 
                             LEFT JOIN faculty f ON s.faculty_id = f.id 
                             LEFT JOIN subjects sub ON s.subject_id = sub.id 
                             LEFT JOIN courses c ON s.course_id = c.id 
                             LEFT JOIN strands st ON s.strand_id = st.id 
                             LEFT JOIN sections sec ON s.section_id = sec.id 
                             LEFT JOIN rooms r ON s.room_id = r.id 
                             ORDER BY s.id ASC");
                                $i = 1;
                                while ($row = $schedules->fetch_assoc()):
                                    $days_of_week = 'N/A';
                                    if (!empty($row['dow'])) {
                                        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                        $day_indices = explode(',', $row['dow']);
                                        $day_names = [];
                                        
                                        foreach($day_indices as $day_index) {
                                            if(isset($days[$day_index])) {
                                                $day_names[] = $days[$day_index];
                                            }
                                        }
                                        
                                        $days_of_week = !empty($day_names) ? implode(', ', $day_names) : 'N/A';
                                    }
                                    
                                    $year_level_display = 'N/A';
                                    if (!empty($row['year_level'])) {
                                        if (!empty($row['strand_name'])) {
                                            $year_level_display = "Grade " . $row['year_level'];
                                        } else if (!empty($row['department_name'])) {
                                            $suffix = '';
                                            if ($row['year_level'] == 1) $suffix = 'st';
                                            else if ($row['year_level'] == 2) $suffix = 'nd';
                                            else if ($row['year_level'] == 3) $suffix = 'rd';
                                            else $suffix = 'th';
                                            
                                            $year_level_display = $row['year_level'] . $suffix . " Year";
                                        }
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo isset($row['faculty_name']) ? ucwords($row['faculty_name']) : 'N/A'; ?></td>
                                    <td><?php echo isset($row['subject_name']) ? $row['subject_name'] : 'N/A'; ?></td>
                                    <td><?php echo isset($row['department_name']) ? $row['department_name'] : 'N/A'; ?></td>
                                    <td><?php echo isset($row['strand_name']) ? $row['strand_name'] : 'N/A'; ?></td>
                                    <td><?php echo isset($row['section_name']) ? $row['section_name'] : 'N/A'; ?></td>
                                    <td><?php echo $year_level_display; ?></td>
                                    <td><?php echo isset($row['room_name']) ? $row['room_name'] . ' (' . $row['room_type'] . ')' : 'N/A'; ?></td>
                                    <td><?php echo $days_of_week; ?></td>
                                    <td><?php echo isset($row['month_from']) ? $row['month_from'] : 'N/A'; ?></td>
                                    <td><?php echo isset($row['month_to']) ? $row['month_to'] : 'N/A'; ?></td>
                                    <td><?php echo isset($row['time_from']) && isset($row['time_to']) ? date('h:i A', strtotime($row['time_from'])) . ' - ' . date('h:i A', strtotime($row['time_to'])) : 'N/A'; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-primary edit_schedule" data-id="<?php echo $row['id']; ?>"><i class="fa fa-edit"></i> Edit</button>
                                            <button class="btn btn-sm btn-danger delete_schedule" data-id="<?php echo $row['id']; ?>"><i class="fa fa-trash"></i> Delete</button>
                                        </div>
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
    </div>
</div>

<script>
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

    // Function to adjust the table width
    function adjustTableWidth() {
        var containerWidth = $('.table-responsive').width();
        var windowWidth = $(window).width();
        
        // Only set fixed width for medium screens
        if (windowWidth >= 768 && windowWidth < 1200) {
            if (containerWidth < 1200) {
                $('.schedule-table').css('width', '1200px');
            }
        } else if (windowWidth < 768) {
            // For mobile screens, let it scroll naturally
            $('.schedule-table').css('width', 'max-content');
        } else {
            // For larger screens, let the table be responsive
            $('.schedule-table').css('width', '100%');
        }
        
        // Apply appropriate text handling for each cell
        $('.schedule-table tbody td').each(function() {
            var cellText = $(this).text().trim();
            if (cellText.length > 30 && !$(this).hasClass('truncate')) {
                $(this).addClass('truncate');
                $(this).attr('title', cellText); // Add tooltip for truncated text
            }
        });
    }

    // Detect if we're coming back from manage_schedule after canceling
    $(document).ready(function() {
        // Check if there's a hash indicating we're coming back from manage_schedule
        var referrer = document.referrer;
        if (referrer && referrer.indexOf('manage_schedule.php') !== -1) {
            console.log('Returning from manage_schedule.php');
            // Force a proper layout refresh
            setTimeout(function() {
                adjustTableWidth();
                $(window).trigger('resize');
            }, 100);
        }
        
        adjustTableWidth();
        
        // Ensure Font Awesome is loaded
        if (typeof FontAwesome === 'undefined') {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css';
            document.head.appendChild(link);
        }
        
        // Fix action buttons width and ensure icons are visible
        $('.action-buttons').css('min-width', '110px');
        $('.action-buttons .btn').css('overflow', 'visible');
    });

    // Adjust table on window resize
    $(window).resize(function() {
        adjustTableWidth();
    });

    $('.edit_schedule').click(function() {
        var scheduleId = $(this).attr('data-id');
        console.log('Edit schedule clicked, ID:', scheduleId);
        
        // Direct page redirect with parameter to indicate we came from update_schedule.php
        location.href = 'index.php?page=manage_schedule&id=' + scheduleId + '&from_update=true';
    });

    $('.delete_schedule').click(function() {
        _conf("Are you sure to delete this schedule?", "delete_schedule", [$(this).attr('data-id')]);
    });

    function delete_schedule(id) {
        console.log('delete_schedule called with ID:', id);
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_schedule',
            method: 'POST',
            data: { id: id },
            dataType: 'json', // Explicitly expect JSON response
            success: function(resp) {
                console.log("JSON delete response:", resp);
                
                if(resp.status === 1) {
                    alert_toast(resp.message || "Schedule successfully deleted", 'success');
                    setTimeout(function() {
                        location.reload();
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

<?php include 'footer.php'; ?> <!-- Ensure this line is at the bottom of the file -->