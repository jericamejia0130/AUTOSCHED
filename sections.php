<!-- filepath: c:\xampp\htdocs\schedulingold - Copy\admin\sections.php -->
<?php include 'db_connect.php'; ?>

<?php
// Get college sections with department information
$college_sections = $conn->query("SELECT s.*,     c.course,     c.description as department_name,    CONCAT(f.lastname, ', ', f.firstname, ' ', COALESCE(f.middlename, '')) as adviser_name,    COALESCE(s.section_code, CONCAT('code', s.id)) as section_code     FROM sections s     LEFT JOIN courses c ON s.course_id = c.id     LEFT JOIN faculty f ON s.faculty_id = f.id    WHERE s.year_level <= 3 AND s.year_level > 0     ORDER BY c.course ASC, s.year_level ASC, s.name ASC");

// Updated query for SHS sections with improved JOIN to ensure we see all sections
$shs_sections = $conn->query("SELECT s.*,     COALESCE(st.code, 'N/A') as strand_code,    COALESCE(st.name, 'Unknown') as strand_name,    CONCAT(f.lastname, ', ', f.firstname, ' ', COALESCE(f.middlename, '')) as adviser_name,    COALESCE(s.section_code, CONCAT('code', s.id)) as section_code    FROM sections s     LEFT JOIN strands st ON s.strand_id = st.id     LEFT JOIN faculty f ON s.faculty_id = f.id    WHERE s.year_level IN (11,12)    ORDER BY s.id DESC");

// Check for query errors and log them
if (!$college_sections) {
    echo "College sections query error: " . $conn->error;
    error_log("College sections query error: " . $conn->error);
}
if (!$shs_sections) {
    echo "SHS sections query error: " . $conn->error;
    error_log("SHS sections query error: " . $conn->error);
}

// Debug: Log the number of sections found
error_log("College sections found: " . ($college_sections ? $college_sections->num_rows : 0));
error_log("SHS sections found: " . ($shs_sections ? $shs_sections->num_rows : 0));

// Debug: Show all SHS sections in the database
$debug_query = $conn->query("SELECT * FROM sections WHERE year_level IN (11,12)");
if ($debug_query) {
    error_log("Direct DB check - SHS sections: " . $debug_query->num_rows);
    while ($row = $debug_query->fetch_assoc()) {
        error_log("Section found - ID: " . $row['id'] . ", Name: " . $row['name'] . ", Year: " . $row['year_level'] . ", Strand: " . $row['strand_id']);
    }
} else {
    error_log("Debug query failed: " . $conn->error);
}
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#college" data-toggle="tab">College Sections</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#shs" data-toggle="tab">Senior High Sections</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <button class="btn btn-primary btn-sm float-right mb-3" id="new_section">
                    <i class="fa fa-plus"></i> New Section
                </button>
                
                <div class="tab-content">
                                        <!-- College Sections Table -->                    <div class="tab-pane fade show active" id="college">                        <div class="table-responsive">                            <table class="table table-bordered table-hover" id="college_table">
                                                                <thead>                                    <tr>                                        <th width="5%">#</th>                                        <th width="10%">Department</th>                                        <th width="20%">Department Name</th>                                        <th width="10%">Year Level</th>                                        <th width="15%">Section</th>                                        <th width="15%">Section Code</th>                                        <th width="15%">Adviser</th>                                        <th width="10%">Action</th>                                    </tr>                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    if($college_sections && $college_sections->num_rows > 0):
                                        while($row = $college_sections->fetch_assoc()):
                                    ?>
                                                                        <tr>                                        <td><?php echo $i++; ?></td>                                        <td><?php echo $row['course'] ?? 'N/A'; ?></td>                                        <td><?php echo $row['department_name'] ?? 'N/A'; ?></td>                                        <td><?php echo $row['year_level'] . ($row['year_level']==1 ? 'st' : ($row['year_level']==2 ? 'nd' : 'rd')) ?> Year</td>                                        <td><?php echo $row['name']; ?></td>                                        <td><?php echo $row['section_code']; ?></td>                                        <td><?php echo $row['adviser_name'] ? $row['adviser_name'] : '<span class="text-muted">Not Assigned</span>'; ?></td>                                        <td>                                            <div class="action-buttons">                                                <button class="btn btn-sm btn-primary edit_section" data-id="<?php echo $row['id']; ?>">Edit</button>                                                <button class="btn btn-sm btn-danger delete_section" data-id="<?php echo $row['id']; ?>">Delete</button>                                            </div>                                        </td>                                    </tr>
                                    <?php 
                                        endwhile; 
                                    else:
                                    ?>
                                                                        <tr>                                        <td colspan="8" class="text-center">No college sections found</td>                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Senior High Sections Table -->
                    <div class="tab-pane fade" id="shs">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="shs_table">
                                                                <thead>                                    <tr>                                        <th width="5%">#</th>                                        <th width="10%">Strand Code</th>                                        <th width="20%">Strand Name</th>                                        <th width="10%">Grade Level</th>                                        <th width="15%">Section</th>                                        <th width="15%">Section Code</th>                                        <th width="15%">Adviser</th>                                        <th width="10%">Action</th>                                    </tr>                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    if($shs_sections && $shs_sections->num_rows > 0):
                                        while($row = $shs_sections->fetch_assoc()):
                                    ?>
                                                                        <tr>                                        <td class="text-center"><?php echo $i++ ?></td>                                        <td><?php echo $row['strand_code'] ?></td>                                        <td><?php echo $row['strand_name'] ?></td>                                        <td>                                            <?php if($row['year_level'] == 11): ?>                                                Grade 11                                            <?php elseif($row['year_level'] == 12): ?>                                                Grade 12                                            <?php else: ?>                                                <?php echo $row['year_level'] ?>                                            <?php endif; ?>                                        </td>                                        <td><?php echo $row['name'] ?></td>                                        <td><?php echo $row['section_code'] ?></td>                                        <td><?php echo $row['adviser_name'] ? $row['adviser_name'] : '<span class="text-muted">Not Assigned</span>'; ?></td>                                        <td class="text-center">                                            <div class="action-buttons">                                                <button class="btn btn-sm btn-primary edit_section" type="button" data-id="<?php echo $row['id'] ?>">Edit</button>                                                <button class="btn btn-sm btn-danger delete_section" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>                                            </div>                                        </td>                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                                                        <tr>                                        <td colspan="8" class="text-center">No SHS sections found</td>                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
