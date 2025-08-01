<?php
include 'include/db.php'; // your connection file

$search = $_POST['query'];
$query = "SELECT * FROM products WHERE product_name LIKE '%$search%'";
$result = mysqli_query($conn, $query);

while ($product = mysqli_fetch_assoc($result)) {
    $product_id = $product['p_id'];
    include 'shop_col.php'; // product card
}
?>
