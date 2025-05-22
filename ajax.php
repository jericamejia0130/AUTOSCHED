<?php
session_start(); // Start session at the very beginning
include 'db_connect.php';
ob_start();
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : null); // Check if 'action' exists

include 'admin_class.php';
$crud = new Action();

if ($action) { // Only proceed if $action is not null
    if ($action == 'delete_schedule') {
        $delete = $crud->delete_schedule();
        exit; // Ensure we don't output anything else
    }
    if ($action == 'login') {
        // Simple text response, not JSON
        error_log("Login request received: " . print_r($_POST, true));
        
        // Call the login method and pass through the result directly
        echo $crud->login();
        exit;
    }
    if ($action == 'login_faculty') {
        $login_faculty = $crud->login_faculty();
        if ($login_faculty) {
            echo $login_faculty;
        }
    }
    if ($action == 'login2') {
        $login = $crud->login2();
        if ($login) {
            echo $login;
        }
    }
    if ($action == 'logout') {
        $logout = $crud->logout();
        if ($logout) {
            echo $logout;
        }
    }
    if ($action == 'logout2') {
        $logout = $crud->logout2();
        if ($logout) {
            echo $logout;
        }
    }
    if ($action == 'save_user') {
        $save = $crud->save_user();
        if ($save) {
            echo $save;
        }
    }
    if ($action == 'delete_user') {
        $save = $crud->delete_user();
        if ($save) {
            echo $save;
        }
    }
    if ($action == 'signup') {
        $save = $crud->signup();
        if ($save) {
            echo $save;
        }
    }
    if ($action == 'update_account') {
        $save = $crud->update_account();
        if ($save) {
            echo $save;
        }
    }
    if ($action == "save_settings") {
        $save = $crud->save_settings();
        if ($save) {
            echo $save;
        }
    }
    if ($action == "save_course") {
        $id = $_POST['id'] ?? '';
        $course = $conn->real_escape_string($_POST['course']);
        $description = $conn->real_escape_string($_POST['description']);
        
        try {
            if(empty($id)) {
                // Check for duplicate course code
                $check = $conn->query("SELECT id FROM courses WHERE course = '$course'");
                if($check->num_rows > 0) {
                    echo json_encode(['status' => 0, 'message' => 'Department code already exists']);
                    exit;
                }
                
                $save = $conn->query("INSERT INTO courses (course, description) VALUES ('$course', '$description')");
            } else {
                $save = $conn->query("UPDATE courses SET course = '$course', description = '$description' WHERE id = '$id'");
            }
            
            if($save) {
                echo json_encode(['status' => 1]);
            } else {
                echo json_encode(['status' => 0, 'message' => 'Failed to save department: ' . $conn->error]);
            }
        } catch(Exception $e) {
            echo json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    if ($action == "delete_course") {
        try {
            $id = intval($_POST['id']);
            
            // Check if course is being used in sections or schedules
            $check_sections = $conn->query("SELECT COUNT(*) as count FROM sections WHERE course_id = $id")->fetch_assoc()['count'];
            $check_schedules = $conn->query("SELECT COUNT(*) as count FROM schedules WHERE course_id = $id")->fetch_assoc()['count'];
            
            if($check_sections > 0 || $check_schedules > 0) {
                echo json_encode(['status' => 0, 'message' => 'Cannot delete department. It is being used in sections or schedules.']);
                exit;
            }
            
            $delete = $conn->query("DELETE FROM courses WHERE id = $id");
            
            if($delete) {
                echo 1;
            } else {
                echo json_encode(['status' => 0, 'message' => 'Failed to delete department']);
            }
        } catch(Exception $e) {
            echo json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    if ($action == "save_subject") {
        // Enable error logging
        error_log("save_subject called with: " . json_encode($_POST));
        
        $id = $_POST['id'] ?? '';
        $subject = $_POST['subject'];
        $units = $_POST['units'];
        $type = $_POST['type'];
        
        // Handle department_id from FormData - it will be in department_id[] format
        $department_ids = isset($_POST['department_id']) ? $_POST['department_id'] : 
                          (isset($_POST['department_id[]']) ? $_POST['department_id[]'] : []);
        
        // Handle department_id if it's not an array but a string
        if(!is_array($department_ids) && !empty($department_ids)) {
            $department_ids = [$department_ids];
        }
        
        // Log department IDs
        error_log("Department IDs: " . json_encode($department_ids));
        
        try {
            // Begin transaction
            $conn->begin_transaction();
            
            if(empty($id)){
                // Insert new subject without department
                $stmt = $conn->prepare("INSERT INTO subjects (subject, units, type) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $subject, $units, $type);
                $save = $stmt->execute();
                $new_id = $conn->insert_id;
                
                error_log("New subject created with ID: " . $new_id);
                
                // Then add department relationships if any are selected
                if(!empty($department_ids)) {
                    foreach($department_ids as $dept_id) {
                        if(empty($dept_id)) continue; // Skip empty values
                        $sql = "INSERT INTO subject_departments (subject_id, department_id) VALUES ('$new_id', '$dept_id')";
                        error_log("Adding department: " . $sql);
                        $result = $conn->query($sql);
                        if(!$result) {
                            error_log("Failed to add department: " . $conn->error);
                            throw new Exception("Failed to add department relation: " . $conn->error);
                        }
                    }
                }
            } else {
                // Update subject
                $stmt = $conn->prepare("UPDATE subjects SET subject = ?, units = ?, type = ? WHERE id = ?");
                $stmt->bind_param("sisi", $subject, $units, $type, $id);
                $save = $stmt->execute();
                
                error_log("Subject updated with ID: " . $id);
                
                // Remove all existing department relationships
                $delete_sql = "DELETE FROM subject_departments WHERE subject_id = '$id'";
                error_log("Removing old departments: " . $delete_sql);
                $result = $conn->query($delete_sql);
                if(!$result) {
                    error_log("Failed to delete old departments: " . $conn->error);
                    throw new Exception("Failed to delete old department relations: " . $conn->error);
                }
                
                // Add new department relationships
                if(!empty($department_ids)) {
                    foreach($department_ids as $dept_id) {
                        if(empty($dept_id)) continue; // Skip empty values
                        $sql = "INSERT INTO subject_departments (subject_id, department_id) VALUES ('$id', '$dept_id')";
                        error_log("Adding department for existing subject: " . $sql);
                        $result = $conn->query($sql);
                        if(!$result) {
                            error_log("Failed to add department: " . $conn->error);
                            throw new Exception("Failed to add department relation: " . $conn->error);
                        }
                    }
                }
            }
            
            // Commit if everything worked
            $conn->commit();
            error_log("Transaction committed successfully");
            echo json_encode(['status' => 1, 'message' => 'Subject saved successfully']);
        } catch (Exception $e) {
            // Roll back if there was an error
            $conn->rollback();
            error_log("Transaction rolled back due to error: " . $e->getMessage());
            echo json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // Get schedule data by day of week for the dashboard chart
    if ($action == "get_schedule_by_day") {
        try {
        // Get count of schedules for each day of week
        $days_of_week = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $result = array();
        
        // Initialize results array with all days set to 0
        foreach ($days_of_week as $index => $day) {
            $result[$day] = 0;
        }
        
        // Query to get schedules by day of week
        $query = "SELECT dow, COUNT(*) as class_count FROM schedules 
                 WHERE dow IS NOT NULL AND dow != '' 
                 GROUP BY dow";
        $schedules = $conn->query($query);
        
        // Process results
        if ($schedules && $schedules->num_rows > 0) {
            while ($row = $schedules->fetch_assoc()) {
                // The dow column could contain multiple days as comma-separated values
                $dow_list = explode(',', $row['dow']);
                foreach ($dow_list as $day_index) {
                    if (isset($days_of_week[$day_index])) {
                        $day_name = $days_of_week[$day_index];
                        // Add the count for this specific day
                        $result[$day_name] += $row['class_count'];
                    }
                }
            }
        }
        
        // Return JSON data
        header('Content-Type: application/json');
        echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    // Get time-based schedule data for heatmap chart
    if ($action == "get_schedule_time_heatmap") {
        try {
        // Define days of week
        $days_of_week = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        
        // Define time slots (1-hour intervals from 7am to 7pm)
        $time_slots = array(
            '7:00 AM', '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', 
            '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', 
            '5:00 PM', '6:00 PM', '7:00 PM'
        );
        
        // Initialize result array
        $result = array();
        foreach ($days_of_week as $day) {
            $result[$day] = array();
            foreach ($time_slots as $time) {
                $result[$day][$time] = 0;
            }
        }
        
        // Query to get schedule data with time slots
        $query = "SELECT 
                    dow,
                    TIME_FORMAT(time_from, '%h:00 %p') as hour_start
                FROM schedules 
                WHERE dow IS NOT NULL AND dow != '' 
                    AND time_from IS NOT NULL";
        
        $schedules = $conn->query($query);
        
        // Process results
        if ($schedules && $schedules->num_rows > 0) {
            while ($row = $schedules->fetch_assoc()) {
                // Handle multiple days in dow
                $dow_list = explode(',', $row['dow']);
                
                foreach ($dow_list as $day_index) {
                    if (isset($days_of_week[$day_index])) {
                        $day_name = $days_of_week[$day_index];
                        $hour = $row['hour_start'];
                        
                        // Only count if the hour is in our predefined slots
                        if (in_array($hour, $time_slots)) {
                            if (!isset($result[$day_name][$hour])) {
                                $result[$day_name][$hour] = 0;
                            }
                            $result[$day_name][$hour]++;
                        }
                    }
                }
            }
        }
        
        // Return JSON data
        header('Content-Type: application/json');
        echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($action == "save_strand_subject") {
        try {
            // Get form data
            $id = isset($_POST['id']) ? $_POST['id'] : '';
            $subject = isset($_POST['subject']) ? $conn->real_escape_string(trim($_POST['subject'])) : '';
            $units = isset($_POST['units']) ? intval($_POST['units']) : 0;
            $subject_type = isset($_POST['subject_type']) ? $_POST['subject_type'] : '';
            
            // Get strand IDs (handle both array and single value)
            $strand_ids = isset($_POST['strand_id']) ? $_POST['strand_id'] : 
                        (isset($_POST['strand_id[]']) ? $_POST['strand_id[]'] : []);
            
            if (!is_array($strand_ids) && !empty($strand_ids)) {
                $strand_ids = [$strand_ids];
            }
            
            // Validate required fields
            if (empty($subject) || empty($subject_type) || $units <= 0) {
                echo json_encode(['status' => 0, 'message' => 'Please fill in all required fields']);
                exit;
            }
            
            // Begin transaction
            $conn->begin_transaction();
            
            if (empty($id)) {
                // Check for duplicate subject
                $check = $conn->query("SELECT id FROM strand_subjects WHERE subject = '$subject'");
                if ($check->num_rows > 0) {
                    echo json_encode(['status' => 0, 'message' => 'Subject already exists']);
                    exit;
                }
                
                // Insert new subject
                $save = $conn->query("INSERT INTO strand_subjects (subject, units, subject_type) 
                                    VALUES ('$subject', $units, '$subject_type')");
                if (!$save) {
                    throw new Exception("Failed to save subject: " . $conn->error);
                }
                
                $new_id = $conn->insert_id;
                
                // Add strand relationships
                if (!empty($strand_ids)) {
                    foreach ($strand_ids as $strand_id) {
                        if (empty($strand_id)) continue;
                        $save = $conn->query("INSERT INTO subject_strands (subject_id, strand_id) 
                                            VALUES ($new_id, $strand_id)");
                        if (!$save) {
                            throw new Exception("Failed to add strand relation: " . $conn->error);
                        }
                    }
                }
            } else {
                // Update existing subject
                $save = $conn->query("UPDATE strand_subjects 
                                    SET subject = '$subject', units = $units, subject_type = '$subject_type' 
                                    WHERE id = $id");
                if (!$save) {
                    throw new Exception("Failed to update subject: " . $conn->error);
                }
                
                // Remove existing strand relationships
                $delete = $conn->query("DELETE FROM subject_strands WHERE subject_id = $id");
                if (!$delete) {
                    throw new Exception("Failed to remove existing strands: " . $conn->error);
                }
                
                // Add new strand relationships
                if (!empty($strand_ids)) {
                    foreach ($strand_ids as $strand_id) {
                        if (empty($strand_id)) continue;
                        $save = $conn->query("INSERT INTO subject_strands (subject_id, strand_id) 
                                            VALUES ($id, $strand_id)");
                        if (!$save) {
                            throw new Exception("Failed to add strand relation: " . $conn->error);
                        }
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            echo json_encode(['status' => 1, 'message' => 'Subject saved successfully']);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($action == "save_section"){
        $id = $_POST['id'];
        $name = $_POST['name'];
        $faculty_id = $_POST['faculty_id'] ? $_POST['faculty_id'] : "NULL";
        $section_code = isset($_POST['section_code']) ? $conn->real_escape_string($_POST['section_code']) : '';
        
        // Check if section_code column exists, if not, add it
        $check_column = $conn->query("SHOW COLUMNS FROM sections LIKE 'section_code'");
        if($check_column->num_rows == 0) {
            // Add section_code column if it doesn't exist
            $conn->query("ALTER TABLE sections ADD COLUMN section_code VARCHAR(50) NULL");
        }
        
        $data = " name = '$name' ";
        if(isset($_POST['course_id']) && $_POST['course_id']) {
            $data .= ", course_id = '{$_POST['course_id']}' ";
            $data .= ", year_level = '{$_POST['year_level']}' ";
        }
        if(isset($_POST['strand_id']) && $_POST['strand_id']) {
            $data .= ", strand_id = '{$_POST['strand_id']}' ";
            $data .= ", year_level = '{$_POST['year_level']}' ";
        }
        $data .= ", faculty_id = $faculty_id ";
        
        // Add section code to the data
        if(!empty($section_code)) {
            $data .= ", section_code = '$section_code' ";
        }
        
        if(empty($id)){
            $save = $conn->query("INSERT INTO sections SET $data");
            
            // If section code is empty but a section was created, generate a default code
            if($save && empty($section_code)) {
                $new_id = $conn->insert_id;
                $default_code = 'code' . $new_id;
                $conn->query("UPDATE sections SET section_code = '$default_code' WHERE id = $new_id");
            }
        } else {
            $save = $conn->query("UPDATE sections SET $data WHERE id = $id");
        }
        
        if($save){
            echo json_encode(array("status" => 1, "msg" => "Data successfully saved"));
        } else {
            echo json_encode(array("status" => 0, "msg" => "Error: " . $conn->error));
        }
        exit;
    }

    // For debugging the save_schedule action
    if ($action == "save_schedule") {
        try {
            // Ensure proper content type
            header('Content-Type: application/json');
            
            // Debug the received data
            error_log("save_schedule called with data: " . json_encode($_POST));
            
            // Call the save_schedule method from admin_class
            $result = $crud->save_schedule();
            
            // Log the result
            error_log("save_schedule result: " . $result);
            
            // Ensure we echo the result to the client - no additional processing needed
            // since admin_class.php already returns valid JSON
            echo $result;
        } catch (Exception $e) {
            error_log("Exception in save_schedule: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit; // Ensure script exits here
    }
    
    // Handler for getting section schedules for student info page
    if ($action == "get_section_schedules") {
        try {
            // Set content type for JSON response
            header('Content-Type: application/json');
            
            // Get section ID from request
            $section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : 0;
            
            if (!$section_id) {
                throw new Exception("Missing section ID");
            }
            
            // Log for debugging
            error_log("Getting schedules for section ID: " . $section_id);
            
            // Query to get all schedules for this section with related data
            $query = "SELECT s.*,
                      CONCAT(f.lastname, ', ', f.firstname, ' ', COALESCE(f.middlename, '')) AS faculty_name,
                      sub.subject,
                      c.course AS department_name,
                      st.name AS strand_name,
                      r.name AS room_name,
                      sec.year_level
                      FROM schedules s
                      LEFT JOIN faculty f ON s.faculty_id = f.id
                      LEFT JOIN subjects sub ON s.subject_id = sub.id
                      LEFT JOIN courses c ON s.course_id = c.id
                      LEFT JOIN strands st ON s.strand_id = st.id
                      LEFT JOIN rooms r ON s.room_id = r.id
                      LEFT JOIN sections sec ON s.section_id = sec.id
                      WHERE s.section_id = $section_id
                      ORDER BY s.time_from ASC, s.time_to ASC";
            
            $schedules = $conn->query($query);
            
            if (!$schedules) {
                throw new Exception("Database query failed: " . $conn->error);
            }
            
            $data = array();
            while ($row = $schedules->fetch_assoc()) {
                $data[] = $row;
            }
            
            // Log the number of schedules found
            error_log("Found " . count($data) . " schedules for section ID: " . $section_id);
            
            // Return the schedules data
            echo json_encode($data);
        } catch (Exception $e) {
            error_log("Error in get_section_schedules: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    // Handler for getting student departments for student info page
    if ($action == "get_student_departments") {
        try {
            header('Content-Type: application/json');
            
            // Query to get all departments with their section counts
            $query = "SELECT c.*, 
                      (SELECT COUNT(*) FROM sections WHERE course_id = c.id) AS section_count
                      FROM courses c
                      ORDER BY c.course ASC";
            
            $result = $conn->query($query);
            
            if (!$result) {
                throw new Exception("Database query failed: " . $conn->error);
            }
            
            $departments = array();
            while ($row = $result->fetch_assoc()) {
                $departments[] = $row;
            }
            
            echo json_encode($departments);
        } catch (Exception $e) {
            error_log("Error in get_student_departments: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    // Handler for getting student strands for student info page
    if ($action == "get_student_strands") {
        try {
            header('Content-Type: application/json');
            
            // Query to get all strands with their section counts
            $query = "SELECT st.*, 
                      (SELECT COUNT(*) FROM sections WHERE strand_id = st.id) AS section_count
                      FROM strands st
                      ORDER BY st.name ASC";
            
            $result = $conn->query($query);
            
            if (!$result) {
                throw new Exception("Database query failed: " . $conn->error);
            }
            
            $strands = array();
            while ($row = $result->fetch_assoc()) {
                $strands[] = $row;
            }
            
            echo json_encode($strands);
        } catch (Exception $e) {
            error_log("Error in get_student_strands: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    // Handler for getting department sections for student info page
    if ($action == "get_department_sections") {
        try {
            header('Content-Type: application/json');
            
            $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
            
            if (!$department_id) {
                throw new Exception("Missing department ID");
            }
            
            $dept_id = $_POST['department_id'];
    
            $query = "SELECT s.*, 
                CONCAT(f.lastname, ', ', f.firstname, ' ', COALESCE(f.middlename, '')) as adviser_name
                FROM sections s 
                LEFT JOIN faculty f ON s.faculty_id = f.id
                WHERE s.course_id = '$dept_id' 
                ORDER BY s.year_level ASC, s.name ASC";
            
            $sections = array();
            $result = $conn->query($query);
            
            if($result) {
                while($row = $result->fetch_assoc()) {
                    $sections[] = $row;
                }
                echo json_encode($sections);
            } else {
                echo json_encode(array("error" => "Error fetching sections: " . $conn->error));
            }
        } catch (Exception $e) {
            error_log("Error in get_department_sections: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    // Handler for getting strand sections for student info page
    if ($action == "get_strand_sections") {
        try {
            header('Content-Type: application/json');
            
            $strand_id = isset($_POST['strand_id']) ? intval($_POST['strand_id']) : 0;
            $grade_level = isset($_POST['grade_level']) ? intval($_POST['grade_level']) : 0;
            
            if (!$strand_id || !$grade_level) {
                throw new Exception("Missing strand ID or grade level");
            }
            
            // Query to get all sections for this strand and grade level
            $query = "SELECT s.* 
                      FROM sections s
                      WHERE s.strand_id = $strand_id AND s.year_level = $grade_level
                      ORDER BY s.name ASC";
            
            $result = $conn->query($query);
            
            if (!$result) {
                throw new Exception("Database query failed: " . $conn->error);
            }
            
            $sections = array();
            while ($row = $result->fetch_assoc()) {
                $sections[] = $row;
            }
            
            echo json_encode($sections);
        } catch (Exception $e) {
            error_log("Error in get_strand_sections: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    // Handler for getting section code for student login
    if ($action == "get_section_code") {
        $section_id = intval($_POST['section_id'] ?? 0);
        
        if($section_id > 0) {
            $query = $conn->query("SELECT section_code FROM sections WHERE id = $section_id LIMIT 1");
            if($query->num_rows > 0) {
                $row = $query->fetch_assoc();
                echo json_encode(['status' => 1, 'section_code' => $row['section_code']]);
                exit;
            }
        }
        echo json_encode(['status' => 0, 'message' => 'Section not found']);
        exit;
    }
    
    // New endpoint to search for sections
    if ($action == "search_sections") {
        $query = $conn->real_escape_string($_POST['query'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        $year_level = intval($_POST['year_level'] ?? 0);
        
        if(empty($query) || $department_id <= 0 || $year_level <= 0) {
            echo '<div class="alert alert-warning">Please provide a search query and select department and year level</div>';
            exit;
        }
        
        // Check if we're using 'section' or 'name' field in the database
        $check_column = $conn->query("SHOW COLUMNS FROM sections LIKE 'section'");
        $section_field = ($check_column->num_rows > 0) ? 'section' : 'name';
        
        // Log which field we're using
        error_log("Using section field: " . $section_field);
        
        $sql = "SELECT s.id, s.$section_field as section_name 
                FROM sections s 
                WHERE s.course_id = $department_id 
                AND s.year_level = $year_level 
                AND s.$section_field LIKE '%$query%'
                ORDER BY s.$section_field";
        
        error_log("Search query: " . $sql);
        
        $result = $conn->query($sql);
        
        if($result && $result->num_rows > 0) {
            echo '<div class="list-group">';
            while($row = $result->fetch_assoc()) {
                echo '<a href="javascript:void(0)" class="list-group-item list-group-item-action section-match" data-id="'.$row['id'].'">'.$row['section_name'].'</a>';
            }
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">No matching sections found</div>';
        }
        exit;
    }

    // Handler for updating student password
    if ($action == "update_student_password") {
        // Ensure user is logged in as a student
        if(!isset($_SESSION['login_id']) || $_SESSION['login_type'] !== 'Student') {
            echo json_encode(['status' => 0, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $user_id = $_SESSION['login_id'];
        $current_password = md5($_POST['current_password']);
        $new_password = isset($_POST['new_password']) && !empty($_POST['new_password']) ? 
                      md5($_POST['new_password']) : '';
        
        // Verify current password
        $check = $conn->query("SELECT * FROM users WHERE id = $user_id AND password = '$current_password'");
        if($check->num_rows == 0) {
            echo json_encode(['status' => 0, 'message' => 'Current password is incorrect']);
            exit;
        }
        
        // Only update if new password was provided
        if(!empty($new_password)) {
            $update = $conn->query("UPDATE users SET password = '$new_password' WHERE id = $user_id");
            if(!$update) {
                echo json_encode(['status' => 0, 'message' => 'Failed to update password: ' . $conn->error]);
                exit;
            }
        }
        
        echo json_encode(['status' => 1, 'message' => 'Password updated successfully']);
        exit;
    }
    if($action == "get_teacher_schedule"){
        $faculty_id = $_POST['faculty_id'];
        $events = array();

        try {
            // Query to get all schedules for this faculty
            $query = "SELECT s.*, 
                      sub.subject as subject_name,
                      c.course as course_name,
                      st.name as strand_name,
                      sec.name as section_name,
                      r.name as room_name
                      FROM schedules s 
                      LEFT JOIN subjects sub ON s.subject_id = sub.id
                      LEFT JOIN courses c ON s.course_id = c.id
                      LEFT JOIN strands st ON s.strand_id = st.id
                      LEFT JOIN sections sec ON s.section_id = sec.id
                      LEFT JOIN rooms r ON s.room_id = r.id
                      WHERE s.faculty_id = '$faculty_id'";

            $result = $conn->query($query);
            
            if(!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }

            while($row = $result->fetch_assoc()) {
                // Format schedule details
                $subject = $row['subject_name'] ?? 'Unknown Subject';
                $section = $row['section_name'] ?? 'Unknown Section';
                $room = $row['room_name'] ?? 'No Room';
                
                // Get course/strand name
                $department = $row['course_name'] ?? $row['strand_name'] ?? 'Unknown Department';
                
                // Build event title
                $title = "$subject - $section ($room)";
                
                // Build description
                $description = "Subject: $subject\nSection: $section\nRoom: $room\nDepartment: $department";
                
                if($row['is_repeating']) {
                    // Handle repeating events
                    $dow = explode(',', $row['dow']);
                    $start_date = date('Y-m-d', strtotime($row['month_from'] . '-01'));
                    $end_date = date('Y-m-t', strtotime($row['month_to'] . '-01'));
                    
                    $event = array(
                        'id' => $row['id'],
                        'title' => $title,
                        'description' => $description,
                        'start' => $start_date . 'T' . $row['time_from'],
                        'end' => $start_date . 'T' . $row['time_to'],
                        'startRecur' => $start_date,
                        'endRecur' => $end_date,
                        'daysOfWeek' => array_map('intval', $dow),
                        'className' => 'bg-primary text-white'
                    );
                } else {
                    // Handle single events
                    $event = array(
                        'id' => $row['id'],
                        'title' => $title,
                        'description' => $description,
                        'start' => date('Y-m-d', strtotime($row['month_from'])) . 'T' . $row['time_from'],
                        'end' => date('Y-m-d', strtotime($row['month_from'])) . 'T' . $row['time_to'],
                        'className' => 'bg-primary text-white'
                    );
                }
                
                $events[] = $event;
            }

            // Return JSON encoded events array with proper headers
            header('Content-Type: application/json');
            echo json_encode($events);
            
        } catch (Exception $e) {
            // Log the error and return error response
            error_log("Error in get_teacher_schedule: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(array('error' => 'Failed to load schedule: ' . $e->getMessage()));
        }
        exit;
    }

    // Add these handlers after other action handlers:
    if($action == 'get_departments') {
        $departments = $conn->query("SELECT * FROM courses ORDER BY course ASC");
        $options = "<option value=''>Select Department</option>";
        while($row = $departments->fetch_assoc()) {
            $options .= "<option value='".$row['id']."'>".$row['course']." - ".$row['description']."</option>";
        }
        echo $options;
        exit;
    }

    if($action == 'get_strands') {
        $strands = $conn->query("SELECT * FROM strands ORDER BY code ASC");
        $options = "<option value=''>Select Strand</option>";
        while($row = $strands->fetch_assoc()) {
            $options .= "<option value='".$row['id']."'>".$row['code']." - ".$row['name']."</option>";
        }
        echo $options;
        exit;
    }

    if($action == 'get_department_sections') {
        extract($_POST);
        $sections = $conn->query("SELECT * FROM sections 
                             WHERE course_id = '$department_id' 
                             AND year_level = '$year_level' 
                             ORDER BY name ASC");
        $options = "<option value=''>Select Section</option>";
        while($row = $sections->fetch_assoc()) {
            $options .= "<option value='".$row['id']."'>".$row['name']."</option>";
        }
        echo $options;
        exit;
    }

    if($action == 'get_strand_sections') {
        extract($_POST);
        $sections = $conn->query("SELECT * FROM sections 
                             WHERE strand_id = '$strand_id' 
                             AND year_level = '$grade_level' 
                             ORDER BY name ASC");
        $options = "<option value=''>Select Section</option>";
        while($row = $sections->fetch_assoc()) {
            $options .= "<option value='".$row['id']."'>".$row['name']."</option>";
        }
        echo $options;
        exit;
    }
    
    if($action == 'verify_section_code') {
        // Turn off error display for this endpoint
        ini_set('display_errors', '0');
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        
        // Set proper JSON header
        header('Content-Type: application/json');
        
        try {
            // Check if sessions are working - log session ID
            error_log("Session ID before verify: " . session_id());
            
            // Make sure we're sending cookies
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
            
            // Ensure no cache headers
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            
            // Sanitize inputs
            $section_id = intval($_POST['section_id'] ?? 0);
            $section_code = $_POST['section_code'] ?? '';
            $type = $_POST['type'] ?? '';
            
            // Log received parameters
            error_log("verify_section_code called with: section_id=$section_id, section_code=$section_code, type=$type");
            
            // Validate parameters
            if(!$section_id) {
                echo json_encode(['status' => 0, 'message' => 'Invalid section ID']);
                exit;
            }
            
            if(!$section_code) {
                echo json_encode(['status' => 0, 'message' => 'Section code is required']);
                exit;
            }
            
            if(!in_array($type, ['college', 'shs'])) {
                echo json_encode(['status' => 0, 'message' => 'Invalid student type']);
                exit;
            }
            
            // Check if section exists
            $query = $conn->prepare("SELECT * FROM sections WHERE id = ?");
            $query->bind_param("i", $section_id);
            $query->execute();
            $result = $query->get_result();
            
            if($result->num_rows == 0) {
                echo json_encode(['status' => 0, 'message' => 'Section not found']);
                exit;
            }
            
            $section = $result->fetch_assoc();
            
            // Check if section belongs to the right type (college or shs)
            if($type == 'college' && empty($section['course_id'])) {
                echo json_encode(['status' => 0, 'message' => 'Invalid section type. This is not a college section.']);
                exit;
            } else if($type == 'shs' && empty($section['strand_id'])) {
                echo json_encode(['status' => 0, 'message' => 'Invalid section type. This is not a SHS section.']);
                exit;
            }
            
            // Check if section_code field exists, if not, add it
            $check_column = $conn->query("SHOW COLUMNS FROM sections LIKE 'section_code'");
            if($check_column->num_rows == 0) {
                // Add section_code column if it doesn't exist
                $conn->query("ALTER TABLE sections ADD COLUMN section_code VARCHAR(50) NULL");
                
                // For testing, set a default code for each section (can be changed later in admin)
                $conn->query("UPDATE sections SET section_code = CONCAT('code', id)");
            }
            
            // Actual section code validation
            $valid = false;
            if(empty($section['section_code'])) {
                // If section has no code yet (temporary case for initial setup)
                // Use section ID as temporary password for testing
                $valid = ($section_code === 'code' . $section_id);
                error_log("Using temporary code: code{$section_id}");
            } else {
                // Normal case: validate against stored code
                $valid = ($section_code === $section['section_code']);
                error_log("Using stored code: {$section['section_code']}");
            }
            
            if($valid) {
                // Set login info in session
                $_SESSION['student_section_id'] = $section_id;
                $_SESSION['student_type'] = $type;
                
                if($type == 'college') {
                    $_SESSION['student_department_id'] = $section['course_id'];
                    $_SESSION['student_year_level'] = $section['year_level'];
                } else { // shs
                    $_SESSION['student_strand_id'] = $section['strand_id'];
                    $_SESSION['student_grade_level'] = $section['year_level'];
                }
                
                // Log session data for debugging
                error_log("Session after setting: " . print_r($_SESSION, true));
                error_log("Session ID after setting: " . session_id());
                
                // Write session data and close
                session_write_close();
                
                echo json_encode([
                    'status' => 1, 
                    'message' => 'Success', 
                    'section_id' => $section_id,
                    'redirect_url' => 'view_student_schedule.php'
                ]);
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid section code']);
            }
        } catch(Exception $e) {
            error_log("Error in verify_section_code: " . $e->getMessage());
            echo json_encode(['status' => 0, 'message' => 'Server error', 'error' => $e->getMessage()]);
        }
        exit;
    }

    if($action == "get_college_sections") {
        try {
            $dept_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
            $year_level = isset($_POST['year_level']) ? intval($_POST['year_level']) : 0;
            
            if(!$dept_id || !$year_level) {
                echo json_encode(['error' => 'Missing parameters']);
                exit;
            }
            
            $query = $conn->prepare("SELECT s.*, c.course as department_name 
                            FROM sections s 
                            LEFT JOIN courses c ON s.course_id = c.id 
                            WHERE s.course_id = ? AND s.year_level = ?
                            ORDER BY s.name ASC");
            $query->bind_param("ii", $dept_id, $year_level);
            $query->execute();
            $result = $query->get_result();
            
            $sections = array();
            while($row = $result->fetch_assoc()) {
                $sections[] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'department' => $row['department_name']
                );
            }
            
            echo json_encode(['status' => 1, 'data' => $sections]);
            
        } catch(Exception $e) {
            echo json_encode(['status' => 0, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    if($action == 'get_shs_sections') {
        try {
            $strand_id = isset($_POST['strand_id']) ? intval($_POST['strand_id']) : 0;
            $grade_level = isset($_POST['grade_level']) ? intval($_POST['grade_level']) : 0;
            
            if(!$strand_id || !$grade_level) {
                echo json_encode(['error' => 'Missing parameters']);
                exit;
            }
            
            $query = $conn->prepare("SELECT s.*, st.name as strand_name 
                            FROM sections s 
                            LEFT JOIN strands st ON s.strand_id = st.id 
                            WHERE s.strand_id = ? AND s.year_level = ?
                            ORDER BY s.name ASC");
            $query->bind_param("ii", $strand_id, $grade_level);
            $query->execute();
            $result = $query->get_result();
            
            $sections = array();
            while($row = $result->fetch_assoc()) {
                $sections[] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'strand' => $row['strand_name']
                );
            }
            
            echo json_encode(['status' => 1, 'data' => $sections]);
            
        } catch(Exception $e) {
            echo json_encode(['status' => 0, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    // Handler for student logout
    if($action == 'student_logout') {
        // Clear student session variables
        unset($_SESSION['student_section_id']);
        unset($_SESSION['student_type']);
        unset($_SESSION['student_department_id']);
        unset($_SESSION['student_year_level']);
        unset($_SESSION['student_strand_id']);
        unset($_SESSION['student_grade_level']);
        
        // For safety, regenerate session ID
        session_regenerate_id(true);
        
        echo json_encode(['status' => 1, 'message' => 'Logged out successfully']);
        exit;
    }

    // Handler for faculty ID verification
    if($action == 'verify_faculty_id') {
        // Set proper JSON header
        header('Content-Type: application/json');
        
        try {
            // Sanitize input
            $faculty_id = isset($_POST['faculty_id']) ? trim($_POST['faculty_id']) : '';
            
            if(empty($faculty_id)) {
                echo json_encode(['status' => 0, 'message' => 'Please enter your Employee ID']);
                exit;
            }
            
            // Query to check if faculty ID exists
            $query = $conn->prepare("SELECT id, id_no, firstname, lastname FROM faculty WHERE id_no = ?");
            $query->bind_param("s", $faculty_id);
            $query->execute();
            $result = $query->get_result();
            
            if($result->num_rows > 0) {
                $faculty = $result->fetch_assoc();
                
                // Set faculty info in session
                $_SESSION['faculty_id'] = $faculty['id'];
                $_SESSION['faculty_id_no'] = $faculty['id_no'];
                $_SESSION['faculty_name'] = $faculty['firstname'] . ' ' . $faculty['lastname'];
                
                echo json_encode([
                    'status' => 1,
                    'message' => 'Success',
                    'faculty_id' => $faculty['id'],
                    'redirect_url' => 'view_schedule.php?faculty_id=' . $faculty['id']
                ]);
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid Faculty ID. Please try again.']);
            }
        } catch(Exception $e) {
            error_log("Error in verify_faculty_id: " . $e->getMessage());
            echo json_encode(['status' => 0, 'message' => 'Server error', 'error' => $e->getMessage()]);
        }
        exit;
    }

} else {
    echo "Error: No action specified.";
}

ob_end_flush();
