<?php
include('include/db.php');

if(isset($_POST['formsubmit'])) {

    $titlee = $_POST['title'];
    $headingg = $_POST['heading'];
    $descc = $_POST['desc'];

    $uploaded_images = []; // Array to store all image names

    if (!empty($_FILES['imeg']['name'][0])) { // Check if at least one file is uploaded

        $totalFiles = count($_FILES['imeg']['name']);

        for ($i = 0; $i < $totalFiles; $i++) {
            $imge = $_FILES['imeg']['name'][$i];
            $imges = $_FILES['imeg']['tmp_name'][$i];

            if ($imge) {
                $folder = 'img/' . basename($imge);

                if (move_uploaded_file($imges, $folder)) {
                    $uploaded_images[] = $imge; // Store image name
                }
            }
        }
    }

    if (!empty($uploaded_images)) {
        $image_string = implode(',', $uploaded_images); // Convert array to comma-separated string

        $sql = "INSERT INTO `home_header` (`upper_tittle`, `heading`, `desce`, `slider_imgs`) 
                VALUES ('$titlee', '$headingg', '$descc', '$image_string')";
        
        $res = mysqli_query($conn, $sql);

        if ($res) {
            header('location: viewHeader.php');
            exit();
        }
    }
}
include('include/header.php');
?>


 <!-- Success Message Box
 <div id="successMessage">
            Data inserted successfully!
            <div class="progress-bar"></div>
</div> -->


<div class="mainHeading">
<h3>Add Home Header</h3>
</div>

<div class="content">

<div class="mainContent">   

<form  method="POST" class="formcontainer" enctype="multipart/form-data">
        <label class="formlabel">Title</label>
        <input type="text" name="title" class="forminput" required><br><br>
        
        <label  class="formlabel">Heading</label>
        <input type="text" name="heading" class="forminput" required><br><br>
        
        <label  class="formlabel">Description</label>
        <textarea  name="desc" class="formtextarea" required></textarea><br><br>

        <label for="message">Slider Images</label>
        <input type="file" name="imeg[]" multiple class="forminput">
        
        <button type="submit" class="formbutton" name="formsubmit">Submit</button>
    </form>

</div>
   

<?php include('include/footer.php'); ?>
