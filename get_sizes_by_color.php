<?php
include('include/db.php');  // Include your DB connection here

$color = $_POST['color'];
$product_id = $_POST['product_id'];

// Fetch sizes and their stock for the selected color
$query = "SELECT size, quantity FROM product_variants WHERE product_id = '$product_id' AND color = '$color'";
$result = mysqli_query($conn, $query);

$sizes = '';
while ($row = mysqli_fetch_assoc($result)) {
    $size = $row['size'];
    $quantity = $row['quantity'];
    
    // Disable size if stock is 0
    $disabled = ($quantity == 0) ? 'disabled' : '';
    
    $sizes .= "<option value='$size' $disabled>$size ($quantity available)</option>";
}

echo $sizes;
?>
