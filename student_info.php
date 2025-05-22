<?php include('db_connect.php');?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <b>Student Information</b>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="college-tab" data-toggle="tab" href="#college">College</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="shs-tab" data-toggle="tab" href="#shs">Senior High</a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    <!-- College Tab -->
                    <div class="tab-pane fade show active" id="college">
                        <div class="accordion" id="collegeAccordion">
                            <!-- Dynamic content will be loaded here -->
                        </div>
                    </div>

                    <!-- Senior High Tab -->
                    <div class="tab-pane fade" id="shs">
                        <div class="accordion" id="shsAccordion">
                            <!-- Dynamic content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    color: #495057;
}
.nav-tabs .nav-link.active {
    color: #007bff;
}
.card-header {
    background-color: #f8f9fa;
}
.year-block, .grade-block {
    border: 1px solid #dee2e6;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.section-block {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 10px;
}
.table-sm td, .table-sm th {
    padding: 0.3rem;
    font-size: 0.9rem;
}
.accordion .card-header {
    padding: 0;
}
.accordion .btn-link {
    width: 100%;
    text-align: left;
    text-decoration: none;
    color: #007bff;
}
.accordion .btn-link:hover {
    text-decoration: none;
}
.badge {
    margin-left: 10px;
}
.schedule-container {
    margin-top: 10px;
}
.btn-link .badge {
    vertical-align: middle;
}

/* Highlight effect for the nav-student_info menu item */
.nav-item.nav-student_info.highlighted {
    background-color: rgba(0, 123, 255, 0.2) !important;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    transition: all 0.5s ease;
}

/* Make the active nav item more visible */
.nav-item.nav-student_info.active {
    background-color: rgba(0, 123, 255, 0.1) !important;
    font-weight: bold !important;
    border-left: 4px solid #007bff !important;
}
</style>

