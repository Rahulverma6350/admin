<?php
include 'include/db.php';

$product_id = $_GET['product_id'];
$selected_color = $_GET['color'];
$exclude_size = $_GET['exclude_size'];
$ordered_color = $_GET['ordered_color'];

// If the selected color is the same as the one the user ordered,
// we exclude the size they already bought.
// Otherwise, show all sizes for that color.

if ($selected_color === $ordered_color) {
    $sql = "SELECT DISTINCT size FROM product_variants 
            WHERE product_id = '$product_id' 
              AND color = '$selected_color' 
              AND size != '$exclude_size' 
              AND quantity > 0";
} else {
    $sql = "SELECT DISTINCT size FROM product_variants 
            WHERE product_id = '$product_id' 
              AND color = '$selected_color' 
              AND quantity > 0";
}

$result = mysqli_query($conn, $sql);

$sizes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $sizes[] = $row['size'];
}

echo json_encode($sizes);
?>
