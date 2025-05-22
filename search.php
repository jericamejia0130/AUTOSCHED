<?php
include 'db_connect.php';
include 'header.php';

// Get search query from URL parameter
$query = isset($_GET['query']) ? $_GET['query'] : '';
$query = trim($conn->real_escape_string($query));

// Initialize results arrays
$faculty_results = [];
$department_results = [];
$subject_results = [];
$room_results = [];
$strand_results = [];
$section_results = [];
$student_results = [];

// Only search if query is not empty
if (!empty($query)) {
    // Create search pattern for SQL LIKE
    $search_pattern = "%{$query}%";
    
    // Search in faculty
    $faculty_sql = "SELECT f.id, f.name, f.email, f.contact, f.address, d.name as designation 
                   FROM faculty f 
                   LEFT JOIN designation d ON f.designation_id = d.id
                   WHERE f.name LIKE ? OR f.email LIKE ? OR f.contact LIKE ? OR f.address LIKE ? OR d.name LIKE ?";
    $stmt = $conn->prepare($faculty_sql);
    $stmt->bind_param("sssss", $search_pattern, $search_pattern, $search_pattern, $search_pattern, $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $faculty_results[] = $row;
    }
    
    // Search in departments (courses)
    $dept_sql = "SELECT id, course, description FROM courses WHERE course LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($dept_sql);
    $stmt->bind_param("ss", $search_pattern, $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $department_results[] = $row;
    }
    
    // Search in subjects
    $subject_sql = "SELECT s.id, s.subject, s.description, c.course 
                   FROM subjects s 
                   LEFT JOIN courses c ON s.course_id = c.id
                   WHERE s.subject LIKE ? OR s.description LIKE ? OR c.course LIKE ?";
    $stmt = $conn->prepare($subject_sql);
    $stmt->bind_param("sss", $search_pattern, $search_pattern, $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subject_results[] = $row;
    }
    
    // Search in rooms
    $room_sql = "SELECT id, name, capacity, description FROM rooms WHERE name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($room_sql);
    $stmt->bind_param("ss", $search_pattern, $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $room_results[] = $row;
    }
    
    // Search in strands
    $strand_sql = "SELECT id, name, description FROM strands WHERE name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($strand_sql);
    $stmt->bind_param("ss", $search_pattern, $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $strand_results[] = $row;
    }
    
    // Search in sections
    $section_sql = "SELECT s.id, s.name, s.grade_level, c.course, str.name as strand 
                   FROM sections s 
                   LEFT JOIN courses c ON s.course_id = c.id
                   LEFT JOIN strands str ON s.strand_id = str.id
                   WHERE s.name LIKE ? OR c.course LIKE ? OR str.name LIKE ?";
    $stmt = $conn->prepare($section_sql);
    $stmt->bind_param("sss", $search_pattern, $search_pattern, $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $section_results[] = $row;
    }
    
    // Search in students (based on student_info structure)
    if ($_SESSION['login_type'] == 'Admin') {
        $student_sql = "SELECT s.id, s.student_id, s.firstname, s.lastname, s.email, 
                      c.course, sec.name as section, str.name as strand
                      FROM students s 
                      LEFT JOIN courses c ON s.course_id = c.id
                      LEFT JOIN sections sec ON s.section_id = sec.id
                      LEFT JOIN strands str ON s.strand_id = str.id
                      WHERE s.student_id LIKE ? OR 
                            s.firstname LIKE ? OR 
                            s.lastname LIKE ? OR 
                            s.email LIKE ? OR
                            c.course LIKE ? OR
                            sec.name LIKE ? OR
                            str.name LIKE ?";
        $stmt = $conn->prepare($student_sql);
        $stmt->bind_param("sssssss", $search_pattern, $search_pattern, $search_pattern, 
                         $search_pattern, $search_pattern, $search_pattern, $search_pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $student_results[] = $row;
        }
    }
}

