
<?php
include('include/db.php');

$sqlf = "SELECT * FROM `blog`";
$resf = mysqli_query($conn, $sqlf);

// delet ka code 

if (isset($_GET['did'])) {
    $get = mysqli_real_escape_string($conn, $_GET['did']); 
    $del = "DELETE FROM `blog` WHERE id = $get";
    $del_res = mysqli_query($conn, $del);

    if($del_res){
        header("location:viewBlog.php");
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
                    <th>Title</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($rowf = mysqli_fetch_assoc($resf)) {
                    // Date Formatting (Convert YYYY-MM-DD to "February 04, 2022")
                    $formattedDate = date("F d, Y", strtotime($rowf['date']));
                ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><img src="img/<?php echo $rowf['img']; ?>" alt="Blog Image" width="80" height="80"></td>
                        <td><?php echo $formattedDate; ?></td>
                        <td><?php echo $rowf['tittle']; ?></td>
                        <td><?php echo $rowf['discription']; ?></td>
                        <td>
                            <a href="editBlog.php?id=<?php echo $rowf['id']; ?>"><i class="fas fa-edit"></i></a>
                            <a href="viewBlog.php?did=<?php echo $rowf['id']; ?>" onclick="return confirm('Are you sure you want to delete this blog?');">
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