<script>
$(document).ready(function(){
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Ensure sidebar is properly closed on mobile view for this page
    if ($(window).width() < 768) {
        $('#sidebar').removeClass('active');
        $('.overlay').removeClass('active');
        $('body').removeClass('sidebar-active');
    }
    
    // Restore saved scroll position if available
    var savedScrollPos = localStorage.getItem('student_info_scroll_pos');
    if (window.location.hash === '#preserve-scroll' && savedScrollPos) {
        setTimeout(function() {
            window.scrollTo(0, parseInt(savedScrollPos));
            // Make sure the nav item is visible by adding a highlight effect
            $('.nav-item.nav-student_info').addClass('highlighted');
            setTimeout(function() {
                $('.nav-item.nav-student_info').removeClass('highlighted');
            }, 2000);
        }, 100);
    }

    // Save scroll position periodically
    var scrollSaveInterval = setInterval(function() {
        localStorage.setItem('student_info_scroll_pos', window.scrollY);
    }, 1000);

    // Handle tab changes
    $('.nav-tabs a').click(function(e){
        e.preventDefault();
        $(this).tab('show');
    });

    // Store active tab in localStorage
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });

    // Check for stored active tab and show it
    var activeTab = localStorage.getItem('activeTab');
    if(activeTab){
        $('#myTab a[href="' + activeTab + '"]').tab('show');
    }
    
    loadDepartments();
    loadStrands();
    
    // Handle department expansion
    $(document).on('show.bs.collapse', '[id^="dept"]', function() {
        const deptId = $(this).attr('id').replace('dept', '');
        loadDepartmentSections(deptId);
    });
    
    // Handle strand expansion
    $(document).on('show.bs.collapse', '[id^="strand"]', function() {
        const strandId = $(this).attr('id').replace('strand', '');
        loadStrandSections(strandId);
    });
    
    // Handle view schedule button clicks
    $(document).on('click', '.view-schedule', function() {
        const scheduleId = $(this).data('id');
        uni_modal('Schedule Details', 'view_schedule.php?id=' + scheduleId, 'mid-large');
    });
    
    // Handle edit schedule button clicks
    $(document).on('click', '.edit-schedule', function() {
        const scheduleId = $(this).data('id');
        uni_modal('Edit Schedule', 'manage_schedule.php?id=' + scheduleId, 'large');
    });
    
    // Listen for schedule update events to refresh the affected section
    window.addEventListener('schedule-updated', function(e) {
        if (e.detail && e.detail.section_id) {
            refreshSectionSchedule(e.detail.section_id);
        }
    });
    
    // Check if we should refresh a specific section's schedule (after redirect)
    const urlParams = new URLSearchParams(window.location.search);
    const refreshSection = urlParams.get('refresh_section');
    if (refreshSection) {
        refreshSectionSchedule(refreshSection);
        // Clean the URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Add a function to ensure the navbar item is visible
    function ensureNavItemVisible() {
        var navItem = $('.nav-item.nav-student_info');
        if (navItem.length) {
            // Get the position of the nav item relative to the viewport
            var itemRect = navItem[0].getBoundingClientRect();
            var isVisible = (
                itemRect.top >= 0 &&
                itemRect.bottom <= window.innerHeight
            );
            
            // If not visible, scroll the sidebar to make it visible
            if (!isVisible) {
                var sidebarContainer = $('#sidebar');
                // Scroll the sidebar to center the nav item
                var newScrollTop = sidebarContainer.scrollTop() + itemRect.top - (window.innerHeight / 2);
                sidebarContainer.scrollTop(newScrollTop);
                
                // Add a highlight to draw attention
                navItem.addClass('highlighted');
                setTimeout(function() {
                    navItem.removeClass('highlighted');
                }, 2000);
            }
        }
    }

    // Call the function when the window is resized or scrolled
    $(window).on('resize scroll', debounce(ensureNavItemVisible, 200));

    // Debounce function to prevent excessive calls
    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    // Use MutationObserver to detect DOM changes and ensure nav item visibility
    var observer = new MutationObserver(debounce(ensureNavItemVisible, 200));
    observer.observe(document.body, { childList: true, subtree: true });

    // Call once on load
    $(window).on('load', ensureNavItemVisible);
});

