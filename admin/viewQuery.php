<?php
include('include/db.php');

$sql = "SELECT * FROM `query`";
$res = mysqli_query($conn, $sql);

include('include/header.php');
?>


<div class="mainHeading">
<h3>View query</h3>
</div>

<div class="content">

<div class="mainContent">

<table id="myTable" class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Firstnam</th>
            <th>Phone Nom</th>
            <th>Email</th>
            <th>Message</th>

        </tr>
    </thead>
    <tbody>
        <?php
        $count = 1;
        while ($row = mysqli_fetch_assoc($res)) {
        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $row['first_name']; ?></td>
                <td><?php echo $row['phone_num']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['message']; ?></td>

            </tr>
    
    <?php
    $count++; 
    } ?>
    </tbody>
</table>

<?php include('include/footer.php'); ?>
