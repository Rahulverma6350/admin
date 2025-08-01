<?php
include('include/db.php');

if(isset($_POST['formsubmit'])) {

    // 🟢 Image Upload Handling
    $img = $_FILES['image']['name']; 
    $imgs = $_FILES['image']['tmp_name']; 
    $file = 'img/' . $img;
    move_uploaded_file($imgs, $file);

    // 🟢 Get Form Data
    $name = $_POST['name']; 
    $profession = $_POST['profession'];
    
    // 🟢 Get Social Media Links as Array
    $socialMediaLinks = [
        'facebook' => $_POST['facebook'],
        'twitter' => $_POST['twitter'],
        'youtube' => $_POST['youtube']
    ];

    // 🔄 Convert Array to JSON Format (for Database Storage)
    $socialMediaJson = json_encode($socialMediaLinks);

    // 🟢 SQL Query for Insertion
    $sql = "INSERT INTO `aboutus_ourteam`(`photo`, `name`, `profession`, `social_media`) 
            VALUES ('$img','$name','$profession','$socialMediaJson')";
    $result = mysqli_query($conn, $sql);

    if($result) {
        header("location:viewAboutUs_team.php");
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

include('include/header.php');
?>

<div class="mainHeading">
    <h3>AboutUs_teamMember</h3>
</div>

<div class="content">
    <div class="mainContent">   
        <form method="POST" class="formcontainer" enctype="multipart/form-data">
            <label class="formlabel">Image</label>
            <input type="file" name="image" class="forminput" required><br>

            <label class="formlabel">Name</label>
            <input type="text" name="name" class="forminput" required>

            <label class="formlabel">Profession</label>
            <input type="text" name="profession" class="forminput" required><br>
            
            <label class="formlabel">Social Media Links</label>
            <input type="text" name="facebook" class="forminput" placeholder="Facebook Link"><br>
            <input type="text" name="twitter" class="forminput" placeholder="Twitter Link"><br>
            <input type="text" name="youtube" class="forminput" placeholder="YouTube Link"><br><br>

            <button type="submit" class="formbutton" name="formsubmit">Submit</button>
        </form>
    </div>
</div>

<?php include('include/footer.php'); ?>
