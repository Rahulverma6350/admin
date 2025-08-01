<?php
include('include/db.php');
$id = isset($_GET['review_id']) ? intval($_GET['review_id']) : 0;
$current_status = isset($_GET['status']) ? intval($_GET['status']) : -1;
if ($id > 0 && ($current_status === 0 || $current_status === 1)) {
    $new_status = ($current_status == 1) ? 0 : 1;
    $updateSql = "UPDATE products_review SET status = $new_status WHERE id = $id";
    $updateResult = mysqli_query($conn, $updateSql);
    if ($updateResult) {
        header("Location: viewreview.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    echo "Invalid parameters.";
}
?>