.table th {
    background-color: #f8f9fa;
}
#new_section {
    margin-right: 15px;
}

/* Improved responsive table styles */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 1rem;
    scrollbar-width: thin;
}

/* Custom scrollbar styling */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,.2);
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-track {
    background-color: rgba(0,0,0,.05);
}

/* Button styling */
.action-buttons {
    display: flex;
    flex-wrap: nowrap;
    justify-content: center;
    gap: 5px;
}

/* Ensure tables are responsive at full screen */
.table {
    width: 100%;
    margin-bottom: 1rem;
}

/* Optimize content display for full screen */
@media (min-width: 1201px) {
    .table td, .table th {
        white-space: normal;
        word-break: break-word;
    }
    
    /* Set fixed column widths for better display */
    #college_table th:first-child,
    #college_table td:first-child,
    #shs_table th:first-child,
    #shs_table td:first-child {
        width: 5%;
    }
    
    #college_table th:nth-child(2),
    #college_table td:nth-child(2),
    #shs_table th:nth-child(2), 
    #shs_table td:nth-child(2) {
        width: 15%;
    }
    
    #college_table th:nth-child(3),
    #college_table td:nth-child(3),
    #shs_table th:nth-child(3),
    #shs_table td:nth-child(3) {
        width: 25%;
    }
    
    #college_table th:nth-child(4),
    #college_table td:nth-child(4),
    #shs_table th:nth-child(4),
    #shs_table td:nth-child(4) {
        width: 15%;
    }
    
    #college_table th:nth-child(5),
    #college_table td:nth-child(5),
    #shs_table th:nth-child(5),
    #shs_table td:nth-child(5) {
        width: 20%;
    }
    
    #college_table th:last-child,
    #college_table td:last-child,
    #shs_table th:last-child,
    #shs_table td:last-child {
        width: 20%;
    }
}

/* For smaller screens, prevent wrapping and ensure horizontal scrolling */
@media (max-width: 1200px) {
    .table {
        white-space: nowrap;
    }
}

/* Ensure action buttons stay visible and well-formatted on mobile */
@media (max-width: 768px) {
    .table th, .table td {
        padding: 0.5rem;
        vertical-align: middle;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 5px;
    }
    
    .action-buttons .btn {
        width: 100%;
        margin-bottom: 3px;
        padding: 0.2rem 0.5rem;
    }
    
    /* Horizontal scroll indicator */
    .table-responsive::after {
        content: "";
        position: absolute;
        bottom: 0;
        right: 0;
        width: 50px;
        height: 100%;
        background: linear-gradient(to right, transparent, rgba(0,0,0,0.05));
        pointer-events: none;
    }
    
    /* Add visual feedback for horizontal scrolling */
    .swipe-indicator {
        display: block;
        text-align: center;
        margin-bottom: 0.5rem;
        font-size: 0.8rem;
        color: #6c757d;
    }
}

/* Fix for dark mode */
body.dark-mode .table-responsive::-webkit-scrollbar-thumb {
    background-color: rgba(255,255,255,.2);
}

body.dark-mode .table-responsive::-webkit-scrollbar-track {
    background-color: rgba(255,255,255,.05);
}

body.dark-mode .table-responsive::after {
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.05));
}
</style>

<script>
$(document).ready(function(){
    $('#new_section').click(function(){
        uni_modal('New Section', 'manage_section.php');
    });
    
    $('.edit_section').click(function(){
        uni_modal('Edit Section', 'manage_section.php?id=' + $(this).attr('data-id'));
    });
    
    $('.delete_section').click(function(){
        _conf("Are you sure to delete this section?", "delete_section", [$(this).attr('data-id')]);
    });
    
    $('#college_table, #shs_table').dataTable({
        responsive: true,
        language: {
            search: "Filter records:",
            searchPlaceholder: "Search sections..."
        },
        dom: 'Bfrtip',
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ]
    });
    
    // Add swipe indicator for mobile
    if(window.innerWidth <= 768) {
        $('.table-responsive').each(function() {
            $(this).prepend('<div class="swipe-indicator"><i class="fas fa-arrows-alt-h"></i> Swipe to see more</div>');
        });
    }
    
    // Hide swipe indicator after scroll
    $('.table-responsive').on('scroll', function() {
        $(this).find('.swipe-indicator').fadeOut();
    });
});
</script>