<?php 
include('include/db.php');


if (isset($_GET['eid'])) {
    $contactId = $_GET['eid']; 

    // Fetch the contact data based on the ID
    $sql = "SELECT * FROM contact WHERE id = $contactId";
    $result = mysqli_query($conn, $sql);
    $contact = mysqli_fetch_assoc($result);

    if (!$contact) {
        die("Contact not found.");
    }

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $phoneNo = mysqli_real_escape_string($conn, $_POST['phone_no']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);

        // Update the contact information in the database
        $updateSql = "UPDATE contact SET phone_no = '$phoneNo', email = '$email', addr = '$address' WHERE id = $contactId";
        if (mysqli_query($conn, $updateSql)) {
            echo "Contact updated successfully.";
            header("Location: viewContactpage.php"); // Redirect to the contact list after update
            exit();
        } else {
            echo "Error updating contact: " . mysqli_error($conn);
        }
    }
} else {
    die("Contact ID not provided.");
}

include('include/header.php');
?>

<div class="mainHeading">
    <h3>Edit Contact Information</h3>
</div>

<div class="content">
    <div class="mainContent">
        <!-- Edit Form -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="phone_no">Phone Number</label>
                <input type="text" class="forminput" name="phone_no" id="phone_no" value="<?php echo htmlspecialchars($contact['phone_no']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="forminput" name="email" id="email" value="<?php echo htmlspecialchars($contact['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="forminput" name="address" id="address" required><?php echo htmlspecialchars($contact['addr']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-success">Update Contact</button>
        </form>
    </div>
</div>

<?php include('include/footer.php'); ?>

