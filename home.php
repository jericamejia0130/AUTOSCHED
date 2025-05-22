<?php include 'db_connect.php' ?>
<style>
   span.float-right.summary_icon {
    font-size: 3rem;
    position: absolute;
    right: 1rem;
    color: #ffffff96;
}
.imgs{
		margin: .5em;
		max-width: calc(100%);
		max-height: calc(100%);
	}
	.imgs img{
		max-width: calc(100%);
		max-height: calc(100%);
		cursor: pointer;
	}
	#imagesCarousel,#imagesCarousel .carousel-inner,#imagesCarousel .carousel-item{
		height: 60vh !important;background: black;
	}
	#imagesCarousel .carousel-item.active{
		display: flex !important;
	}
	#imagesCarousel .carousel-item-next{
		display: flex !important;
	}
	#imagesCarousel .carousel-item img{
		margin: auto;
	}
	#imagesCarousel img{
		width: auto!important;
		height: auto!important;
		max-height: calc(100%)!important;
		max-width: calc(100%)!important;
	}
    
    /* Dark mode compatibility for dashboard cards */
    .dashboard-card {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    body.dark-mode .dashboard-card.bg-primary {
        background-color: #0078d7 !important;
    }
    
    body.dark-mode .dashboard-card.bg-success {
        background-color: #107c41 !important;
    }
    
    body.dark-mode .dashboard-card.bg-warning {
        background-color: #c75000 !important;
    }
    
    body.dark-mode .dashboard-card.bg-danger {
        background-color: #d13438 !important;
    }
    
    body.dark-mode .welcome-card {
        background-color: var(--dark-card) !important;
        color: var(--dark-text) !important;
        border-color: var(--dark-border) !important;
    }
    
    body:not(.dark-mode) .welcome-card {
        background-color: var(--light-card);
        color: var(--light-text);
        border-color: var(--light-border);
    }
    
    /* Responsive fixes for small screens */
    @media (max-width: 768px) {
        .dashboard-card {
            margin-bottom: 15px;
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Total Teachers/Staff Box -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3 dashboard-card">
                <div class="card-header">Total Teachers/Staff</div>
                <div class="card-body">
                    <?php
                    $faculty_count = $conn->query("SELECT COUNT(*) as total FROM faculty")->fetch_assoc()['total'];
                    ?>
                    <h5 class="card-title text-center"><?php echo $faculty_count; ?></h5>
                </div>
            </div>
        </div>

        <!-- Total Departments Box -->
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3 dashboard-card">
                <div class="card-header">Total Departments</div>
                <div class="card-body">
                    <?php
                    $department_count = $conn->query("SELECT COUNT(*) as total FROM courses")->fetch_assoc()['total'];
                    ?>
                    <h5 class="card-title text-center"><?php echo $department_count; ?></h5>
                </div>
            </div>
        </div>

        <!-- Total Subjects Box -->
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3 dashboard-card">
                <div class="card-header">Total Subjects</div>
                <div class="card-body">
                    <?php
                    $subject_count = $conn->query("SELECT COUNT(*) as total FROM subjects")->fetch_assoc()['total'];
                    ?>
                    <h5 class="card-title text-center"><?php echo $subject_count; ?></h5>
                </div>
            </div>
        </div>

        <!-- Total Rooms & Laboratories Box -->
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3 dashboard-card">
                <div class="card-header">Total Rooms & Laboratories</div>
                <div class="card-body">
                    <?php
                    $room_count = $conn->query("SELECT COUNT(*) as total FROM rooms")->fetch_assoc()['total'];
                    ?>
                    <h5 class="card-title text-center"><?php echo $room_count; ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Overview Graph Section -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header bg-primary text-white">
                    <h5>Schedule Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="scheduleChart" height="300"></canvas>
                        </div>
                        <div class="col-md-4">
                            <div class="schedule-stats p-3">
                                <?php
                                // Get total schedules
                                $total_schedules = $conn->query("SELECT COUNT(*) as total FROM schedules")->fetch_assoc()['total'];
                                
                                // Get active schedules (current month)
                                $current_month = date('Y-m');
                                $active_schedules = $conn->query("SELECT COUNT(*) as total FROM schedules 
                                    WHERE ('$current_month' BETWEEN DATE_FORMAT(month_from, '%Y-%m') AND DATE_FORMAT(month_to, '%Y-%m')) 
                                    OR (month_from <= NOW() AND month_to IS NULL)")->fetch_assoc()['total'];
                                
                                // Get schedules by department and strand (top 3)
                                $dept_query = $conn->query("SELECT c.course, COUNT(*) as count FROM schedules s 
                                    LEFT JOIN courses c ON s.course_id = c.id 
                                    WHERE c.course IS NOT NULL 
                                    GROUP BY s.course_id 
                                    ORDER BY count DESC LIMIT 3");
                                
                                $strand_query = $conn->query("SELECT st.name, COUNT(*) as count FROM schedules s 
                                    LEFT JOIN strands st ON s.strand_id = st.id 
                                    WHERE st.name IS NOT NULL 
                                    GROUP BY s.strand_id 
                                    ORDER BY count DESC LIMIT 3");
                                ?>
                                
                                <h6>Schedule Statistics</h6>
                                <p>Total Schedules: <strong><?php echo $total_schedules; ?></strong></p>
                                <p>Active Schedules: <strong><?php echo $active_schedules; ?></strong></p>
                                
                                <h6 class="mt-4">Top Departments</h6>
                                <ul class="list-group">
                                    <?php while($row = $dept_query->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $row['course']; ?>
                                        <span class="badge badge-primary badge-pill"><?php echo $row['count']; ?></span>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                                
                                <h6 class="mt-4">Top Strands</h6>
                                <ul class="list-group">
                                    <?php while($row = $strand_query->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $row['name']; ?>
                                        <span class="badge badge-primary badge-pill"><?php echo $row['count']; ?></span>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Time Slot Heatmap Section -->
<div class="container-fluid mt-4 mb-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header bg-primary text-white">
                    <h5>Schedule Time Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="timeHeatmap" height="250"></canvas>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <h6>Time Slot Analysis</h6>
                                <p class="text-muted">
                                    This heatmap displays the distribution of classes across different time slots during the week.
                                    Darker colors indicate more classes scheduled during that time.
                                </p>
                                
                                <?php
                                // Get busiest day and time
                                $busiest_day_query = $conn->query("SELECT 
                                    CASE 
                                        WHEN dow = '0' THEN 'Sunday'
                                        WHEN dow = '1' THEN 'Monday'
                                        WHEN dow = '2' THEN 'Tuesday'
                                        WHEN dow = '3' THEN 'Wednesday'
                                        WHEN dow = '4' THEN 'Thursday'
                                        WHEN dow = '5' THEN 'Friday'
                                        WHEN dow = '6' THEN 'Saturday'
                                    END as day_name,
                                    COUNT(*) as class_count
                                    FROM schedules 
                                    WHERE dow IS NOT NULL AND dow != ''
                                    GROUP BY dow
                                    ORDER BY class_count DESC
                                    LIMIT 1");
                                
                                $busiest_time_query = $conn->query("SELECT 
                                    TIME_FORMAT(time_from, '%h:%i %p') as start_time,
                                    TIME_FORMAT(time_to, '%h:%i %p') as end_time,
                                    COUNT(*) as slot_count
                                    FROM schedules 
                                    WHERE time_from IS NOT NULL AND time_to IS NOT NULL
                                    GROUP BY TIME_FORMAT(time_from, '%H:%i'), TIME_FORMAT(time_to, '%H:%i')
                                    ORDER BY slot_count DESC
                                    LIMIT 1");
                                
                                $busiest_day = $busiest_day_query->fetch_assoc();
                                $busiest_time = $busiest_time_query->fetch_assoc();
                                ?>
                                
                                <div class="mt-4">
                                    <h6>Schedule Insights</h6>
                                    <div class="card bg-light mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Busiest Day</h5>
                                            <p class="card-text">
                                                <span class="badge badge-primary"><?php echo isset($busiest_day['day_name']) ? $busiest_day['day_name'] : 'N/A'; ?></span>
                                                with <?php echo isset($busiest_day['class_count']) ? $busiest_day['class_count'] : '0'; ?> classes
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title">Popular Time Slot</h5>
                                            <p class="card-text">
                                                <span class="badge badge-primary">
                                                    <?php echo isset($busiest_time['start_time']) ? $busiest_time['start_time'] . ' - ' . $busiest_time['end_time'] : 'N/A'; ?>
                                                </span>
                                                with <?php echo isset($busiest_time['slot_count']) ? $busiest_time['slot_count'] : '0'; ?> schedules
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
	$('#manage-records').submit(function(e){
        e.preventDefault()
        start_load()
        $.ajax({
            url:'ajax.php?action=save_track',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            success:function(resp){
                resp=JSON.parse(resp)
                if(resp.status==1){
                    alert_toast("Data successfully saved",'success')
                    setTimeout(function(){
                        location.reload()
                    },800)

                }
                
            }
        })
    })
    $('#tracking_id').on('keypress',function(e){
        if(e.which == 13){
            get_person()
        }
    })
    $('#check').on('click',function(e){
            get_person()
    })
    function get_person(){
            start_load()
        $.ajax({
                url:'ajax.php?action=get_pdetails',
                method:"POST",
                data:{tracking_id : $('#tracking_id').val()},
                success:function(resp){
                    if(resp){
                        resp = JSON.parse(resp)
                        if(resp.status == 1){
                            $('#name').html(resp.name)
                            $('#address').html(resp.address)
                            $('[name="person_id"]').val(resp.id)
                            $('#details').show()
                            end_load()

                        }else if(resp.status == 2){
                            alert_toast("Unknow tracking id.",'danger');
                            end_load();
                        }
                    }
                }
            })
    }
    
    // Schedule chart data
    $(document).ready(function() {
        $.ajax({
            url: 'ajax.php?action=get_schedule_by_day',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                // jQuery will automatically parse the JSON now
                createScheduleChart(data);
            },
            error: function(xhr, status, error) {
                console.log("Error fetching schedule data:", error);
                createScheduleChart({});
            }
        });
    });
    
    function createScheduleChart(data) {
        // If no data available, create sample data for display
        if (!data || Object.keys(data).length === 0) {
            data = {
                'Sunday': 0,
                'Monday': 15,
                'Tuesday': 20,
                'Wednesday': 18,
                'Thursday': 22,
                'Friday': 10,
                'Saturday': 2
            };
        }
        
        var ctx = document.getElementById('scheduleChart').getContext('2d');
        
        // Get theme-appropriate colors
        var isDarkMode = document.body.classList.contains('dark-mode');
        var textColor = isDarkMode ? '#ffffff' : '#333333';
        var gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    label: 'Number of Classes',
                    data: Object.values(data),
                    backgroundColor: [
                        'rgba(160, 196, 255, 0.7)',
                        'rgba(79, 129, 189, 0.7)',
                        'rgba(0, 112, 192, 0.7)',
                        'rgba(0, 176, 240, 0.7)', 
                        'rgba(0, 176, 80, 0.7)',
                        'rgba(255, 192, 0, 0.7)',
                        'rgba(192, 0, 0, 0.7)'
                    ],
                    borderColor: [
                        'rgba(160, 196, 255, 1)',
                        'rgba(79, 129, 189, 1)',
                        'rgba(0, 112, 192, 1)',
                        'rgba(0, 176, 240, 1)', 
                        'rgba(0, 176, 80, 1)',
                        'rgba(255, 192, 0, 1)',
                        'rgba(192, 0, 0, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Schedule Distribution by Day of Week',
                        color: textColor,
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw + ' classes';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            precision: 0
                        },
                        grid: {
                            color: gridColor
                        }
                    },
                    x: {
                        ticks: {
                            color: textColor
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            }
        });
        
        // Update chart colors when theme changes
        window.addEventListener('themeChange', function() {
            var isDarkMode = document.body.classList.contains('dark-mode');
            chart.options.plugins.title.color = isDarkMode ? '#ffffff' : '#333333';
            chart.options.scales.y.ticks.color = isDarkMode ? '#ffffff' : '#333333';
            chart.options.scales.x.ticks.color = isDarkMode ? '#ffffff' : '#333333';
            chart.options.scales.y.grid.color = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            chart.options.scales.x.grid.color = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            chart.update();
        });
    }
    
    // Create the time slot heatmap
    $(document).ready(function() {
        $.ajax({
            url: 'ajax.php?action=get_schedule_time_heatmap',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                // jQuery will automatically parse the JSON now
                createTimeHeatmap(data);
            },
            error: function(xhr, status, error) {
                console.log("Error fetching time slot data:", error);
                // Create with sample data anyway
                createTimeHeatmap();
            }
        });
    });
    
    function createTimeHeatmap(data) {
        // Default time slots
        var timeSlots = [
            '7:00 AM', '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', 
            '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', 
            '5:00 PM', '6:00 PM', '7:00 PM'
        ];
        
        var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        // Sample data if none provided
        if (!data) {
            data = {
                'Monday': {'8:00 AM': 5, '9:00 AM': 8, '10:00 AM': 12, '1:00 PM': 10, '2:00 PM': 7},
                'Tuesday': {'8:00 AM': 6, '9:00 AM': 9, '10:00 AM': 11, '1:00 PM': 9, '2:00 PM': 8},
                'Wednesday': {'8:00 AM': 4, '9:00 AM': 7, '10:00 AM': 10, '1:00 PM': 8, '2:00 PM': 6},
                'Thursday': {'8:00 AM': 7, '9:00 AM': 10, '10:00 AM': 13, '1:00 PM': 11, '2:00 PM': 9},
                'Friday': {'8:00 AM': 3, '9:00 AM': 6, '10:00 AM': 9, '1:00 PM': 7, '2:00 PM': 5}
            };
        }
        
        // Prepare dataset for heatmap
        var heatmapData = [];
        
        days.forEach(function(day, dayIndex) {
            timeSlots.forEach(function(time, timeIndex) {
                var value = 0;
                if (data[day] && data[day][time]) {
                    value = data[day][time];
                }
                
                heatmapData.push({
                    x: day,
                    y: time,
                    v: value
                });
            });
        });
        
        // Get theme colors
        var isDarkMode = document.body.classList.contains('dark-mode');
        var textColor = isDarkMode ? '#ffffff' : '#333333';
        var gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        
        // Create color scale
        function getColor(value) {
            var minColor = isDarkMode ? 'rgba(0, 120, 215, 0.1)' : 'rgba(0, 120, 215, 0.1)';
            var maxColor = isDarkMode ? 'rgba(0, 120, 215, 0.9)' : 'rgba(0, 120, 215, 0.9)';
            
            // Find the max value
            var max = 0;
            heatmapData.forEach(function(item) {
                if (item.v > max) max = item.v;
            });
            
            // Calculate color intensity
            var intensity = value / (max === 0 ? 1 : max);
            var r = parseInt(minColor.match(/\d+/g)[0] + (maxColor.match(/\d+/g)[0] - minColor.match(/\d+/g)[0]) * intensity);
            var g = parseInt(minColor.match(/\d+/g)[1] + (maxColor.match(/\d+/g)[1] - minColor.match(/\d+/g)[1]) * intensity);
            var b = parseInt(minColor.match(/\d+/g)[2] + (maxColor.match(/\d+/g)[2] - minColor.match(/\d+/g)[2]) * intensity);
            var a = parseFloat(minColor.match(/[\d\.]+/g)[3] + (maxColor.match(/[\d\.]+/g)[3] - minColor.match(/[\d\.]+/g)[3]) * intensity);
            
            return 'rgba(' + r + ',' + g + ',' + b + ',' + a + ')';
        }
        
        // Process data for chart.js
        var chartData = {
            labels: days,
            datasets: timeSlots.map(function(time, timeIndex) {
                return {
                    label: time,
                    data: days.map(function(day, dayIndex) {
                        var value = 0;
                        if (data[day] && data[day][time]) {
                            value = data[day][time];
                        }
                        return value;
                    }),
                    backgroundColor: days.map(function(day, dayIndex) {
                        var value = 0;
                        if (data[day] && data[day][time]) {
                            value = data[day][time];
                        }
                        return getColor(value);
                    })
                };
            })
        };
        
        var ctx = document.getElementById('timeHeatmap').getContext('2d');
        
        var chart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    title: {
                        display: true,
                        text: 'Class Schedule Time Distribution',
                        color: textColor,
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ' - ' + context.label + ': ';
                                }
                                label += context.raw + ' classes';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            stepSize: 5
                        },
                        grid: {
                            color: gridColor
                        }
                    },
                    y: {
                        stacked: true,
                        ticks: {
                            color: textColor
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            }
        });
        
        // Update chart colors when theme changes
        window.addEventListener('themeChange', function() {
            var isDarkMode = document.body.classList.contains('dark-mode');
            chart.options.plugins.title.color = isDarkMode ? '#ffffff' : '#333333';
            chart.options.scales.x.ticks.color = isDarkMode ? '#ffffff' : '#333333';
            chart.options.scales.y.ticks.color = isDarkMode ? '#ffffff' : '#333333';
            chart.options.scales.x.grid.color = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            chart.options.scales.y.grid.color = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            
            // Update dataset colors
            chart.data.datasets.forEach(function(dataset, i) {
                dataset.backgroundColor = days.map(function(day, dayIndex) {
                    var value = 0;
                    if (data[day] && data[day][dataset.label]) {
                        value = data[day][dataset.label];
                    }
                    return getColor(value);
                });
            });
            
            chart.update();
        });
    }
</script>