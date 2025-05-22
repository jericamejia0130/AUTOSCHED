<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include FPDF library
require('fpdf.php');
include 'db_connect.php';

// Check if ID is provided
if (!isset($_GET['id']) && !isset($_GET['faculty_id']) && !isset($_GET['all'])) {
    die('No schedule specified');
}

class PDF extends FPDF {
    // Page header
    function Header() {
        // Logo - Uncomment if you have a logo
        // $this->Image('logo.png',10,6,30);
        
        // Title
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Schedule Details', 0, 1, 'C');
        $this->Ln(5);
    }
    
    // Page footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->PageNo().'/{nb}', 0, 0, 'C');
        $this->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 0, 'R');
    }
    
    // Function to create a multi-line cell with specified width
    function MultiLineCell($w, $h, $txt, $border=0, $align='J', $fill=false) {
        // Calculate height of wrapped text
        $cw = $this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                continue;
            }
            if($c==' ') {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += isset($cw[ord($c)]) ? $cw[ord($c)] : 0;
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j)
                        $i++;
                }
                else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
            }
            else
                $i++;
        }
        
        // Create cell with calculated height
        $this->Cell($w, $h*$nl, $txt, $border, 0, $align, $fill);
    }
}

// Create PDF object
try {
    $pdf = new PDF('L'); // Use Landscape orientation
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // Single schedule print
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Get the schedule details
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
                         WHERE s.id = " . $id);
                         
        if ($qry->num_rows > 0) {
            $row = $qry->fetch_assoc();
            
            // Format days of week for display
            $days_display = '';
            if(isset($row['dow']) && !empty($row['dow'])) {
                $days_arr = explode(',', $row['dow']);
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
            if(isset($row['year_level']) && !empty($row['year_level'])) {
                if(isset($row['strand_name']) && !empty($row['strand_name'])) {
                    // For SHS
                    $year_level_display = "Grade " . $row['year_level'];
                } else if(isset($row['course_name']) && !empty($row['course_name'])) {
                    // For College
                    $suffix = '';
                    if($row['year_level'] == 1) $suffix = 'st';
                    else if($row['year_level'] == 2) $suffix = 'nd';
                    else if($row['year_level'] == 3) $suffix = 'rd';
                    else $suffix = 'th';
                    
                    $year_level_display = $row['year_level'] . $suffix . " Year";
                }
            }
            
            // Add schedule details to PDF
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 10, 'Faculty:', 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, isset($row['faculty_name']) ? ucwords($row['faculty_name']) : 'N/A', 0, 1);
            
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 10, 'Subject:', 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, isset($row['subject_name']) ? $row['subject_name'] : 'N/A', 0, 1);
            
            if(isset($row['course_name']) && !empty($row['course_name'])) {
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(50, 10, 'Department:', 0, 0);
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 10, $row['course_name'], 0, 1);
            }
            
            if(isset($row['strand_name']) && !empty($row['strand_name'])) {
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(50, 10, 'Strand:', 0, 0);
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 10, $row['strand_name'], 0, 1);
            }
            
            if(!empty($year_level_display)) {
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(50, 10, 'Level:', 0, 0);
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 10, $year_level_display, 0, 1);
            }
            
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 10, 'Section:', 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, isset($row['section_name']) ? $row['section_name'] : 'N/A', 0, 1);
            
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 10, 'Room/Laboratory:', 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, isset($row['location']) ? $row['location'] : 'N/A', 0, 1);
            
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 10, 'Days of Week:', 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, $days_display, 0, 1);
            
            if(isset($row['month_from']) && !empty($row['month_from'])) {
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(50, 10, 'Month From:', 0, 0);
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 10, date('F Y', strtotime($row['month_from'])), 0, 1);
            }
            
            if(isset($row['month_to']) && !empty($row['month_to'])) {
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(50, 10, 'Month To:', 0, 0);
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 10, date('F Y', strtotime($row['month_to'])), 0, 1);
            }
            
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 10, 'Time:', 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $time_from = isset($row['time_from']) ? date('h:i A', strtotime($row['time_from'])) : 'N/A';
            $time_to = isset($row['time_to']) ? date('h:i A', strtotime($row['time_to'])) : 'N/A';
            $pdf->Cell(0, 10, $time_from . ' - ' . $time_to, 0, 1);
        } else {
            $pdf->Cell(0, 10, 'Schedule not found.', 0, 1);
        }
    }
    // Faculty schedule print
    else if (isset($_GET['faculty_id'])) {
        $faculty_id = $_GET['faculty_id'];
        
        // Get faculty details
        $faculty_qry = $conn->query("SELECT *, CONCAT(lastname, ', ', firstname, ' ', COALESCE(middlename, '')) AS name FROM faculty WHERE id = " . $faculty_id);
        if ($faculty_qry->num_rows > 0) {
            $faculty = $faculty_qry->fetch_assoc();
            
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Faculty: ' . $faculty['name'], 0, 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Schedule Listing', 0, 1);
            $pdf->Ln(5);
            
            // Get all schedules for this faculty
            $schedules = $conn->query("SELECT s.*, 
                                    c.course AS course_name, 
                                    st.name AS strand_name, 
                                    sec.name AS section_name,
                                    sec.year_level AS year_level,
                                    sub.subject AS subject_name,
                                    r.name AS location 
                             FROM schedules s 
                             LEFT JOIN courses c ON s.course_id = c.id 
                             LEFT JOIN strands st ON s.strand_id = st.id 
                             LEFT JOIN sections sec ON s.section_id = sec.id
                             LEFT JOIN subjects sub ON s.subject_id = sub.id
                             LEFT JOIN rooms r ON s.room_id = r.id 
                             WHERE s.faculty_id = " . $faculty_id . 
                             " ORDER BY FIELD(s.dow, 0, 1, 2, 3, 4, 5, 6), s.time_from");
            
            if ($schedules->num_rows > 0) {
                $day_names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                
                // Define column widths for landscape mode
                $dayWidth = 50;
                $timeWidth = 50;
                $subjectWidth = 70;
                $sectionWidth = 35;
                $roomWidth = 35;
                $programWidth = 40;
                
                // Table header
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($dayWidth, 10, 'Day', 1, 0, 'C');
                $pdf->Cell($timeWidth, 10, 'Time', 1, 0, 'C');
                $pdf->Cell($subjectWidth, 10, 'Subject', 1, 0, 'C');
                $pdf->Cell($sectionWidth, 10, 'Section', 1, 0, 'C');
                $pdf->Cell($roomWidth, 10, 'Room', 1, 0, 'C');
                $pdf->Cell($programWidth, 10, 'Course/Strand', 1, 1, 'C');
                
                // Table data
                $pdf->SetFont('Arial', '', 10);
                
                while($row = $schedules->fetch_assoc()) {
                    // Format days with abbreviations for better fit
                    $days_arr = explode(',', $row['dow']);
                    $formatted_days = [];
                    $day_abbr = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; // Abbreviated day names
                    
                    foreach($days_arr as $day_index) {
                        if(isset($day_abbr[$day_index])) {
                            $formatted_days[] = $day_abbr[$day_index];
                        }
                    }
                    
                    $days_display = implode(', ', $formatted_days);
                    
                    // Format data for display
                    // Don't truncate text - we'll let the cells handle it with wrapping
                    $subject_name = isset($row['subject_name']) ? $row['subject_name'] : 'N/A';
                    $section_name = isset($row['section_name']) ? $row['section_name'] : 'N/A';
                    $location = isset($row['location']) ? $row['location'] : 'N/A';
                    
                    // Fix spacing in the time display
                    $time_display = date('h:i A', strtotime($row['time_from'])) . ' - ' . 
                                   date('h:i A', strtotime($row['time_to']));
                    
                    // Get course or strand
                    $program = !empty($row['course_name']) ? $row['course_name'] : 
                              (!empty($row['strand_name']) ? $row['strand_name'] : 'N/A');
                    
                                          // No need to limit text length - the cells have been sized properly
                    
                    $rowHeight = 10; // Default row height
                    
                    // Save current position
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();
                    
                    // Print cells with content
                    $pdf->Cell($dayWidth, $rowHeight, $days_display, 1, 0, 'L');
                    $pdf->Cell($timeWidth, $rowHeight, $time_display, 1, 0, 'L');
                    $pdf->Cell($subjectWidth, $rowHeight, $subject_name, 1, 0, 'L');
                    $pdf->Cell($sectionWidth, $rowHeight, $section_name, 1, 0, 'L');
                    $pdf->Cell($roomWidth, $rowHeight, $location, 1, 0, 'L');
                    $pdf->Cell($programWidth, $rowHeight, $program, 1, 1, 'L');
                }
            } else {
                $pdf->Cell(0, 10, 'No schedules found for this faculty.', 0, 1);
            }
        } else {
            $pdf->Cell(0, 10, 'Faculty not found.', 0, 1);
        }
    }
    // All schedules print
    else if (isset($_GET['all'])) {
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'All Schedules', 0, 1);
        $pdf->Ln(5);
        
        // Get all unique faculty with schedules
        $faculty_query = $conn->query("SELECT DISTINCT f.id, CONCAT(f.lastname, ', ', f.firstname, ' ', COALESCE(f.middlename, '')) AS name 
                                   FROM schedules s 
                                   JOIN faculty f ON s.faculty_id = f.id 
                                   ORDER BY f.lastname, f.firstname");
        
        if ($faculty_query->num_rows > 0) {
            $first = true;
            while($faculty = $faculty_query->fetch_assoc()) {
                // Add a new page for each faculty (except first one)
                if (!$first) {
                    $pdf->AddPage();
                }
                $first = false;
                
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->Cell(0, 10, 'Faculty: ' . $faculty['name'], 0, 1);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 10, 'Schedule Listing', 0, 1);
                $pdf->Ln(5);
                
                // Get all schedules for this faculty
                $schedules = $conn->query("SELECT s.*, 
                                        c.course AS course_name, 
                                        st.name AS strand_name, 
                                        sec.name AS section_name,
                                        sec.year_level AS year_level,
                                        sub.subject AS subject_name,
                                        r.name AS location 
                                 FROM schedules s 
                                 LEFT JOIN courses c ON s.course_id = c.id 
                                 LEFT JOIN strands st ON s.strand_id = st.id 
                                 LEFT JOIN sections sec ON s.section_id = sec.id
                                 LEFT JOIN subjects sub ON s.subject_id = sub.id
                                 LEFT JOIN rooms r ON s.room_id = r.id 
                                 WHERE s.faculty_id = " . $faculty['id'] . 
                                 " ORDER BY FIELD(s.dow, 0, 1, 2, 3, 4, 5, 6), s.time_from");
                
                if ($schedules->num_rows > 0) {
                    $day_names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    
                    // Define column widths for landscape mode
                    $dayWidth = 50;
                    $timeWidth = 50;
                    $subjectWidth = 70;
                    $sectionWidth = 35;
                    $roomWidth = 35;
                    $programWidth = 40;
                    
                    // Table header
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell($dayWidth, 10, 'Day', 1, 0, 'C');
                    $pdf->Cell($timeWidth, 10, 'Time', 1, 0, 'C');
                    $pdf->Cell($subjectWidth, 10, 'Subject', 1, 0, 'C');
                    $pdf->Cell($sectionWidth, 10, 'Section', 1, 0, 'C');
                    $pdf->Cell($roomWidth, 10, 'Room', 1, 0, 'C');
                    $pdf->Cell($programWidth, 10, 'Course/Strand', 1, 1, 'C');
                    
                    // Table data
                    $pdf->SetFont('Arial', '', 10);
                    
                    while($row = $schedules->fetch_assoc()) {
                        // Format days with abbreviations for better fit
                        $days_arr = explode(',', $row['dow']);
                        $formatted_days = [];
                        $day_abbr = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; // Abbreviated day names
                        
                        foreach($days_arr as $day_index) {
                            if(isset($day_abbr[$day_index])) {
                                $formatted_days[] = $day_abbr[$day_index];
                            }
                        }
                        
                        $days_display = implode(', ', $formatted_days);
                        
                        // Format data for display
                        // Don't truncate text - we'll let the cells handle it with wrapping
                        $subject_name = isset($row['subject_name']) ? $row['subject_name'] : 'N/A';
                        $section_name = isset($row['section_name']) ? $row['section_name'] : 'N/A';
                        $location = isset($row['location']) ? $row['location'] : 'N/A';
                        
                        // Fix spacing in the time display
                        $time_display = date('h:i A', strtotime($row['time_from'])) . ' - ' . 
                                       date('h:i A', strtotime($row['time_to']));
                        
                        // Get course or strand
                        $program = !empty($row['course_name']) ? $row['course_name'] : 
                                  (!empty($row['strand_name']) ? $row['strand_name'] : 'N/A');
                        
                                                  // No need to limit text length - the cells have been sized properly
                        
                        $rowHeight = 10; // Default row height
                        
                        // Save current position
                        $x = $pdf->GetX();
                        $y = $pdf->GetY();
                        
                        // Print cells with content
                        $pdf->Cell($dayWidth, $rowHeight, $days_display, 1, 0, 'L');
                        $pdf->Cell($timeWidth, $rowHeight, $time_display, 1, 0, 'L');
                        $pdf->Cell($subjectWidth, $rowHeight, $subject_name, 1, 0, 'L');
                        $pdf->Cell($sectionWidth, $rowHeight, $section_name, 1, 0, 'L');
                        $pdf->Cell($roomWidth, $rowHeight, $location, 1, 0, 'L');
                        $pdf->Cell($programWidth, $rowHeight, $program, 1, 1, 'L');
                    }
                } else {
                    $pdf->Cell(0, 10, 'No schedules found for this faculty.', 0, 1);
                }
            }
        } else {
            $pdf->Cell(0, 10, 'No schedules found.', 0, 1);
        }
    }

    // Output PDF
    $pdf->Output('I', 'schedule.pdf');
} catch (Exception $e) {
    // Display any errors that occur during PDF generation
    echo '<h1>Error creating PDF</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<p>Error on line ' . $e->getLine() . ' in file ' . $e->getFile() . '</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
?>
