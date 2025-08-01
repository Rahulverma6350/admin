<?php
include('include/db.php');

// 🟢 Step 1: Get Blog ID from URL
if(isset($_GET['id'])) {
    $blog_id = $_GET['id'];

    // Fetch Blog Data
    $sql = "SELECT * FROM `blog` WHERE id = $blog_id";
    $res = mysqli_query($conn, $sql);
    $rowBlog= mysqli_fetch_assoc($res);

}

// 🟢 Step 2: Handle Form Submission for Update
if(isset($_POST['updateBlog'])) {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $summernote = $_POST['summernote']; 

    // Fetch old image before update
    $get_blog = mysqli_query($conn, "SELECT * FROM blog WHERE id = $blog_id");
    $row = mysqli_fetch_assoc($get_blog);

    // Image Upload Handling
    if(!empty($_FILES['image']['name'])) {
        $img = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $file_path = "img/" . $img;
        move_uploaded_file($tmp_name, $file_path);
    } else {
        // Use old image if new one is not uploaded
        $img = $row['img'];
    }

    // Update Query
    $update_sql = "UPDATE `blog` SET `img`='$img', `date`='$date', `tittle`='$title', `discription`='$description', `summernote`='$summernote' WHERE `id`=$blog_id";
    $update_res = mysqli_query($conn, $update_sql);

    if($update_res) {
        header('location:viewBlog.php');
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}


include('include/header.php');
?>

<div class="mainHeading">
    <h3>Edit Blog</h3>
</div>

<div class="content">
    <div class="mainContent">
        <form method="POST" enctype="multipart/form-data">
            <label class="formlabel">Image</label>
            <input type="file" name="image" class="forminput"><br>
            <img src="img/<?php echo $rowBlog['img']; ?>" width="100" height="100"><br><br>

            <label for="date">Date</label>
            <input type="date" name="date" class="forminput" value="<?php echo $rowBlog['date']; ?>">

            <label class="formlabel">Title</label>
            <input type="text" name="title" class="forminput" value="<?php echo $rowBlog['tittle']; ?>" required><br><br>

            <label class="formlabel">Description</label>
            <textarea name="description" class="formtextarea" required><?php echo $rowBlog['discription']; ?></textarea><br><br>

            <!-- 🟢 Summernote Input Field (Data Pre-Fill Ho Raha Hai) -->
            <label class="formlabel">Summernote Content</label>
            <textarea id="summernote" name="summernote"><?php echo htmlspecialchars($rowBlog['summernote']); ?></textarea><br><br>

            <button type="submit" class="formbutton" name="updateBlog">Update</button>
        </form>
    </div>
</div>

<?php include('include/footer.php'); ?>

<!-- Summernote CDN Links -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

<script>
    // Initialize Summernote
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 200
        });
    });
</script>
