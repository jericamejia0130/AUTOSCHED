<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);

Class Action {
    private $db;

    public function __construct() {
        ob_start();
        include 'db_connect.php';
        $this->db = $conn;
    }

    function __destruct() {
        $this->db->close();
        ob_end_flush();
    }
    

    function login(){
        extract($_POST);
        $qry = $this->db->query("SELECT * FROM users where username = '".$username."' and password = '".md5($password)."' ");
        if($qry->num_rows > 0){
            $user_data = $qry->fetch_array();
            foreach ($user_data as $key => $value) {
                if($key != 'password' && !is_numeric($key))
                    $_SESSION['login_'.$key] = $value;
            }
            $_SESSION['login_type'] = 'admin';
            
            // Ensure username is properly set in both variables for compatibility
            $_SESSION['login_name'] = $user_data['username'];
            $_SESSION['login_username'] = $user_data['username'];
            
            // Add timestamp for cache-busting profile images
            $_SESSION['profile_timestamp'] = time();
            
            error_log("User login successful: " . $username . ". Session variables set: " . json_encode($_SESSION));
            
            return 1;
        }else{
            return 2;
        }
    }

    function login_faculty(){
        // Faculty login is disabled - only admin accounts are available
        return 3; // Return error code
    }

    function login2(){
        // This login method is disabled - only admin accounts are available
        return 3; // Return error code
    }
    function logout(){
        session_destroy();
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        header("location:login.php");
    }
    function logout2(){
        session_destroy();
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        header("location:../index.php");
    }


    // filepath: c:\xampp\htdocs\schedulingold - Copy\admin\admin_class.php
    function save_user() {
        extract($_POST);
        $data = "";
        
        // Basic validation
        if (!isset($username)) {
            error_log("Save user failed: missing required field username");
            return 0;
        }

        // Sanitize inputs
        $username = $this->db->real_escape_string($username);
        
        $data = " username = '$username' ";
        $data .= ", type = 'Admin' "; // Always set type to Admin
        
        if(!empty($password)) {
            $password = md5($password);
            $data .= ", password = '$password' ";
        }

        // Handle profile image upload
        if(isset($_FILES['profile_image']) && $_FILES['profile_image']['tmp_name'] != '') {
            // Create uploads directory if it doesn't exist
            if (!file_exists('assets/uploads/')) {
                mkdir('assets/uploads/', 0777, true);
            }
            
            // Process the image upload
            $file_name = strtotime(date('y-m-d H:i')).'_'.$_FILES['profile_image']['name'];
            $target_path = 'assets/uploads/'.$file_name;
            
            // Log the upload attempt
            error_log("Attempting to upload profile image: " . $_FILES['profile_image']['name'] . " to " . $target_path);
            
            if(move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                $data .= ", profile_image = '$file_name' ";
                error_log("Profile image uploaded successfully");
            } else {
                error_log("Failed to upload profile image. Error: " . error_get_last()['message']);
            }
        }

        if(empty($id)) {
            // Check if username already exists
            $check = $this->db->query("SELECT * FROM users WHERE username = '$username'");
            if($check->num_rows > 0) {
                return 2; // Username already exists
            }
            $save = $this->db->query("INSERT INTO users SET $data");
        } else {
            // Check if username already exists for a different user
            $check = $this->db->query("SELECT * FROM users WHERE username = '$username' AND id != $id");
            if($check->num_rows > 0) {
                return 2; // Username already exists
            }
            
            error_log("Updating user ID: $id with data: $data");
            $save = $this->db->query("UPDATE users SET $data WHERE id = $id");
            
            // Get the updated user data to ensure all session variables are set correctly
            $updated_user = $this->db->query("SELECT * FROM users WHERE id = $id");
            if($updated_user->num_rows > 0) {
                $user_data = $updated_user->fetch_assoc();
                
                // If the user is updating their own account, update the session variables
                if(isset($_SESSION['login_id']) && $_SESSION['login_id'] == $id) {
                    error_log("Updating session for user $id - new username: $username");
                    
                    // Update all relevant session variables with new username
                    $_SESSION['login_username'] = $username;
                    $_SESSION['login_name'] = $username;
                    
                    // Also update these variables to ensure consistency across the application
                    if(isset($_SESSION['login_user'])) {
                        $_SESSION['login_user'] = $username;
                    }
                    
                    // If profile image was updated, update the session variable
                    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['tmp_name'] != '' && isset($file_name)) {
                        $_SESSION['login_profile_image'] = $file_name;
                    } else if(isset($user_data['profile_image'])) {
                        // Also update from database in case it changed
                        $_SESSION['login_profile_image'] = $user_data['profile_image'];
                    }
                    
                    // Add a timestamp to prevent browser caching of updated profile image
                    $_SESSION['profile_timestamp'] = time();
                    
                    // Log the session update
                    error_log("Session updated for user $id. New username: $username, Session vars: " . json_encode($_SESSION));
                }
            }
        }

        if($save) {
            error_log("User saved successfully. Username: " . $username . ", ID: " . ($id ?: $this->db->insert_id));
            return 1;
        }
        error_log("Failed to save user. SQL Error: " . $this->db->error);
        return 0;
    }
    function delete_user(){
        extract($_POST);
        $delete = $this->db->query("DELETE FROM users where id = ".$id);
        if($delete)
            return 1;
    }
    function signup(){
        extract($_POST);
        $data = " username = '$email' ";
        $data .= ", password = '".md5($password)."' ";
        $chk = $this->db->query("SELECT * FROM users where username = '$email' ")->num_rows;
        if($chk > 0){
            return 2;
            exit;
        }
            $save = $this->db->query("INSERT INTO users set ".$data);
        if($save){
            $uid = $this->db->insert_id;
            $data = '';
            foreach($_POST as $k => $v){
                if($k =='password')
                    continue;
                if(empty($data) && !is_numeric($k) )
                    $data = " $k = '$v' ";
                else
                    $data .= ", $k = '$v' ";
            }
            if($_FILES['img']['tmp_name'] != ''){
                            $fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
                            $move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
                            $data .= ", avatar = '$fname' ";

            }
            $save_alumni = $this->db->query("INSERT INTO alumnus_bio set $data ");
            if($data){
                $aid = $this->db->insert_id;
                $this->db->query("UPDATE users set alumnus_id = $aid where id = $uid ");
                $login = $this->login2();
                if($login)
                return 1;
            }
        }
    }
    function update_account(){
        extract($_POST);
        $data = " username = '$email' ";
        if(!empty($password))
        $data .= ", password = '".md5($password)."' ";
        $chk = $this->db->query("SELECT * FROM users where username = '$email' and id != '{$_SESSION['login_id']}' ")->num_rows;
        if($chk > 0){
            return 2;
            exit;
        }
            $save = $this->db->query("UPDATE users set $data where id = '{$_SESSION['login_id']}' ");
        if($save){
            $data = '';
            foreach($_POST as $k => $v){
                if($k =='password')
                    continue;
                if(empty($data) && !is_numeric($k) )
                    $data = " $k = '$v' ";
                else
                    $data .= ", $k = '$v' ";
            }
            if($_FILES['img']['tmp_name'] != ''){
                            $fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
                            $move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
                            $data .= ", avatar = '$fname' ";

            }
            $save_alumni = $this->db->query("UPDATE alumnus_bio set $data where id = '{$_SESSION['bio']['id']}' ");
            if($data){
                foreach ($_SESSION as $key => $value) {
                    unset($_SESSION[$key]);
                }
                $login = $this->login2();
                if($login)
                return 1;
            }
        }
    }

    function save_settings(){
        extract($_POST);
        $data = " name = '".str_replace("'","&#x2019;",$name)."' ";
        $data .= ", email = '$email' ";
        $data .= ", contact = '$contact' ";
        $data .= ", about_content = '".htmlentities(str_replace("'","&#x2019;",$about))."' ";
        if($_FILES['img']['tmp_name'] != ''){
                        $fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
                        $move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
                    $data .= ", cover_img = '$fname' ";

        }
        
        // echo "INSERT INTO system_settings set ".$data;
        $chk = $this->db->query("SELECT * FROM system_settings");
        if($chk->num_rows > 0){
            $save = $this->db->query("UPDATE system_settings set ".$data);
        }else{
            $save = $this->db->query("INSERT INTO system_settings set ".$data);
        }
        if($save){
        $query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
        foreach ($query as $key => $value) {
            if(!is_numeric($key))
                $_SESSION['settings'][$key] = $value;
        }

            return 1;
                }
    }

    
    function save_course() {
        extract($_POST);

        // Validate input
        if (empty($course) || empty($description)) {
            return json_encode(['status' => 0, 'message' => 'All fields are required.']);
        }

        $data = " course = '$course', description = '$description' ";

        if (empty($id)) {
            // Insert new department
            $save = $this->db->query("INSERT INTO courses SET $data");
        } else {
            // Update existing department
            $save = $this->db->query("UPDATE courses SET $data WHERE id = $id");
        }

        if ($save) {
            return json_encode(['status' => 1]); // Success
        } else {
            error_log("SQL Error: " . $this->db->error);
            return json_encode(['status' => 0, 'message' => $this->db->error]); // Failure
        }
    }
    function delete_course(){
        extract($_POST);
        $delete = $this->db->query("DELETE FROM courses where id = ".$id);
        if($delete){
            return 1;
        }
    }
    function save_subject() {
    extract($_POST);
    $data = " subject = '$subject', units = '$units' ";
    
    if (empty($id)) {
        $save = $this->db->query("INSERT INTO subjects SET $data");
    } else {
        $save = $this->db->query("UPDATE subjects SET $data WHERE id = $id");
    }

    if ($save) {
        return 1; // Success
    } else {
        error_log("SQL Error: " . $this->db->error);
        return 0; // Failure
    }
}
    function delete_subject(){
        extract($_POST);
        $delete = $this->db->query("DELETE FROM subjects where id = ".$id);
        if($delete){
            return 1;
        }
    }
    function save_faculty(){
        extract($_POST);
        
        // Check if required fields are set
        if(empty($lastname) || empty($firstname) || empty($designation_id)) {
            return json_encode(['status' => 0, 'message' => 'Please fill in all required fields']);
        }
        
        // Check for duplicate ID number if provided and not updating current record
        if(!empty($id_no)) {
            $check_duplicate = $this->db->query("SELECT id FROM faculty WHERE id_no = '$id_no' " . (isset($id) ? "AND id != $id" : ""));
            if($check_duplicate && $check_duplicate->num_rows > 0) {
                return 2; // ID No already exists
            }
        } else if(empty($id)) {
            // Auto-generate ID if not provided for new faculty
            $id_prefix = date('Ymd');
            $check_id = $this->db->query("SELECT id_no FROM faculty WHERE id_no LIKE '{$id_prefix}%' ORDER BY id_no DESC LIMIT 1");
            $last_id = $check_id->num_rows > 0 ? $check_id->fetch_array()['id_no'] : "{$id_prefix}0000";
            $id_no = intval(substr($last_id, strlen($id_prefix))) + 1;
            $id_no = "{$id_prefix}" . str_pad($id_no, 4, "0", STR_PAD_LEFT);
        }
        
        $data = " id_no = '$id_no', firstname = '$firstname', lastname = '$lastname', middlename = '$middlename', 
                  designation_id = '$designation_id' ";

        if (!empty($_FILES['image']['tmp_name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $file_type = mime_content_type($_FILES['image']['tmp_name']);

            if (in_array($file_type, $allowed_types)) {
                $image_name = strtotime(date('Y-m-d H:i:s')) . '_' . $_FILES['image']['name'];
                if (move_uploaded_file($_FILES['image']['tmp_name'], 'assets/uploads/' . $image_name)) {
                    $data .= ", image = '$image_name' ";
                } else {
                    return json_encode(['status' => 0, 'message' => 'Failed to upload image']);
                }
            } else {
                return json_encode(['status' => 0, 'message' => 'Unsupported file type. Please upload a JPG or PNG image']);
            }
        }

        if(empty($id)){
            $save = $this->db->query("INSERT INTO faculty set $data");
        }else{
            $save = $this->db->query("UPDATE faculty set $data where id = $id");
        }

        if($save){
            return 1; // Success
        } else {
            return json_encode(['status' => 0, 'message' => 'Database error: ' . $this->db->error]);
        }
    }
    function delete_faculty(){
        extract($_POST);
        
        // Check if faculty exists
        $check = $this->db->query("SELECT id FROM faculty WHERE id = $id");
        if($check->num_rows == 0) {
            return json_encode(['status' => 0, 'message' => 'Faculty not found']);
        }
        
        // Check if faculty is being used in schedules
        $check_schedules = $this->db->query("SELECT COUNT(*) as count FROM schedules WHERE faculty_id = $id")->fetch_assoc()['count'];
        if($check_schedules > 0) {
            return json_encode(['status' => 0, 'message' => 'Cannot delete faculty. They have assigned schedules.']);
        }
        
        // Delete the faculty image
        $faculty = $this->db->query("SELECT image FROM faculty WHERE id = $id")->fetch_assoc();
        if($faculty && !empty($faculty['image']) && file_exists('assets/uploads/'.$faculty['image'])) {
            @unlink('assets/uploads/'.$faculty['image']);
        }
        
        // Delete the faculty record
        $delete = $this->db->query("DELETE FROM faculty WHERE id = $id");
        if($delete){
            return 1; // Success
        } else {
            return json_encode(['status' => 0, 'message' => 'Database error: ' . $this->db->error]);
        }
    }
    function save_schedule() {
    extract($_POST);

    // Add an explicit response header to ensure JSON content type
    header('Content-Type: application/json');

    // Log request for debugging
    error_log("Schedule save data: " . print_r($_POST, true));

    // Ensure faculty_id is valid
    if (!isset($faculty_id) || empty($faculty_id)) {
        return json_encode(['status' => 'error', 'message' => 'Faculty is required']);
    }
    
    // Ensure section_id is valid
    if (!isset($section_id) || empty($section_id)) {
        return json_encode(['status' => 'error', 'message' => 'Section is required']);
    }
    
    // Ensure subject_id is valid
    if (!isset($subject_id) || empty($subject_id)) {
        return json_encode(['status' => 'error', 'message' => 'Subject is required']);
    }
    
    // Ensure room_id is valid
    if (!isset($room_id) || empty($room_id)) {
        return json_encode(['status' => 'error', 'message' => 'Room is required']);
    }
    
    // Ensure time_from and time_to are valid
    if (!isset($time_from) || empty($time_from) || !isset($time_to) || empty($time_to)) {
        return json_encode(['status' => 'error', 'message' => 'Time range is required']);
    }

    // Handle course_id and strand_id to avoid null values
    if (!isset($course_id) || $course_id === '') {
        $course_id = 'NULL';
    } else {
        $course_id = "'" . $this->db->real_escape_string($course_id) . "'";
    }
    
    if (!isset($strand_id) || $strand_id === '') {
        $strand_id = 'NULL';
    } else {
        $strand_id = "'" . $this->db->real_escape_string($strand_id) . "'";
    }

    // Validate and process the Days of Week (dow)
    if (isset($dow) && is_array($dow)) {
        $dow = implode(',', $dow); // Convert array to comma-separated string
    } else {
        $dow = ''; // Default to an empty string if not set
    }

    // Check for conflicts
    $exclude_condition = !empty($id) ? " AND id != '$id' " : "";
    
    // Instead of complex SQL, we'll do a simpler check and then verify each result
    $conflict_check = $this->db->query("SELECT * FROM schedules 
                                        WHERE faculty_id = '$faculty_id' 
                                          AND room_id = '$room_id' 
                                          $exclude_condition
                                          AND (
                                              ('$time_from' BETWEEN time_from AND time_to) 
                                              OR ('$time_to' BETWEEN time_from AND time_to)
                                              OR (time_from BETWEEN '$time_from' AND '$time_to')
                                              OR (time_to BETWEEN '$time_from' AND '$time_to')
                                          )
                                          AND ('$month_from' <= month_to AND '$month_to' >= month_from)
                                        ");

    // If we found potential conflicts based on time and room, check for dow conflicts
    $real_conflicts = [];
    
    if ($conflict_check && $conflict_check->num_rows > 0) {
        error_log("Potential time conflicts detected. Current ID: " . ($id ?? 'New Schedule'));
        error_log("Current schedule DOW: " . $dow);
        
        // Convert current dow to array for easier comparison
        $current_dow_array = explode(',', $dow);
        
        // Check each potential conflict to see if there's actually a day overlap
        while($row = $conflict_check->fetch_assoc()) {
            error_log("Checking potential conflict with ID: {$row['id']}, DOW: {$row['dow']}");
            
            // Skip if no dow overlap
            if(empty($row['dow'])) {
                continue;
            }
            
            $existing_dow_array = explode(',', $row['dow']);
            $has_day_overlap = false;
            
            // Check if any day in current schedule overlaps with any day in existing schedule
            foreach($current_dow_array as $current_day) {
                if(in_array($current_day, $existing_dow_array)) {
                    $has_day_overlap = true;
                    error_log("Day overlap found on day: $current_day");
                    break; 
                }
            }
            
            // If days overlap, this is a real conflict
            if($has_day_overlap) {
                $real_conflicts[] = "ID: {$row['id']}, Faculty: {$row['faculty_id']}, Room: {$row['room_id']}, " .
                              "Time: {$row['time_from']}-{$row['time_to']}, Days: {$row['dow']}";
            }
        }
        
        // If we found real conflicts (with dow overlap), return conflict status
        if(!empty($real_conflicts)) {
            error_log("Real conflicts found: " . implode("; ", $real_conflicts));
            return json_encode(['status' => 'conflict', 'message' => 'Schedule conflict detected with existing schedule.']);
        }
    }
    
    // No conflicts found!

    // Validate is_repeating to avoid null
    $is_repeating = isset($is_repeating) && $is_repeating ? 1 : 0;

    // Build the SQL data string with escaped values for security
    $faculty_id = $this->db->real_escape_string($faculty_id);
    $subject_id = $this->db->real_escape_string($subject_id);
    $section_id = $this->db->real_escape_string($section_id);
    $room_id = $this->db->real_escape_string($room_id);
    $time_from = $this->db->real_escape_string($time_from);
    $time_to = $this->db->real_escape_string($time_to);
    $dow = $this->db->real_escape_string($dow);
    $month_from = isset($month_from) ? $this->db->real_escape_string($month_from) : '';
    $month_to = isset($month_to) ? $this->db->real_escape_string($month_to) : '';

    $data = " faculty_id = '$faculty_id' ";
    $data .= ", subject_id = '$subject_id' ";
    $data .= ", course_id = $course_id "; // Note: No quotes as it might be NULL
    $data .= ", strand_id = $strand_id "; // Note: No quotes as it might be NULL
    $data .= ", section_id = '$section_id' ";
    $data .= ", room_id = '$room_id' ";
    $data .= ", time_from = '$time_from' ";
    $data .= ", time_to = '$time_to' ";
    $data .= ", is_repeating = '$is_repeating' ";
    $data .= ", dow = '$dow' "; // Save the processed Days of Week
    
    if ($is_repeating) {
        if (!empty($month_from)) {
            $data .= ", month_from = '$month_from' ";
        }
        if (!empty($month_to)) {
            $data .= ", month_to = '$month_to' ";
        }
    } else {
        $data .= ", month_from = NULL ";
        $data .= ", month_to = NULL ";
    }

    try {
        if (empty($id)) {
            $sql = "INSERT INTO schedules SET $data";
            $save = $this->db->query($sql);
        } else {
            $id = $this->db->real_escape_string($id);
            $sql = "UPDATE schedules SET $data WHERE id = $id";
            $save = $this->db->query($sql);
        }

        if ($save) {
            return json_encode(['status' => 'success']);
        } else {
            error_log("Schedule save error: " . $this->db->error . " in SQL: " . $sql);
            return json_encode(['status' => 'error', 'message' => $this->db->error]);
        }
    } catch (Exception $e) {
        error_log("Schedule save exception: " . $e->getMessage());
        return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
    function delete_schedule() {
    extract($_POST); // Ensure $id is extracted from the POST request
    if (!isset($id) || empty($id)) {
        echo json_encode(['status' => 0, 'message' => 'Missing schedule ID']);
        return;
    }
    
    try {
        // Begin transaction
        $this->db->begin_transaction();
        
        // Delete the schedule
        $delete = $this->db->query("DELETE FROM schedules WHERE id = " . intval($id));
        
        if ($delete) {
            // Commit transaction
            $this->db->commit();
            echo json_encode(['status' => 1, 'message' => 'Schedule deleted successfully']);
        } else {
            // Rollback transaction
            $this->db->rollback();
            echo json_encode(['status' => 0, 'message' => 'Failed to delete schedule: ' . $this->db->error]);
        }
    } catch (Exception $e) {
        // Rollback transaction if active
        try {
            $this->db->rollback();
        } catch (Exception $rollbackEx) {
            // Ignore rollback errors
        }
        echo json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
    function get_schedule() {
    extract($_POST);
    $data = array();
   
    $qry = $this->db->query("SELECT s.*, st.name as strand_name 
                             FROM schedules s 
                             LEFT JOIN strands st ON s.strand_id = st.id 
                             WHERE faculty_id = 0 OR faculty_id = $faculty_id");

    while ($row = $qry->fetch_assoc()) {
        if ($row['is_repeating'] == 1) {
            if (!empty($row['repeating_data']) && $rdata = json_decode($row['repeating_data'], true)) {
                $dow = isset($rdata['dow']) ? explode(',', $rdata['dow']) : [];
                $start_date = new DateTime($rdata['start']);
                $end_date = new DateTime($rdata['end']);
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start_date, $interval, $end_date->modify('+1 day'));

                foreach ($period as $date) {
                    if (in_array($date->format('w'), $dow)) {
                        $data[] = array(
                            'id' => $row['id'],
                            'title' => $row['title'],
                            'start' => $date->format('Y-m-d') . 'T' . $row['time_from'],
                            'end' => $date->format('Y-m-d') . 'T' . $row['time_to'],
                            'description' => $row['description'],
                            'location' => $row['location'],
                            'room' => $row['room_name'] . ' (' . $row['room_type'] . ')',
                            'strand' => $row['strand_name']
                        );
                    }
                }
            }
        } else {
            $data[] = array(
                'id' => $row['id'],
                'title' => $row['title'],
                'start' => $row['schedule_date'] . 'T' . $row['time_from'],
                'end' => $row['schedule_date'] . 'T' . $row['time_to'],
                'description' => $row['description'],
                'location' => $row['location'],
                'room' => $row['room_name'] . ' (' . $row['room_type'] . ')',
                'strand' => $row['strand_name']
            );
        }
    }

    return json_encode($data);
}
    function get_teacher_schedule() {
        try {
            if (!isset($_POST['faculty_id']) || empty($_POST['faculty_id'])) {
                return json_encode(['error' => 'No faculty ID provided']);
            }
            
            $faculty_id = intval($_POST['faculty_id']);
            $data = array();
            
            // Build a better query with proper error handling
            $qry = $this->db->query("SELECT s.*, 
                                    CONCAT(f.firstname, ' ', f.lastname) as faculty_name,
                                    sub.subject as subject_name,
                                    r.name as room_name,
                                    r.type as room_type,
                                    sec.name as section_name 
                                FROM schedules s 
                                LEFT JOIN faculty f ON f.id = s.faculty_id 
                                LEFT JOIN subjects sub ON sub.id = s.subject_id
                                LEFT JOIN rooms r ON r.id = s.room_id
                                LEFT JOIN sections sec ON sec.id = s.section_id
                                WHERE s.faculty_id = $faculty_id");

            if (!$qry) {
                error_log("Schedule query error: " . $this->db->error);
                return json_encode(['error' => 'Database query failed: ' . $this->db->error]);
            }

            if ($qry->num_rows === 0) {
                // No schedules found but not an error
                return json_encode([]);
            }

            while ($row = $qry->fetch_assoc()) {
                if ($row['is_repeating'] == 1) {
                    // For repeating schedules
                    if (!empty($row['dow'])) {
                        $dow = explode(',', $row['dow']);
                        $start_date = $row['month_from'] . '-01';
                        $end_date = date('Y-m-t', strtotime($row['month_to'] . '-01'));
                        
                        foreach ($dow as $day) {
                            $data[] = array(
                                'id' => $row['id'],
                                'title' => ($row['subject_name'] ?? 'No Subject') . ' (' . ($row['section_name'] ?? 'No Section') . ')',
                                'daysOfWeek' => [intval($day)],
                                'startTime' => $row['time_from'],
                                'endTime' => $row['time_to'],
                                'startRecur' => $start_date,
                                'endRecur' => $end_date,
                                'extendedProps' => array(
                                    'room' => ($row['room_name'] ? $row['room_name'] . ' (' . $row['room_type'] . ')' : 'No Room'),
                                    'faculty' => $row['faculty_name'] ?? 'Unknown',
                                    'section' => $row['section_name'] ?? 'No Section',
                                    'description' => 'Subject: ' . ($row['subject_name'] ?? 'No Subject') . 
                                                     '<br>Room: ' . ($row['room_name'] ?? 'No Room') . 
                                                     '<br>Section: ' . ($row['section_name'] ?? 'No Section')
                                )
                            );
                        }
                    }
                } else {
                    // For non-repeating schedules (single date)
                    if (!empty($row['schedule_date'])) {
                        $data[] = array(
                            'id' => $row['id'],
                            'title' => ($row['subject_name'] ?? 'No Subject') . ' (' . ($row['section_name'] ?? 'No Section') . ')',
                            'start' => $row['schedule_date'] . 'T' . $row['time_from'],
                            'end' => $row['schedule_date'] . 'T' . $row['time_to'],
                            'extendedProps' => array(
                                'room' => ($row['room_name'] ? $row['room_name'] . ' (' . $row['room_type'] . ')' : 'No Room'),
                                'faculty' => $row['faculty_name'] ?? 'Unknown',
                                'section' => $row['section_name'] ?? 'No Section',
                                'description' => 'Subject: ' . ($row['subject_name'] ?? 'No Subject') . 
                                                 '<br>Room: ' . ($row['room_name'] ?? 'No Room') . 
                                                 '<br>Section: ' . ($row['section_name'] ?? 'No Section')
                            )
                        );
                    }
                }
            }
            
            return json_encode($data);
        } catch (Exception $e) {
            error_log("Schedule error: " . $e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }
    function delete_forum(){
        extract($_POST);
        $delete = $this->db->query("DELETE FROM forum_topics where id = ".$id);
        if($delete){
            return 1;
        }
    }
    function save_comment(){
        extract($_POST);
        $data = " comment = '".htmlentities(str_replace("'","&#x2019;",$comment))."' ";

        if(empty($id)){
            $data .= ", topic_id = '$topic_id' ";
            $data .= ", user_id = '{$_SESSION['login_id']}' ";
            $save = $this->db->query("INSERT INTO forum_comments set ".$data);
        }else{
            $save = $this->db->query("UPDATE forum_comments set ".$data." where id=".$id);
        }
        if($save)
            return 1;
    }
    function delete_comment(){
        extract($_POST);
        $delete = $this->db->query("DELETE FROM forum_comments where id = ".$id);
        if($delete){
            return 1;
        }
    }
    function save_event(){
        extract($_POST);
        $data = " title = '$title' ";
        $data .= ", schedule = '$schedule' ";
        $data .= ", content = '".htmlentities(str_replace("'","&#x2019;",$content))."' ";
        if($_FILES['banner']['tmp_name'] != ''){
                        $_FILES['banner']['name'] = str_replace(array("(",")"," "), '', $_FILES['banner']['name']);
                        $fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['banner']['name'];
                        $move = move_uploaded_file($_FILES['banner']['tmp_name'],'assets/uploads/'. $fname);
                    $data .= ", banner = '$fname' ";

        }
        if(empty($id)){

            $save = $this->db->query("INSERT INTO events set ".$data);
        }else{
            $save = $this->db->query("UPDATE events set ".$data." where id=".$id);
        }
        if($save)
            return 1;
    }
    function delete_event(){
        extract($_POST);
        $delete = $this->db->query("DELETE FROM events where id = ".$id);
        if($delete){
            return 1;
        }
    }
    
    function participate(){
        extract($_POST);
        $data = " event_id = '$event_id' ";
        $data .= ", user_id = '{$_SESSION['login_id']}' ";
        $commit = $this->db->query("INSERT INTO event_commits set $data ");
        if($commit)
            return 1;

    }
    function save_room() {
    extract($_POST);

    $data = " name = '$name', type = '$type', capacity = '$capacity' ";
    
    if (empty($id)) {
        // Insert new room
        $save = $this->db->query("INSERT INTO rooms SET $data");
    } else {
        // Update existing room
        $save = $this->db->query("UPDATE rooms SET $data WHERE id = $id");
    }

    if ($save) {
        return json_encode(['status' => 1, 'message' => 'Room saved successfully!']);
    } else {
        error_log("SQL Error: " . $this->db->error);
        return json_encode(['status' => 0, 'message' => 'Failed to save room.']);
    }
}
function get_room() {
    extract($_POST);
    $qry = $this->db->query("SELECT * FROM rooms WHERE id = $id");
    if ($qry->num_rows > 0) {
        return json_encode($qry->fetch_assoc());
    } else {
        return 0; // Room not found
    }
}
function delete_room() {
    extract($_POST);
    $delete = $this->db->query("DELETE FROM rooms WHERE id = $id");
    if ($delete) {
        return 1; // Success
    } else {
        error_log("SQL Error: " . $this->db->error);
        return 0; // Failure
    }
}
function save_designation() {
    extract($_POST);
    $data = " designation = '$designation' ";
    if (empty($id)) {
        $save = $this->db->query("INSERT INTO designations SET $data");
    } else {
        $save = $this->db->query("UPDATE designations SET $data WHERE id = $id");
    }
    if ($save) {
        return 1;
    } else {
        error_log("SQL Error: " . $this->db->error);
        return 0;
    }
}

function delete_designation() {
    extract($_POST);
    $delete = $this->db->query("DELETE FROM designations WHERE id = $id");
    if ($delete) {
        return 1;
    } else {
        error_log("SQL Error: " . $this->db->error);
        return 0;
    }
}
function save_strand() {
    extract($_POST);
    
    try {
        // Validate required fields
        if(empty($code) || empty($name)) {
            return json_encode(['status' => 0, 'message' => 'Strand code and name are required']);
        }
        
        // Check for duplicate code
        if(empty($id)) {
            $check = $this->db->query("SELECT id FROM strands WHERE code = '$code' OR name = '$name'");
            if($check && $check->num_rows > 0) {
                return json_encode(['status' => 0, 'message' => 'Strand code or name already exists']);
            }
            
            // Insert new strand
            $data = "code = '$code', name = '$name'";
            $save = $this->db->query("INSERT INTO strands SET $data");
            $new_id = $this->db->insert_id;
        } else {
            // Check for duplicate code excluding current strand
            $check = $this->db->query("SELECT id FROM strands WHERE (code = '$code' OR name = '$name') AND id != $id");
            if($check && $check->num_rows > 0) {
                return json_encode(['status' => 0, 'message' => 'Strand code or name already exists']);
            }
            
            // Update existing strand
            $data = "code = '$code', name = '$name'";
            $save = $this->db->query("UPDATE strands SET $data WHERE id = $id");
            $new_id = $id;
        }
        
        if(!$save) {
            error_log("SQL Error: " . $this->db->error);
            return json_encode(['status' => 0, 'message' => 'Database error: ' . $this->db->error]);
        }
        
        return json_encode(['status' => 1, 'message' => 'Strand saved successfully', 'id' => $new_id]);
    } catch(Exception $e) {
        error_log("Strand save error: " . $e->getMessage());
        return json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function delete_strand() {
    extract($_POST);
    
    try {
        // Check if strand exists
        $check = $this->db->query("SELECT id FROM strands WHERE id = $id");
        if(!$check || $check->num_rows == 0) {
            return json_encode(['status' => 0, 'message' => 'Strand not found']);
        }
        
        // Check for dependencies in subject_strands
        $check_subjects = $this->db->query("SELECT COUNT(*) as count FROM subject_strands WHERE strand_id = $id")->fetch_assoc()['count'];
        if($check_subjects > 0) {
            return json_encode(['status' => 0, 'message' => 'Cannot delete strand. It is being used in subjects.']);
        }
        
        // Check for dependencies in sections
        $check_sections = $this->db->query("SELECT COUNT(*) as count FROM sections WHERE strand_id = $id")->fetch_assoc()['count'];
        if($check_sections > 0) {
            return json_encode(['status' => 0, 'message' => 'Cannot delete strand. It is being used in sections.']);
        }
        
        // Check for dependencies in schedules
        $check_schedules = $this->db->query("SELECT COUNT(*) as count FROM schedules WHERE strand_id = $id")->fetch_assoc()['count'];
        if($check_schedules > 0) {
            return json_encode(['status' => 0, 'message' => 'Cannot delete strand. It is being used in schedules.']);
        }
        
        // Delete the strand
        $delete = $this->db->query("DELETE FROM strands WHERE id = $id");
        
        if($delete) {
            return 1; // Success - using 1 to maintain compatibility
        } else {
            return json_encode(['status' => 0, 'message' => 'Failed to delete strand: ' . $this->db->error]);
        }
    } catch(Exception $e) {
        error_log("Strand delete error: " . $e->getMessage());
        return json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function get_student_departments() {
    $query = "SELECT c.*, 
              (SELECT COUNT(s.id) FROM sections s WHERE s.course_id = c.id) as section_count
              FROM courses c 
              ORDER BY c.course ASC";
              
    $result = $this->db->query($query);
    if(!$result) {
        error_log("SQL Error: " . $this->db->error);
        return json_encode(['error' => 'Database query failed']);
    }

    $data = array();
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return json_encode($data);
}

function get_student_strands() {
    $data = array();
    $query = "SELECT st.*, 
              (SELECT COUNT(s.id) FROM sections s WHERE s.strand_id = st.id) as section_count
              FROM strands st 
              ORDER BY st.name ASC";
              
    $result = $this->db->query($query);
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return json_encode($data);
}

function save_section() {
    // Start output buffering to catch any PHP errors or unwanted output
    ob_start();
    
    try {
        // Create a log file for debugging
        $log_file = fopen("section_debug.log", "a");
        fwrite($log_file, date("Y-m-d H:i:s") . " - save_section called\n");
        fwrite($log_file, "POST data: " . json_encode($_POST) . "\n");
        
        // Generate a unique request ID to track duplicate calls
        $request_id = uniqid('section_');
        fwrite($log_file, "Request ID: " . $request_id . "\n");
        
        extract($_POST);
        
        // Debug: Log received parameters
        error_log("save_section called with: " . json_encode($_POST));
        
        $data = [];
        
        // Validate required fields
        if (empty($name)) {
            fwrite($log_file, "Error: Section name is required\n");
            fclose($log_file);
            return json_encode(['status' => 0, 'msg' => 'Section name is required']);
        }
        
        if (empty($year_level)) {
            fwrite($log_file, "Error: Year level is required\n");
            fclose($log_file);
            return json_encode(['status' => 0, 'msg' => 'Year level is required']);
        }
        
        // Ensure we have either course_id or strand_id
        if (empty($course_id) && empty($strand_id)) {
            fwrite($log_file, "Error: Either Department or Strand must be selected\n");
            fclose($log_file);
            return json_encode(['status' => 0, 'msg' => 'Either Department or Strand must be selected']);
        }
        
        // Sanitize input
        $name = $this->db->real_escape_string($name);
        $year_level = intval($year_level);
        
        // Better sanitization for IDs
        if (isset($id) && $id !== '') {
            $id = intval($id);
            fwrite($log_file, "Editing existing section with ID: " . $id . "\n");
        } else {
            $id = null;
            fwrite($log_file, "Creating new section (no ID)\n");
        }
        
        if (!empty($course_id)) {
            $course_id = intval($course_id);
        } else {
            $course_id = null;
        }
        
        if (!empty($strand_id)) {
            $strand_id = intval($strand_id);
        } else {
            $strand_id = null;
        }
        
        // Only check for exact duplicates (same name, same year_level, same department/strand)
        $check_query = "";
        if (!empty($course_id)) {
            $check_query = "SELECT * FROM sections WHERE name = '$name' AND year_level = '$year_level' AND course_id = '$course_id'";
            if ($id !== null) {
                $check_query .= " AND id != $id";
            }
        } else if (!empty($strand_id)) {
            $check_query = "SELECT * FROM sections WHERE name = '$name' AND year_level = '$year_level' AND strand_id = '$strand_id'";
            if ($id !== null) {
                $check_query .= " AND id != $id";
            }
        }
        
        // Log the duplicate check query
        fwrite($log_file, "Duplicate check query: " . $check_query . "\n");
        
        // Use a transaction to prevent race conditions
        $this->db->begin_transaction();
        
        $check = $this->db->query($check_query);
        if (!$check) {
            $this->db->rollback();
            fwrite($log_file, "Error in duplicate check query: " . $this->db->error . "\n");
            fclose($log_file);
            return json_encode(['status' => 0, 'msg' => 'Database error during duplicate check: ' . $this->db->error]);
        }
        
        // Check the results of the duplicate query and log the count
        fwrite($log_file, "Duplicate check found " . $check->num_rows . " rows\n");
        
        if($check && $check->num_rows > 0) {
            $this->db->rollback();
            // Log each duplicate found
            while ($row = $check->fetch_assoc()) {
                fwrite($log_file, "Duplicate found - ID: " . $row['id'] . ", Name: " . $row['name'] . 
                                  ", Year: " . $row['year_level'] . 
                                  ", Course ID: " . ($row['course_id'] ?? 'NULL') . 
                                  ", Strand ID: " . ($row['strand_id'] ?? 'NULL') . "\n");
            }
            
            fwrite($log_file, "Error: Section name already exists for this level/department or strand\n");
            fclose($log_file);
            return json_encode(['status' => 0, 'msg' => 'Section name already exists for this level/department or strand']);
        }
        
        // Prepare data array for insert/update
        $data['name'] = $name;
        $data['year_level'] = $year_level;
        
        // For college sections
        if (!empty($course_id)) {
            $data['course_id'] = $course_id;
            $data['strand_id'] = NULL; // Use PHP NULL for proper handling
        } 
        // For SHS sections
        else if (!empty($strand_id)) {
            $data['strand_id'] = $strand_id;
            $data['course_id'] = NULL; // Use PHP NULL for proper handling
        }
        
        // Build SQL query with appropriate NULL handling
        $fields = [];
        foreach($data as $field => $value) {
            if ($value === NULL) {
                $fields[] = "`$field` = NULL";
            } else {
                $fields[] = "`$field` = '" . $this->db->real_escape_string($value) . "'";
            }
        }
        
        $fields_str = implode(", ", $fields);
        
        // Insert or Update
        if($id === null) {
            $sql = "INSERT INTO sections SET $fields_str";
        } else {
            $sql = "UPDATE sections SET $fields_str WHERE id = $id";
        }
        
        error_log("SQL Query: " . $sql);
        fwrite($log_file, "SQL Query: " . $sql . "\n");
        
        // Execute the query
        $save = $this->db->query($sql);
        
        if($save) {
            // Commit transaction
            $this->db->commit();
            
            fwrite($log_file, "Success: Section saved\n");
            $result = json_encode(['status' => 1, 'msg' => 'Section saved successfully']);
            fwrite($log_file, "Response: " . $result . "\n");
            fclose($log_file);
            
            // Clear any output buffer before returning the JSON
            ob_end_clean();
            return $result;
        } else {
            // Rollback transaction
            $this->db->rollback();
            
            error_log("SQL Error: " . $this->db->error);
            fwrite($log_file, "Error: " . $this->db->error . "\n");
            $result = json_encode(['status' => 0, 'msg' => 'Database error: ' . $this->db->error]);
            fwrite($log_file, "Response: " . $result . "\n");
            fclose($log_file);
            
            // Clear any output buffer before returning the JSON
            ob_end_clean();
            return $result;
        }
    } catch (Exception $e) {
        // Rollback transaction if active
        try {
            if ($this->db->ping()) {
                $this->db->rollback();
            }
        } catch (Exception $ex) {
            // Ignore rollback errors
        }
        
        error_log("Exception in save_section: " . $e->getMessage());
        if(isset($log_file)) {
            fwrite($log_file, "Exception: " . $e->getMessage() . "\n");
            fclose($log_file);
        }
        
        // Clear any output buffer before returning the JSON
        ob_end_clean();
        return json_encode(['status' => 0, 'msg' => 'Exception: ' . $e->getMessage()]);
    }
    
    // Catch any unexpected execution paths
    if(isset($log_file)) {
        fwrite($log_file, "Warning: Unexpected function exit\n");
        fclose($log_file);
    }
    
    // Clear any output buffer before returning the JSON
    ob_end_clean();
    return json_encode(['status' => 0, 'msg' => 'Unexpected error occurred']);
}

function delete_section() {
    extract($_POST);
    
    try {
        // Check if section exists
        $check = $this->db->query("SELECT * FROM sections WHERE id = '$id'");
        if($check->num_rows == 0) {
            return json_encode(['status' => 0, 'message' => 'Section not found']);
        }
        
        // Check if section is being used in schedules
        $check_schedules = $this->db->query("SELECT COUNT(*) as count FROM schedules WHERE section_id = '$id'")->fetch_assoc()['count'];
        if($check_schedules > 0) {
            return json_encode(['status' => 0, 'message' => 'Cannot delete section. It is being used in schedules.']);
        }
        
        $delete = $this->db->query("DELETE FROM sections WHERE id = '$id'");
        
        if($delete) {
            return 1;
        } else {
            return json_encode(['status' => 0, 'message' => 'Failed to delete section: ' . $this->db->error]);
        }
    } catch (Exception $e) {
        return json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

} // End of Class Action