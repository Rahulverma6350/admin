<?php
include('include/db.php');

$sqlf = "SELECT * FROM `home_header`";
$resf = mysqli_query($conn, $sqlf);
$rowf = mysqli_fetch_assoc($resf);


if (isset($_POST['update_form'])) {
    $titlee = $_POST['etitle'];
    $headingg = $_POST['eheading'];
    $descc = $_POST['edesc'];
    $id = $rowf['id']; // Assuming you have an ID column

    // Fetch existing images from database
    $existingImages = explode(',', $rowf['slider_imgs']);

    // Handle Deleting Selected Images
    if (!empty($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $deleteImg) {
            $filePath = "./img/$deleteImg";
            if (file_exists($filePath)) {
                unlink($filePath); // Delete file from folder
            }
            // Remove from existing images array
            $existingImages = array_diff($existingImages, [$deleteImg]);
        }
    }

    // Handle New Image Uploads
    $newImages = [];
    if (!empty($_FILES['eimeg']['name'][0])) { // Check if any file is uploaded
        foreach ($_FILES['eimeg']['name'] as $key => $imageName) {
            $imageTmpName = $_FILES['eimeg']['tmp_name'][$key];
            $folder = "./img/" . $imageName;
            if (move_uploaded_file($imageTmpName, $folder)) {
                $newImages[] = $imageName;
            }
        }
    }

    // Merge remaining old images with new images
    $finalImages = array_merge($existingImages, $newImages);
    $finalImagesString = implode(',', $finalImages); // Convert array to string

    // Update database with new image list
    $sql = "UPDATE home_header SET 
                upper_tittle = '$titlee',
                heading = '$headingg',
                desce = '$descc',
                slider_imgs = '$finalImagesString'
            WHERE id = $id";

    $res = mysqli_query($conn, $sql);

    if ($res) {
        header('location: viewHeader.php');
    }
}


include('include/header.php');
?>

<div class="mainHeading">
<h3>Edit Home Header</h3>
</div>

<div class="content">

<div class="mainContent">  
     
<form method="POST" class="formcontainer" enctype="multipart/form-data">
    <label class="formlabel">Title</label>
    <input type="text" name="etitle" class="forminput" value="<?php echo htmlspecialchars($rowf['upper_tittle']); ?>"><br><br>

    <label class="formlabel">Heading</label>
    <input type="text" name="eheading" class="forminput" value="<?php echo htmlspecialchars($rowf['heading']); ?>"><br><br>

    <label class="formlabel">Description</label>
    <textarea name="edesc" class="formtextarea"><?php echo htmlspecialchars($rowf['desce']); ?></textarea><br><br>

    <!-- Display Existing Images -->
    <label class="formlabel">Existing Slider Images</label><br>
    <?php
    $images = explode(',', $rowf['slider_imgs']); // Convert comma-separated images into an array
    foreach ($images as $img) {
        if (!empty($img)) {
            echo "<div style='display:inline-block; margin:5px;'>
                    <img src='img/$img' width='100' height='50' alt='Image'>
                    <input type='checkbox' name='delete_images[]' value='$img'>🗑️
                  </div>";
        }
    }
    ?>
    <br><br>

    <!-- Upload New Images -->
    <label class="formlabel">Upload New Slider Images</label>
    <input type="file" name="eimeg[]" multiple class="forminput"><br><br>

    <button type="submit" class="formbutton" name="update_form">Update</button>
</form>


</div>
   

<?php include('include/footer.php'); ?>
