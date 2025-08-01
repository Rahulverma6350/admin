<?php include('include/header.php'); 
include('include\db.php');

if(isset($_POST['submit'])){

$nomber = $_POST['number'];
$email = $_POST['email'];
$adress = $_POST['adress'];

$sql = "INSERT INTO contact(phone_no, email, addr) VALUES ('$nomber','$email','$adress')";
$result = mysqli_query($conn, $sql);

if($result){
    header('location: viewContactpage.php');
}

};
?>

<div class="mainHeading">
    <h3>Add Contact Details</h3>
</div>

<div class="content">
    <div class="mainContent">
   
   
    <form method="post">

    <div class="input_box">
    <label for="inputPhone">Phone Number</label>
    <input type="tel" name="number"  class="forminput" required>
    </div>

            <div class="input_box">
            <label for="formUsername">Email</label>
                <input type="email" name="email" required  class="forminput">
            </div>

            <div class="input_box mt-1">
            <label for="inputPassword">Address</label>
                <input type="text" name="adress" required  class="forminput">
            </div>
            <button type="submit" class="btn btn-success mt-1" name="submit">Submit</button>
        </form>
    </div>
</div>

<?php include('include/footer.php'); ?>
  