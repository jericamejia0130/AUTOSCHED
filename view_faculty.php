<?php include 'db_connect.php' ?>
<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $faculty = $conn->query("SELECT f.*, CONCAT(f.lastname, ', ', f.firstname, ' ', COALESCE(f.middlename,'')) as full_name,
                             d.designation 
                             FROM faculty f
                             LEFT JOIN designations d ON f.designation_id = d.id
                             WHERE f.id = $id");
    $row = $faculty->fetch_assoc();
}
?>
<div class="container-fluid">
    <div class="text-center mb-4">
        <?php if (!empty($row['image'])): ?>
            <img src="assets/uploads/<?php echo $row['image'] ?>" class="faculty-profile-img">
        <?php else: ?>
            <img src="assets/uploads/default.png" class="faculty-profile-img">
        <?php endif; ?>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%;">ID Number</th>
                            <td><?php echo $row['id_no'] ?></td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td><?php echo ucwords($row['full_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Designation</th>
                            <td><?php echo $row['designation'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .faculty-profile-img {
        max-width: 150px;
        max-height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid #f8f9fa;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    th {
        background-color: #f8f9fa;
    }
</style>