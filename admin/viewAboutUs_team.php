<?php
include('include/db.php');

$sqlf = "SELECT * FROM `aboutus_ourteam`";
$resf = mysqli_query($conn, $sqlf);

// delet ka 
if (isset($_GET['did'])) {
    $get = mysqli_real_escape_string($conn, $_GET['did']); // 🔒 Security Fix
    $del = "DELETE FROM aboutus_ourteam WHERE id = $get";
    $del_res = mysqli_query($conn, $del);

    if($del_res){
        header("location:viewAboutUs_team.php");
    }
}

include('include/header.php');
?>

<div class="mainHeading">
    <h3>View Blog</h3>
</div>

<div class="content">
    <div class="mainContent">
        <table id="myTable" class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Img</th>
                    <th>name</th>
                    <th>proffession</th>
                 
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($rowf = mysqli_fetch_assoc($resf)) {
                ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><img src="img/<?php echo $rowf['photo']; ?>" alt="Blog Image" width="80" height="80"></td>
                        <td><?php echo $rowf['name']; ?></td>
                        <td><?php echo $rowf['profession']; ?></td>
                        <td>
                            <a href="editabout_team.php?id=<?php echo $rowf['id']; ?>"><i class="fas fa-edit"></i></a>
             
                            <a href="viewAboutUs_team.php?did=<?php echo $rowf['id']; ?>" onclick="return confirm('Are you sure you want to delete this blog?');">
                                <i class="fa-solid fa-trash" style="color: red;"></i>
                            </a>
                        </td>
                    </tr>
                <?php 
                    $i++;
                } 
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('include/footer.php'); ?>