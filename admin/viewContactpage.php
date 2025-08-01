<?php 
include('include/db.php');

// Fetch Data
$sql = "SELECT * FROM contact";
$result = mysqli_query($conn, $sql);

include('include/header.php'); 

?>

<div class="mainHeading">
    <h3>View Contact</h3>
</div>

<div class="content">
    <div class="mainContent">

        <table id="myTable" class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Phone num</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $row['phone_no']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['addr']; ?></td>
                    <td><a href="editContactpage.php?eid=<?php echo $row['id']; ?>"><i class="fas fa-edit"></i></a>                     
                    </td>
                </tr>
                <?php
                $i++;
                }
                ?>
            </tbody>
        </table>
   
<?php include('include/footer.php');?>
