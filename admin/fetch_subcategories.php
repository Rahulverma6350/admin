<?php
include('include/db.php');

// Get the category ID from the request
$categoryId = $_GET['category_id'];

// Query to get subcategories based on the category ID
$sql = "SELECT sub_category FROM product_categories WHERE pc_id = $categoryId";
$res = mysqli_query($conn, $sql);

// Check if the category exists
if (mysqli_num_rows($res) > 0) {
    // Get the subcategories list from the database
    $row = mysqli_fetch_assoc($res);
    $subcategories = explode(", ", $row['sub_category']); // Convert string to array

    // Output each subcategory as an option in the dropdown
    echo '<option value="">Select Sub-category</option>'; // Default option
    foreach ($subcategories as $sub) {
        // Display each subcategory in the select dropdown
        echo '<option value="' . trim($sub) . '">' . trim($sub) . '</option>';
    }
} else {
    // If no subcategories are found for the category
    echo '<option value="">No subcategories found</option>';
}

// Close the database connection
$conn->close();
?>
