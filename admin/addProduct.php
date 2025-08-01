<?php
include('include/db.php');

// Fetch product categories
$sqlf = "SELECT * FROM product_categories";
$resf = mysqli_query($conn, $sqlf);

// if (isset($_POST['formsubmit'])) {

//     // Sanitize and collect form data
//     $productName = mysqli_real_escape_string($conn, $_POST['productName']);
//     $prdcatid = $_POST['prdcatid'];
//     $subcategoryNam = $_POST['subcategoryname'];
//     $price = $_POST['price'];
//     $discountedPrice = $_POST['discountedPrice'];
//     $description = mysqli_real_escape_string($conn, $_POST['description']);
//     $rating = $_POST['rating'];
//     $stockQuantity = $_POST['stockQuantity'];
//     $barcode = $_POST['barcode'];
//     $sku = $_POST['sku'];
//     $vendor = $_POST['vendor'];
//     $productType = $_POST['productType'];
//     $product_variant = $_POST['productVariant'];
//     $FatalProduct = $_POST['FatalProduct'];

//     // Handle image upload
//     $productImg = mysqli_real_escape_string($conn, $_FILES['productImg']['name']);
//     $productImgs = $_FILES['productImg']['tmp_name'];
//     $folder = "img/" . $productImg;
//     move_uploaded_file($productImgs, $folder);

//     // Handle hover image upload
//     $productImgh = mysqli_real_escape_string($conn, $_FILES['productImgh']['name']);
//     $productImghs = $_FILES['productImgh']['tmp_name'];
//     $folderh = "img/" . $productImgh;
//     move_uploaded_file($productImghs, $folderh);

//     // Insert the product data into the database
//     $sql = "INSERT INTO products (product_name, category_id, subcategory_name, price, discounted_price, descr, rating, stock_quantity, barcode, sku, vendor, product_type, product_image, hover_image, product_variant,FatalProduct) 
//                 VALUES ('$productName', '$prdcatid', '$subcategoryNam', '$price', '$discountedPrice', '$description', '$rating', '$stockQuantity', '$barcode', '$sku', '$vendor', '$productType', '$productImg', '$productImgh', '$product_variant', '$FatalProduct')";

//     $res = mysqli_query($conn, $sql);

//     if ($res) {
//         // Get the last inserted product ID
//         $productId = mysqli_insert_id($conn);

//         if (isset($_POST['variants'])) {
//             foreach ($_POST['variants'] as $color => $sizes) {
//                 foreach ($sizes as $size => $details) {
//                     if (isset($details['enabled'])) {
//                         $quantity = isset($details['quantity']) ? (int)$details['quantity'] : 1;
//                         $color = mysqli_real_escape_string($conn, $color);
//                         $size = mysqli_real_escape_string($conn, $size);

//                         $variantSQL = "INSERT INTO product_variants (product_id, color, size, quantity)
//                                         VALUES ('$productId', '$color', '$size', '$quantity')";
//                         mysqli_query($conn, $variantSQL);
//                     }
//                 }
//             }
//         }
//         // Redirect to view product page
//         header('location: viewProduct.php');
//         exit();
//     } else {
//         echo "Error inserting product.";
//     }
// }