// Load departments dynamically
function loadDepartments() {
    $.ajax({
        url: 'ajax.php?action=get_student_departments',
        method: 'GET',
        dataType: 'json',
        success: function(departments) {
            try {
                if (!Array.isArray(departments)) {
                    console.error('Invalid department data:', departments);
                    $('#collegeAccordion').html('<div class="alert alert-danger text-center p-3">Error: Invalid department data</div>');
                    return;
                }
                
                if (departments.length === 0) {
                    $('#collegeAccordion').html('<div class="alert alert-info text-center p-3">No departments found</div>');
                    return;
                }
                
                let html = '';
                
                departments.forEach(dept => {
                    let sectionCount = dept.section_count || 0;
                    html += `
                        <div class="card mb-3">
                            <div class="card-header">
                                <h2 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" 
                                            data-target="#dept${dept.id}">
                                        ${dept.course} - ${dept.description}
                                        <span class="badge badge-primary">${sectionCount} sections</span>
                                    </button>
                                </h2>
                            </div>
                            <div id="dept${dept.id}" class="collapse" data-parent="#collegeAccordion">
                                <div class="card-body" id="dept-sections-${dept.id}">
                                    <div class="text-center">Loading sections...</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#collegeAccordion').html(html);
            } catch (e) {
                console.error('Error processing departments:', e);
                $('#collegeAccordion').html('<div class="alert alert-danger text-center p-3">Error processing department data</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading departments:', status, error);
            let errorMessage = 'Error loading departments';
            
            if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.error) {
                        errorMessage = `Error: ${response.error}`;
                    }
                } catch (e) {
                    console.error('Could not parse error response:', e);
                }
            }
            
            // Fall back to direct AJAX call if the action doesn't work
            $.ajax({
                url: 'courses.php',
                method: 'GET',
                success: function(html) {
                    // Extract course data from HTML
                    try {
                        let departments = [];
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const rows = doc.querySelectorAll('table tbody tr');
                        
                        rows.forEach((row, index) => {
                            const cells = row.querySelectorAll('td');
                            if (cells.length >= 3) {
                                departments.push({
                                    id: index + 1,
                                    course: cells[1].textContent.trim(),
                                    description: cells[2].textContent.trim(),
                                    section_count: 0
                                });
                            }
                        });
                        
                        if (departments.length > 0) {
                            let html = '';
                            departments.forEach(dept => {
                                html += `
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h2 class="mb-0">
                                                <button class="btn btn-link" type="button" data-toggle="collapse" 
                                                        data-target="#dept${dept.id}">
                                                    ${dept.course} - ${dept.description}
                                                    <span class="badge badge-primary">0 sections</span>
                                                </button>
                                            </h2>
                                        </div>
                                        <div id="dept${dept.id}" class="collapse" data-parent="#collegeAccordion">
                                            <div class="card-body" id="dept-sections-${dept.id}">
                                                <div class="text-center">Loading sections...</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            $('#collegeAccordion').html(html);
                        } else {
                            $('#collegeAccordion').html('<div class="alert alert-info text-center p-3">No departments found</div>');
                        }
                    } catch (e) {
                        console.error('Error extracting courses:', e);
                        $('#collegeAccordion').html('<div class="alert alert-danger text-center p-3">Error processing departments</div>');
                    }
                },
                error: function() {
            $('#collegeAccordion').html(`<div class="alert alert-danger text-center p-3">${errorMessage}</div>`);
                }
            });
        }
    });
}

// Load strands dynamically
function loadStrands() {
    $.ajax({
        url: 'ajax.php?action=get_student_strands',
        method: 'GET',
        dataType: 'json',
        success: function(strands) {
            try {
                if (!Array.isArray(strands)) {
                    console.error('Invalid strand data:', strands);
                    $('#shsAccordion').html('<div class="alert alert-danger text-center p-3">Error: Invalid strand data</div>');
                    return;
                }
                
                if (strands.length === 0) {
                    $('#shsAccordion').html('<div class="alert alert-info text-center p-3">No strands found</div>');
                    return;
                }
                
                let html = '';
                
                strands.forEach(strand => {
                    html += `
                        <div class="card mb-3">
                            <div class="card-header">
                                <h2 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" 
                                            data-target="#strand${strand.id}">
                                        ${strand.code} - ${strand.name}
                                        <span class="badge badge-primary">${strand.section_count} sections</span>
                                    </button>
                                </h2>
                            </div>
                            <div id="strand${strand.id}" class="collapse" data-parent="#shsAccordion">
                                <div class="card-body" id="strand-sections-${strand.id}">
                                    <div class="text-center">Loading sections...</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#shsAccordion').html(html);
            } catch (e) {
                console.error('Error processing strands:', e);
                $('#shsAccordion').html('<div class="alert alert-danger text-center p-3">Error processing strand data</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading strands:', status, error);
            let errorMessage = 'Error loading strands';
            
            if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.error) {
                        errorMessage = `Error: ${response.error}`;
                    }
                } catch (e) {
                    console.error('Could not parse error response:', e);
                }
            }
            
            // Fall back to direct AJAX call if the action doesn't work
            $.ajax({
                url: 'strands.php',
                method: 'GET',
                success: function(html) {
                    // Extract strand data from HTML
                    try {
                        let strands = [];
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const rows = doc.querySelectorAll('table tbody tr');
                        
                        rows.forEach((row) => {
                            const cells = row.querySelectorAll('td');
                            if (cells.length >= 3) {
                                const id = row.getAttribute('data-id') || cells[0].textContent.trim();
                                strands.push({
                                    id: id,
                                    code: cells[1].textContent.trim(),
                                    name: cells[2].textContent.trim(),
                                    section_count: 0
                                });
                            }
                        });
                        
                        if (strands.length > 0) {
                            let html = '';
                            strands.forEach(strand => {
                                html += `
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h2 class="mb-0">
                                                <button class="btn btn-link" type="button" data-toggle="collapse" 
                                                        data-target="#strand${strand.id}">
                                                    ${strand.code} - ${strand.name}
                                                    <span class="badge badge-primary">0 sections</span>
                                                </button>
                                            </h2>
                                        </div>
                                        <div id="strand${strand.id}" class="collapse" data-parent="#shsAccordion">
                                            <div class="card-body" id="strand-sections-${strand.id}">
                                                <div class="text-center">Loading sections...</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            $('#shsAccordion').html(html);
                        } else {
                            $('#shsAccordion').html('<div class="alert alert-info text-center p-3">No strands found</div>');
                        }
                    } catch (e) {
                        console.error('Error extracting strands:', e);
                        $('#shsAccordion').html('<div class="alert alert-danger text-center p-3">Error processing strands</div>');
                    }
                },
                error: function() {
            $('#shsAccordion').html(`<div class="alert alert-danger text-center p-3">${errorMessage}</div>`);
                }
            });
        }
    });
}

