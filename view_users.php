<?php
include('header.php');
include('session.php');
include('navbar.php');
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>User Profile</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$session_id'");
                    while ($row = mysqli_fetch_array($query)) {
                    ?>
                        <tr>
                            <td><?php echo $row['id_no']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <a href="edit_profile.php?id=<?php echo $row['user_id']; ?>" class="btn btn-primary">Edit</a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>