// Function to format results as cards with links
function formatResults($results, $type, $link, $title_field, $description_field, $icon_class) {
    if (empty($results)) return '';
    
    $output = '<div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="'.$icon_class.'"></i> ' . ucfirst($type) . ' Results (' . count($results) . ')</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">';
    
    foreach ($results as $item) {
        $id = $item['id'];
        $title = $item[$title_field];
        $desc = isset($item[$description_field]) ? $item[$description_field] : '';
        
        $output .= '<a href="'.$link.'?id='.$id.'" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">'.$title.'</h5>
                        </div>
                        <p class="mb-1">'.$desc.'</p>';
        
        // Add additional details specific to each type
        if ($type == 'faculty') {
            $output .= '<small>Email: '.$item['email'].' | Contact: '.$item['contact'].' | Designation: '.$item['designation'].'</small>';
        } elseif ($type == 'subjects') {
            $output .= '<small>Department: '.$item['course'].'</small>';
        } elseif ($type == 'sections') {
            $output .= '<small>Department: '.$item['course'].' | Grade Level: '.$item['grade_level'].'</small>';
        } elseif ($type == 'student') {
            $output .= '<small>ID: '.$item['student_id'].' | Department: '.$item['course'].' | Section: '.$item['section'].'</small>';
        }
        
        $output .= '</a>';
    }
    
    $output .= '</div></div></div></div>';
    return $output;
}
?>

<style>
    .search-results-container {
        margin-top: 20px;
    }
    .search-results-section {
        margin-bottom: 30px;
    }
    .search-results-header {
        background-color: var(--light-primary);
        color: white;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    body.dark-mode .search-results-header {
        background-color: var(--dark-primary);
    }
    .result-count {
        font-size: 14px;
        margin-left: 10px;
        font-weight: normal;
    }
    .search-item {
        margin-bottom: 15px;
        transition: all 0.2s;
    }
    .search-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    body.dark-mode .search-item {
        background-color: var(--dark-card);
        border-color: var(--dark-border);
    }
    .search-input-large {
        font-size: 18px;
        height: 50px;
        border-radius: 25px;
    }
    .search-btn-large {
        height: 50px;
        border-radius: 25px;
        width: 100px;
    }
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: var(--light-primary);
    }
    body.dark-mode .section-title {
        color: var(--dark-primary);
    }
    .no-results {
        padding: 30px;
        text-align: center;
        font-size: 1.2rem;
        color: #888;
    }
    body.dark-mode .no-results {
        color: #aaa;
    }
</style>

<main id="view-panel">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fa fa-search"></i> Search Results</h4>
                    </div>
                    <div class="card-body">
                        <!-- Search form at the top of results -->
                        <form action="search.php" method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="query" class="form-control search-input-large" 
                                       placeholder="Search for faculty, departments, subjects, rooms..." 
                                       value="<?php echo htmlspecialchars($query); ?>" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary search-btn-large">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (!empty($query)): ?>
                            <div class="alert alert-info">
                                Showing results for: <strong><?php echo htmlspecialchars($query); ?></strong>
                            </div>
                            
                            <div class="row search-results-container">
                                <?php
                                // Display faculty results
                                echo formatResults($faculty_results, 'faculty', 'manage_faculty.php', 'name', 'address', 'fa fa-user-tie');
                                
                                // Display department results
                                echo formatResults($department_results, 'departments', 'manage_course.php', 'course', 'description', 'fa fa-list');
                                
                                // Display subject results
                                echo formatResults($subject_results, 'subjects', 'manage_subject.php', 'subject', 'description', 'fa fa-book');
                                
                                // Display room results
                                echo formatResults($room_results, 'rooms', 'manage_room.php', 'name', 'description', 'fa fa-door-open');
                                
                                // Display strand results
                                echo formatResults($strand_results, 'strands', 'manage_strand.php', 'name', 'description', 'fa fa-stream');
                                
                                // Display section results
                                echo formatResults($section_results, 'sections', 'manage_section.php', 'name', 'course', 'fa fa-layer-group');
                                
                                // Display student results if user is admin
                                if ($_SESSION['login_type'] == 'Admin') {
                                    echo formatResults($student_results, 'student', 'manage_student.php', 'firstname', 'lastname', 'fa fa-user-graduate');
                                }
                                
                                // Check if there are no results at all
                                $total_results = count($faculty_results) + count($department_results) + 
                                              count($subject_results) + count($room_results) + 
                                              count($strand_results) + count($section_results);
                                if ($_SESSION['login_type'] == 'Admin') {
                                    $total_results += count($student_results);
                                }
                                
                                if ($total_results == 0) {
                                    echo '<div class="col-md-12">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <i class="fa fa-search fa-4x text-muted mb-3"></i>
                                                    <h4>No results found</h4>
                                                    <p class="text-muted">Try different keywords or check your spelling</p>
                                                </div>
                                            </div>
                                          </div>';
                                }
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fa fa-search fa-5x text-muted mb-3"></i>
                                <h3>Enter a search term to find faculty, departments, subjects and more.</h3>
                                <p class="text-muted">You can search by name, email, ID, or other details.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?> 