// Add this JavaScript function to load section schedules
function loadSectionSchedules(sectionId) {
    // Show loading indicator
    $(`#schedule-${sectionId}`).html('<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading schedules...</div>');
    
    $.ajax({
        url: 'ajax.php?action=get_section_schedules',
        method: 'POST',
        data: { section_id: sectionId },
        dataType: 'json',
        success: function(schedules) {
            let html = '';
            
            // Check if there's an error in the response
            if (schedules && schedules.error) {
                console.error('Server returned error:', schedules.error);
                $(`#schedule-${sectionId}`).html(`<div class="alert alert-danger">Error: ${schedules.error}</div>`);
                return;
            }
            
            // Check if schedules exist and is an array with items
            if (Array.isArray(schedules) && schedules.length > 0) {
                html += '<table class="table table-sm table-bordered mt-2">';
                html += '<thead class="thead-light">';
                html += '<tr>';
                html += '<th>Subject</th>';
                html += '<th>Faculty</th>';
                html += '<th>Department/Strand</th>';
                html += '<th>Year/Grade</th>';
                html += '<th>Days</th>';
                html += '<th>Time</th>';
                html += '<th>Room</th>';
                html += '<th>Action</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';
                
                schedules.forEach(schedule => {
                    html += '<tr>';
                    html += `<td>${schedule.subject}</td>`;
                    html += `<td>${schedule.faculty}</td>`;
                    html += `<td>${schedule.department} / ${schedule.strand}</td>`;
                    html += `<td>${schedule.year} / ${schedule.grade}</td>`;
                    html += `<td>${schedule.days}</td>`;
                    html += `<td>${schedule.time}</td>`;
                    html += `<td>${schedule.room}</td>`;
                    html += `<td><button class="btn btn-sm btn-primary view-schedule" data-id="${schedule.id}">View</button> <button class="btn btn-sm btn-secondary edit-schedule" data-id="${schedule.id}">Edit</button></td>`;
                    html += '</tr>';
                });
                
                html += '</tbody>';
                html += '</table>';
            } else {
                html += '<div class="text-center">No schedules found</div>';
            }
            
            $(`#schedule-${sectionId}`).html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error loading schedules:', status, error);
            let errorMessage = 'Error loading schedules';
            
            if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.error) {
                        errorMessage = `Error: ${response.error}`;
                    }
                } catch (e) {
                    console.error('Could not parse error response:', e);
                }
            }
            
            $(`#schedule-${sectionId}`).html(`<div class="alert alert-danger">${errorMessage}</div>`);
        }
    });
}
</script>
</rewritten_file>