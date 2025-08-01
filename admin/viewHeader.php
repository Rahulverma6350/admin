<?php
include('include/db.php');

$sqlf = "SELECT * FROM `home_header`";
$resf = mysqli_query($conn, $sqlf);

include('include/header.php');
?>

<div class="mainHeading">
<h3>View Home Header</h3>
</div>

<div class="content">

<div class="mainContent">
    
<table id="myTable" class="table table-striped">
    <thead>
       <tr>
        <th>#</th>
        <th>Heading</th>
        <th>Slider Images</th>
        <th>Action</th>
        </tr>
    </thead>
<tbody>
    <?php
    $i=1;
    while($rowf = mysqli_fetch_assoc($resf)){
    ?>
    <tr>
      <td><?php echo $i; ?></td>
      <td><?php echo $rowf['heading'] ?></td>
      <td>
    <?php 
    $images = explode(',', $rowf['slider_imgs']); // Convert string to array
    foreach ($images as $img) {
        if (!empty($img)) { // Ensure the value is not empty
            echo "<img src='img/$img' width='70' height='50' alt='Image'> ";
        }
    }
    ?>
</td>

      <td>
        <a href="editHeader.php"><i class="fas fa-edit"></i></a>
        <!-- <a href="#" class="ms-1"><i class="fa fa-trash text-success" aria-hidden="true"></i>
        </a> -->
      </td>
      </tr>
</tbody>
      <?php 
      $i++;
} ?>

</div>

<?php include('include/footer.php'); ?>
