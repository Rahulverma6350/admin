


<?php
include('include/db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch existing data
    $sql = "SELECT * FROM `aboutus_ourteam` WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $rowAboutfeatch = mysqli_fetch_assoc($result);

}

// Update logic
if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $profession = mysqli_real_escape_string($conn, $_POST['profession']);

    $socialMediaLinks = [
        'facebook' => $_POST['facebook'],
        'twitter' => $_POST['twitter'],
        'youtube' => $_POST['youtube']
    ];

    // 🔄 Convert Array to JSON Format (for Database Storage)
    $socialMediaJson = json_encode($socialMediaLinks);
    
    // Handle image upload if a new image is selected
    if (!empty($_FILES['photo']['name'])) {
        $photo = $_FILES['photo']['name'];
        $target = "img/" . basename($photo);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target);
    } else {
        $photo = $rowAboutfeatch['photo']; // Keep the old image if no new image is uploaded
    }

    $updateQuery = "UPDATE `aboutus_ourteam` SET `photo`='$photo',`name`='$name',`profession`='$profession',`social_media`='$socialMediaJson' WHERE id = $id";
    
    if (mysqli_query($conn, $updateQuery)) {
        echo "<script> window.location.href='viewAboutUs_team.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

include('include/header.php');
?>

<div class="mainHeading">
    <h3>editAbout_team</h3>
</div>

<div class="content">
    <div class="mainContent">
    <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
            <label class="form-label">Current Image</label><br>
            <img src="img/<?php echo $rowAboutfeatch['photo']; ?>" width="100" height="100">
        </div>
        <div class="mb-3">
            <label class="form-label">Upload New Image (optional)</label>
            <input type="file" name="photo" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo $rowAboutfeatch['name']; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Profession</label>
            <input type="text" name="profession" class="form-control" value="<?php echo $rowAboutfeatch['profession']; ?>" required>
        </div>

        <?php $socialMediaLinks = json_decode($rowAboutfeatch['social_media'], true); ?>
        <label class="formlabel">Social Media Links</label>
            <input type="text" name="facebook" class="forminput" placeholder="Facebook Link" value="<?php echo $socialMediaLinks['facebook']; ?>" ><br>
            <input type="text" name="twitter" class="forminput" placeholder="Twitter Link" value="<?php echo $socialMediaLinks['twitter']; ?>"> <br>
            <input type="text" name="youtube" class="forminput" placeholder="YouTube Link" value="<?php echo $socialMediaLinks['youtube']; ?>"><br><br>

        <button type="submit" name="update" class="btn btn-success">Update</button>
        <a href="viewBlog.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('include/footer.php'); ?>