if (isset($_POST['formsubmit'])) {

    // Sanitize and collect form data
    $productName = mysqli_real_escape_string($conn, $_POST['productName']);
    $prdcatid = $_POST['prdcatid'];
    $subcategoryNam = $_POST['subcategoryname'];
    $price = $_POST['price'];
    $discountedPrice = $_POST['discountedPrice'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $rating = $_POST['rating'];
    $stockQuantity = $_POST['stockQuantity'];
    $barcode = $_POST['barcode'];
    $sku = $_POST['sku'];
    $vendor = $_POST['vendor'];
    $productType = $_POST['productType'];
    $product_variant = $_POST['productVariant'];
    $FatalProduct = $_POST['FatalProduct'];

    // Handle image upload
    $productImg = mysqli_real_escape_string($conn, $_FILES['productImg']['name']);
    $productImgs = $_FILES['productImg']['tmp_name'];
    $folder = "img/" . $productImg;
    move_uploaded_file($productImgs, $folder);

    // Handle hover image upload
    $productImgh = mysqli_real_escape_string($conn, $_FILES['productImgh']['name']);
    $productImghs = $_FILES['productImgh']['tmp_name'];
    $folderh = "img/" . $productImgh;
    move_uploaded_file($productImghs, $folderh);

    // Set timezone to India Standard Time (IST)
    date_default_timezone_set('Asia/Kolkata');

    // Get current date and time in 12-hour format with AM/PM
    $submitDate = date("Y-m-d h:i:s A"); // 12-hour format with AM/PM
    echo "Current India Time: " . $submitDate;

    // Insert the product data into the database
    $sql = "INSERT INTO products (product_name, category_id, subcategory_name, price, discounted_price, descr, rating, stock_quantity, barcode, sku, vendor, product_type, product_image, hover_image, product_variant, FatalProduct, submitDate) 
        VALUES ('$productName', '$prdcatid', '$subcategoryNam', '$price', '$discountedPrice', '$description', '$rating', '$stockQuantity', '$barcode', '$sku', '$vendor', '$productType', '$productImg', '$productImgh', '$product_variant', '$FatalProduct', '$submitDate')";



    $res = mysqli_query($conn, $sql);

    if ($res) {
        // Get the last inserted product ID
        $productId = mysqli_insert_id($conn);

        if (isset($_POST['variants'])) {
            foreach ($_POST['variants'] as $color => $sizes) {
                foreach ($sizes as $size => $details) {
                    if (isset($details['enabled'])) {
                        $quantity = isset($details['quantity']) ? (int)$details['quantity'] : 1;
                        $color = mysqli_real_escape_string($conn, $color);
                        $size = mysqli_real_escape_string($conn, $size);

                        $variantSQL = "INSERT INTO product_variants (product_id, color, size, quantity)
                                        VALUES ('$productId', '$color', '$size', '$quantity')";
                        mysqli_query($conn, $variantSQL);
                    }
                }
            }
        }
        // Redirect to view product page
        header('location: viewProduct.php');
        exit();
    } else {
        echo "Error inserting product.";
    }
}

include('include/header.php');
?>
<style>
    .color-box {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 10px;
        background-color: #fafafa;
        margin-bottom: 20px;
    }

    .color-title {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .size-option {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .qty-group input[type='number'] {
        width: 60px;
        text-align: center;
    }
</style>

<div class="mainHeading">
    <h3>Add Products</h3>
</div>

<div class="content">

    <div class="mainContent">
        <form method="POST" class="formcontainer" enctype="multipart/form-data">

            <div class="container">
                <div class="row">

                    <!-- Product Image -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Image</label>
                        <input type="file" name="productImg">
                    </div>

                    <!-- Product hover Image -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Hover Image</label>
                        <input type="file" name="productImgh">
                    </div>

                    <!-- Product Name -->
                    <div class="col-md-12">
                        <label class="formlabel">Product Name</label>
                        <input type="text" name="productName" class="forminput">
                    </div>

                    <!-- Product Category (select dropdown) -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Category</label>
                        <select name="prdcatid" id="categorySelect" class="forminput">
                            <option value="">Select Category</option>
                            <?php while ($row = mysqli_fetch_assoc($resf)) { ?>
                                <option value="<?php echo $row['pc_id'] ?>"><?php echo $row['category'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Product Sub-category (select dropdown) -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Sub-category</label>
                        <select name="subcategoryname" id="subcategorySelect" class="forminput" readonly>
                            <option value="">Select Sub-category</option>
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="col-md-6">
                        <label class="formlabel">Price</label>
                        <input type="number" name="price" class="forminput">
                    </div>

                    <!-- Discounted Price -->
                    <div class="col-md-6">
                        <label class="formlabel">Discounted Price</label>
                        <input type="number" name="discountedPrice" class="forminput">
                    </div>

                    <!-- Description -->
                    <div class="col-md-12">
                        <label class="formlabel">Description</label>
                        <textarea name="description" class="forminput"></textarea>
                    </div>
                    <!-- product_variant  -->

                    <!-- Product Type Dropdown -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Variant</label>
                        <select name="productVariant" id="productVariant" class="forminput" required>
                            <option value="">Select Variant</option>
                            <option value="grocery">Grocery/Tools Product</option>
                            <option value="fashion">Fashion Product</option>
                        </select>
                    </div>

                    <!-- Color Options -->
                    <?php
                    $colorOptions = ['Red', 'Green', 'Blue', 'Black', 'White', 'Yellow', 'brown', 'orange', 'pink'];
                    $sizeOptions = ['S', 'M', 'L', 'XL', 'XXL'];
                    ?>

                    <!-- Container for Dynamic Color + Size Blocks -->
                    <div class="row" id="variantContainer" style="display:none;">
                        <!-- Dynamic blocks will appear here -->
                    </div>

                    <!-- Dropdown to Add Color -->
                    <div class="mb-3 mt-3" id="fashionSection" style="display: none; margin-top: 15px;">
                        <label><strong>Add Color Variant:</strong></label>
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <select id="colorSelector" class="form-select w-50">
                                <option value="">-- Select Color --</option>
                                <?php foreach ($colorOptions as $color): ?>
                                    <option value="<?= $color ?>"><?= $color ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-success btn-sm" onclick="addColorBlock()">+ Add</button>
                        </div>
                    </div>

                    <input type="hidden" name="product_id" value="<?= $row['p_id']; ?>">

                    <!-- fatal product -->
                    <div class="col-md-6">
                        <label class="formlabel">Fatal Product</label>
                        <select name="FatalProduct" id="FatalProduct" class="forminput" required>
                            <option value="0">Select fatal</option>
                            <option value="1">Fatal Product</option>
                        </select>
                    </div>


                    <!-- Rating -->
                    <div class="col-md-6">
                        <label class="formlabel">Rating</label>
                        <input type="number" name="rating" class="forminput" step="0.1" min="0" max="5">
                    </div>

                    <!-- Quantity -->
                    <div class="col-md-6">
                        <label class="formlabel">Stock Quantity</label>
                        <input type="number" name="stockQuantity" class="forminput" min="1">
                    </div>

                    <!-- Barcode -->
                    <div class="col-md-6">
                        <label class="formlabel">Barcode</label>
                        <input type="text" name="barcode" class="forminput">
                    </div>

                    <!-- SKU -->
                    <div class="col-md-6">
                        <label class="formlabel">SKU</label>
                        <input type="text" name="sku" class="forminput">
                    </div>

                    <!-- Vendor -->
                    <div class="col-md-6">
                        <label class="formlabel">Vendor</label>
                        <input type="text" name="vendor" class="forminput">
                    </div>

                    <!-- Product Type -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Type</label>
                        <input type="text" name="productType" class="forminput">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="formbutton mt-4" name="formsubmit">Submit</button>

                </div>
            </div>
        </form>

        <?php include('include/footer.php'); ?>

        <script>
            // AJAX request to fetch subcategories based on selected category
            $('#categorySelect').change(function() {
                var categoryId = $(this).val(); // Get selected category ID
                if (categoryId) {
                    // Send AJAX request to fetch subcategories
                    $.ajax({
                        url: 'fetch_subcategories.php', // Server-side page to handle the request
                        type: 'GET', // Method
                        data: {
                            category_id: categoryId
                        }, // Send the selected category ID
                        success: function(response) {
                            // Clear previous options and append new ones
                            $('#subcategorySelect').html(response);
                        }
                    });
                } else {
                    // If no category is selected, reset subcategory options
                    $('#subcategorySelect').html('<option value="">Select Sub-category</option>');
                }
            });
        </script>

        <!-- show and hide color size  -->
        <script>
            // Category IDs where size & color are required (update IDs accordingly)
            const apparelCategories = [8, 9]; // Example: 1 = T-shirts, 2 = Shirts, 3 = Jeans etc.

            $('#categorySelect').change(function() {
                const selectedCat = parseInt($(this).val());

                // Show/hide size & color sections
                // if (apparelCategories.includes(selectedCat)) {
                //     $('#sizeOptions').show();
                //     $('#colorOptions').show();
                // } else {
                //     $('#sizeOptions').hide();
                //     $('#colorOptions').hide();
                // }

                // Load subcategories as before
                if (selectedCat) {
                    $.ajax({
                        url: 'fetch_subcategories.php',
                        type: 'GET',
                        data: {
                            category_id: selectedCat
                        },
                        success: function(response) {
                            $('#subcategorySelect').html(response);
                        }
                    });
                } else {
                    $('#subcategorySelect').html('<option value="">Select Sub-category</option>');
                }
            });

            // Hide by default until user selects a category
            $('#sizeOptions, #colorOptions').hide();
        </script>

        <script>
            const sizeOptions = <?= json_encode($sizeOptions) ?>;

            function updateQty(btn, delta) {
                const input = btn.parentElement.querySelector('input[type=number]');
                let value = parseInt(input.value) + delta;
                if (value < 1) value = 1;
                input.value = value;
            }

            function removeBlock(color) {
                const block = document.querySelector(`[data-color="${color}"]`);
                if (block) block.remove();
            }

            function addColorBlock() {
                const selectedColor = document.getElementById('colorSelector').value;
                if (!selectedColor) return;

                if (document.querySelector(`[data-color="${selectedColor}"]`)) {
                    alert(selectedColor + ' already added!');
                    return;
                }

                const container = document.getElementById('variantContainer');

                let html = `
            <div class="col-md-6 mb-4 variant-block" data-color="${selectedColor}">
                <div class="p-3 border rounded shadow-sm bg-light position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2" aria-label="Remove" onclick="removeBlock('${selectedColor}')"></button>
                    <h6 class="mb-3">${selectedColor}</h6>
                    <input type="hidden" name="colors[]" value="${selectedColor}">`;

                sizeOptions.forEach(size => {
                    html += `
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" name="variants[${selectedColor}][${size}][enabled]">
                        <label class="ms-2 me-2">${size}</label>
                        <div class="qty-group d-flex align-items-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateQty(this, -1)">-</button>
                            <input type="number" name="variants[${selectedColor}][${size}][quantity]" value="1" min="1" class="form-control mx-1" style="width: 60px;">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateQty(this, 1)">+</button>
                        </div>
                    </div>`;
                });

                html += `</div></div>`;

                container.insertAdjacentHTML('beforeend', html);
                document.getElementById('colorSelector').value = '';
            }
        </script>

        <!-- product variant  -->
        <script>
            document.getElementById('productVariant').addEventListener('change', function() {
                const selectedValue = this.value;
                const variantOptions = document.getElementById('variantContainer');

                if (selectedValue === 'fashion') {
                    variantOptions.style.display = 'block';
                } else {
                    variantOptions.style.display = 'none';
                }
            });
        </script>


        <script>
            document.getElementById("productVariant").addEventListener("change", function() {
                const selected = this.value;
                const fashionSection = document.getElementById("fashionSection");

                if (selected === "fashion") {
                    fashionSection.style.display = "block";
                } else {
                    fashionSection.style.display = "none";
                }
            });
        </script>

        <script>
$(document).ready(function () {
    // When discounted price input changes
    $('input[name="discountedPrice"]').on('input', function () {
        let price = parseFloat($('input[name="price"]').val());
        let discountedPrice = parseFloat($(this).val());

        if (!isNaN(price) && !isNaN(discountedPrice)) {
            if (discountedPrice > price) {
                alert('Discounted Price cannot be greater than Original Price!');
                $(this).val(''); // clear the field
            }
        }
    });

    // Also handle when original price is updated
    $('input[name="price"]').on('input', function () {
        let price = parseFloat($(this).val());
        let discountedPrice = parseFloat($('input[name="discountedPrice"]').val());

        if (!isNaN(price) && !isNaN(discountedPrice)) {
            if (discountedPrice > price) {
                alert('Discounted Price cannot be greater than Original Price!');
                $('input[name="discountedPrice"]').val('');
            }
        }
    });
});
</script>
