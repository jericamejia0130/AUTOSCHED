<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);

Class StudentAction {
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

    function login() {
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            return json_encode(['status' => 0, 'message' => 'Username and password are required']);
        }

        $username = $this->db->real_escape_string($_POST['username']);
        $password = md5($_POST['password']);

        $qry = $this->db->query("SELECT u.*, s.course_id, s.section_id, c.course, sec.name as section 
                                FROM users u 
                                LEFT JOIN students s ON u.id = s.user_id 
                                LEFT JOIN courses c ON s.course_id = c.id 
                                LEFT JOIN sections sec ON s.section_id = sec.id 
                                WHERE u.username = '$username' 
                                AND u.password = '$password' 
                                AND u.type = 'Student' 
                                LIMIT 1");
        
        if (!$qry) {
            error_log("Login query failed: " . $this->db->error);
            return json_encode(['status' => 0, 'message' => 'Database error occurred']);
        }

        if ($qry->num_rows > 0) {
            $row = $qry->fetch_assoc();
            foreach ($row as $key => $value) {
                if (!is_numeric($key)) {
                    $_SESSION['login_' . $key] = $value;
                }
            }
            return json_encode(['status' => 1, 'redirect' => 'student_page.php']);
        }

        return json_encode(['status' => 0, 'message' => 'Invalid username or password']);
    }

    function logout() {
        session_destroy();
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        header("location:login.php");
    }

    function get_student_schedule() {
        if (!isset($_SESSION['login_id'])) {
            return json_encode(['error' => 'Not logged in']);
        }

        $student_id = $_SESSION['login_id'];
        $data = array();
        
        $qry = $this->db->query("SELECT s.*, 
                                sub.subject as subject_name,
                                f.firstname as faculty_firstname,
                                f.lastname as faculty_lastname,
                                r.name as room_name,
                                r.type as room_type
                                FROM schedules s 
                                LEFT JOIN subjects sub ON s.subject_id = sub.id
                                LEFT JOIN faculty f ON s.faculty_id = f.id
                                LEFT JOIN rooms r ON s.room_id = r.id
                                WHERE s.course_id = {$_SESSION['login_course_id']}
                                AND s.section_id = {$_SESSION['login_section_id']}");

        while ($row = $qry->fetch_assoc()) {
            if ($row['is_repeating'] == 1) {
                $dow = explode(',', $row['dow']);
                $start_date = $row['month_from'] . '-01';
                $end_date = date('Y-m-t', strtotime($row['month_to']));
                
                foreach ($dow as $day) {
                    $data[] = array(
                        'id' => $row['id'],
                        'title' => $row['subject_name'],
                        'daysOfWeek' => [intval($day)],
                        'startTime' => $row['time_from'],
                        'endTime' => $row['time_to'],
                        'startRecur' => $start_date,
                        'endRecur' => $end_date,
                        'extendedProps' => array(
                            'faculty' => $row['faculty_firstname'] . ' ' . $row['faculty_lastname'],
                            'room' => $row['room_name'] . ' (' . $row['room_type'] . ')'
                        )
                    );
                }
            }
        }
        
        return json_encode($data);
    }

    function get_student_info() {
        if (!isset($_SESSION['login_id'])) {
            return json_encode(['error' => 'Not logged in']);
        }

        $student_id = $_SESSION['login_id'];
        $qry = $this->db->query("SELECT s.*, c.course, sec.name as section 
                                FROM students s 
                                LEFT JOIN courses c ON s.course_id = c.id 
                                LEFT JOIN sections sec ON s.section_id = sec.id 
                                WHERE s.user_id = $student_id");

        if ($qry->num_rows > 0) {
            return json_encode($qry->fetch_assoc());
        }
        return json_encode(['error' => 'Student information not found']);
    }
}
?>