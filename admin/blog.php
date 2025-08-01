<?php
include('include/db.php');

if(isset($_POST['formsubmit'])) {

    // Image Upload Handling
    $img = $_FILES['image']['name']; 
    $imgs = $_FILES['image']['tmp_name']; 
    $file = 'img/' . $img;
    move_uploaded_file($imgs, $file);

    // Get Form Data
    $titlee = $_POST['date']; 
    $headingg = $_POST['heading'];
    $descc = $_POST['desc'];
    $summerNote = $_POST['summernote']; 

    // SQL Query for Insertion
    $sql = "INSERT INTO `blog`(`img`, `date`, `tittle`, `discription`, `summernote`) 
            VALUES ('$img','$titlee','$headingg','$descc','$summerNote')";
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

            <label for="date">Date</label>
            <input type="date" name="date" class="forminput" id="dateInput">

            <label class="formlabel">Heading</label>
            <input type="text" name="heading" class="forminput" required><br><br>

            <label class="formlabel">Description</label>
            <textarea name="desc" class="formtextarea" required></textarea><br><br>

            <!-- Summernote Input Field -->
            <label class="formlabel">Summernote Content</label>
            <textarea id="summernote" name="summernote"></textarea><br><br>

            <button type="submit" class="formbutton" name="formsubmit">Submit</button>
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

    // Set today's date for the date input
    let today = new Date().toISOString().split('T')[0];
    let dateInput = document.getElementById("dateInput");
    dateInput.setAttribute("min", today);
    dateInput.setAttribute("value", today);
</script>
