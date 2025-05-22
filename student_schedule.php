<?php
if(!isset($_SESSION['login_type']) || $_SESSION['login_type'] != 'Student'){
    header('location:login.php');
    exit;
}

include 'db_connect.php';

$section_id = $_SESSION['login_section'];
$schedule_query = $conn->query("SELECT s.*, 
    CONCAT(f.lastname, ', ', f.firstname) as faculty_name,
    sub.subject as subject_name,
    r.name as room_name,
    CASE 
        WHEN c.course IS NOT NULL THEN c.course
        WHEN st.name IS NOT NULL THEN st.name 
    END as department_strand
    FROM schedules s 
    LEFT JOIN faculty f ON f.id = s.faculty_id
    LEFT JOIN subjects sub ON sub.id = s.subject_id
    LEFT JOIN rooms r ON r.id = s.room_id
    LEFT JOIN courses c ON c.id = s.course_id
    LEFT JOIN strands st ON st.id = s.strand_id
    WHERE s.section_id = '$section_id'
    ORDER BY s.time_from ASC");
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">My Class Schedule</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Days</th>
                            <th>Time</th>
                            <th>Room</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($schedule_query->num_rows > 0): ?>
                            <?php while($row = $schedule_query->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['subject_name'] ?></td>
                                    <td><?php echo $row['faculty_name'] ?></td>
                                    <td><?php 
                                        $days = explode(',', $row['dow']);
                                        $day_names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                        $display_days = array_map(function($d) use ($day_names) {
                                            return $day_names[$d];
                                        }, $days);
                                        echo implode(', ', $display_days);
                                    ?></td>
                                    <td><?php echo date('h:ia', strtotime($row['time_from'])).' - '.date('h:ia', strtotime($row['time_to'])) ?></td>
                                    <td><?php echo $row['room_name'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No schedule available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
