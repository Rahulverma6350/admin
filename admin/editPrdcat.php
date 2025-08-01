<?php
include('include/db.php');

// Check if category ID is passed in the URL
if (isset($_GET['id'])) {
    $categoryId = $_GET['id']; // Get the category ID

    // Fetch the category data based on the ID
    $sql = "SELECT * FROM product_categories WHERE pc_id = $categoryId";
    $res = mysqli_query($conn, $sql);
    $category = mysqli_fetch_assoc($res);
    
    if (!$category) {
        die("Category not found.");
    }

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $categoryName = mysqli_real_escape_string($conn, $_POST['category_name']);
        $subcategories = mysqli_real_escape_string($conn, implode(", ", $_POST['subcategories']));

        // Update the category and subcategories in the database
        $updateSql = "UPDATE product_categories SET category = '$categoryName', sub_category = '$subcategories' WHERE pc_id = $categoryId";
        if (mysqli_query($conn, $updateSql)) {
            echo "Category updated successfully.";
            header("Location: viewPrdcat.php"); // Redirect to the categories list after update
            exit();
        } else {
            echo "Error updating category: " . mysqli_error($conn);
        }
    }
} else {
    die("Category ID not provided.");
}

include('include/header.php');
?>

<div class="mainHeading">
    <h3>Edit Product Category</h3>
</div>

<div class="content">

<div class="mainContent">
    <!-- Edit Form -->
    <form method="POST" action="">
        <div class="form-group">
            <label for="category_name">Category Name</label>
            <input type="text" class="forminput" name="category_name" id="category_name" value="<?php echo htmlspecialchars($category['category']); ?>" required>
        </div>

        <div class="form-group">
            <label for="subcategories">Sub Categories</label>
            <div id="subcategories-container">
                <?php
                // Split subcategories into an array
                $subcategories = explode(", ", $category['sub_category']);
                foreach ($subcategories as $sub) {
                    echo '<input type="text" name="subcategories[]" class="forminput" value="' . htmlspecialchars($sub) . '" required><br>';
                }
                ?>
            </div>
            <button type="button" onclick="addSubcategory()" class="plusbtn mt-2">+</button><br><br>
        </div>

        <button type="submit" class="btn btn-success mt-2">Update Category</button>
    </form>
</div>

</div>

<?php include('include/footer.php'); ?>

<script>
// Function to dynamically add a new subcategory input field
function addSubcategory() {
    var container = document.getElementById("subcategories-container");
    var newInput = document.createElement("input");
    newInput.type = "text";
    newInput.name = "subcategories[]";
    newInput.className = "forminput";
    newInput.required = true;  // Make the input required
    container.appendChild(newInput);
}
</script>
