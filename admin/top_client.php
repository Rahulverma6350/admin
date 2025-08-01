
<?php
include('include/db.php');

if(isset($_POST['formsubmit'])) {

    // Image Upload Handling
    $img = $_FILES['image']['name']; 
    $imgs = $_FILES['image']['tmp_name']; 
    $file = 'img/' . $img;
    move_uploaded_file($imgs, $file);

    // Get Form Data
    $name = $_POST['nam']; 
    $profession = $_POST['profession'];
    $descc = $_POST['desc'];

    // SQL Query for Insertion
    $sql = "INSERT INTO `top_client`(`img`, `name`, `profession`, `description`) VALUES ('$img','$name','$profession','$descc')";
    $result = mysqli_query($conn, $sql);

}
include('include/header.php');
?>

<div class="mainHeading">
    <h3>Add Home Header</h3>
</div>

<div class="content">
    <div class="mainContent">   
        <form method="POST" class="formcontainer" enctype="multipart/form-data">
            <label class="formlabel">Image</label>
            <input type="file" name="image" class="forminput" required><br><br>

            <label for="name">name</label>
            <input type="text" name="nam" class="forminput">

            <label class="formlabel">proFession</label>
            <input type="text" name="profession" class="forminput" required><br><br>

            <label class="formlabel">Description</label>
            <textarea name="desc" class="formtextarea" required></textarea><br><br>

            <button type="submit" class="formbutton" name="formsubmit">Submit</button>
        </form>
    </div>
</div>

<?php include('include/footer.php'); ?>

