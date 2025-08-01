<?php
include('include/db.php');

// Get product_id from URL
if (isset($_GET['p_id'])) {
    $productId = $_GET['p_id'];
} else {
    echo "edit id not found";
}

// Fetch product info
$sql = "SELECT * FROM products WHERE p_id = '$productId'";
$res = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($res);

// Fetch categories
$sqlf = "SELECT * FROM product_categories";
$resf = mysqli_query($conn, $sqlf);

// Fetch existing color/size variants
$variantQuery = mysqli_query($conn, "SELECT color, size, quantity FROM product_variants WHERE product_id = '$productId'");
$variants = [];
while ($row = mysqli_fetch_assoc($variantQuery)) {
    $variants[$row['color']][$row['size']] = $row['quantity'];
}



// Fetch distinct color options
$colorQuery = mysqli_query($conn, "SELECT DISTINCT color FROM product_variants");
$colorOptions = [];
while ($row = mysqli_fetch_assoc($colorQuery)) {
    $colorOptions[] = $row['color'];
}


// Fetch distinct size options
$sizeQuery = mysqli_query($conn, "SELECT DISTINCT size FROM product_variants");
$sizeOptions = [];
while ($row = mysqli_fetch_assoc($sizeQuery)) {
    $sizeOptions[] = $row['size'];
}

// Fetch product details from the database
$sql = "SELECT * FROM products WHERE p_id = '$productId'";
$res = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($res);


// Fetch product categories
$sqlf = "SELECT * FROM product_categories";
$resf = mysqli_query($conn, $sqlf);


if (isset($_POST['formsubmit'])) {
    // Collect and sanitize form data
    $productName = $_POST['productName'];
    $prdcatid = $_POST['prdcatid'];
    $subcategory = $_POST['subcategory'];
    $price = $_POST['price'];
    $discountedPrice = $_POST['discountedPrice'];
    $description = $_POST['description'];
    $rating = $_POST['rating'];
    $stockQuantity = $_POST['stockQuantity'];
    $barcode = $_POST['barcode'];
    $sku = $_POST['sku'];
    $vendor = $_POST['vendor'];
    $productType = $_POST['productType'];

    // Handle image uploads
    if ($_FILES['productImg']['name']) {
        $productImg = mysqli_real_escape_string($conn, $_FILES['productImg']['name']);
        $productImgs = $_FILES['productImg']['tmp_name'];
        $folder = "./img/" . $productImg;
        move_uploaded_file($productImgs, $folder);
    } else {
        $productImg = $product['product_image'];
    }

    if ($_FILES['productImgh']['name']) {
        $productImgh = mysqli_real_escape_string($conn, $_FILES['productImgh']['name']);
        $productImghs = $_FILES['productImgh']['tmp_name'];
        $folderh = "./img/" . $productImgh;
        move_uploaded_file($productImghs, $folderh);
    } else {
        $productImgh = $product['hover_image'];
    }

    // Update product data
    $updateProductSql = "UPDATE products SET 
                         product_name = '$productName',
                         category_id = '$prdcatid',
                         subcategory_name = '$subcategory',
                         price = '$price',
                         discounted_price = '$discountedPrice',
                         descr = '$description',
                         rating = '$rating',
                         stock_quantity = '$stockQuantity',
                         barcode = '$barcode',
                         sku = '$sku',
                         vendor = '$vendor',
                         product_type = '$productType',
                         product_image = '$productImg',
                         hover_image = '$productImgh'
                         WHERE p_id = '$productId'";

    if (mysqli_query($conn, $updateProductSql)) {
        // Handle colors update
        if (isset($_POST['colors'])) {
            $colorsJson = json_encode($_POST['colors']);
            $updateColorsSql = "UPDATE product_colors SET colors = '$colorsJson' WHERE product_id = '$productId'";
            mysqli_query($conn, $updateColorsSql);
        }

        // Handle weights update
        $weightsArray = [
            $_POST['weight1'],
            $_POST['weight2'],
            $_POST['weight3']
        ];
        $weightsArray = array_filter($weightsArray);

        if (!empty($weightsArray)) {
            $weightsJson = json_encode($weightsArray);
            $updateWeightsSql = "UPDATE product_weights SET weights = '$weightsJson' WHERE product_id = '$productId'";
            mysqli_query($conn, $updateWeightsSql);
        }

        // ✅ Handle variants update (color + size + quantity)
        if (isset($_POST['variants'])) {
            // Delete old variants
            mysqli_query($conn, "DELETE FROM product_variants WHERE product_id = '$productId'");

            foreach ($_POST['variants'] as $color => $sizes) {
                foreach ($sizes as $size => $data) {
                    if (isset($data['enabled'])) {
                        $quantity = intval($data['quantity']);
                        $colorSafe = mysqli_real_escape_string($conn, $color);
                        $sizeSafe = mysqli_real_escape_string($conn, $size);

                        $insertSql = "INSERT INTO product_variants (product_id, color, size, quantity)
                                      VALUES ('$productId', '$colorSafe', '$sizeSafe', '$quantity')";
                        mysqli_query($conn, $insertSql);
                    }
                }
            }
        }

        // Redirect after update
        header("Location: viewProduct.php");
        exit;
    } else {
        echo "Error updating product: " . mysqli_error($conn);
    }
}


?>

<?php include('include/header.php'); ?>

<div class="mainHeading">
    <h3>Edit Product</h3>
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
                        <img src="./img/<?php echo $product['product_image']; ?>" width="100" />
                    </div>

                    <!-- Product hover Image -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Hover Image</label>
                        <input type="file" name="productImgh">
                        <img src="./img/<?php echo $product['hover_image']; ?>" width="100" />
                    </div>

                    <!-- Product Name -->
                    <div class="col-md-12">
                        <label class="formlabel">Product Name</label>
                        <input type="text" name="productName" class="forminput" value="<?php echo $product['product_name']; ?>">
                    </div>

                    <!-- Product Category (select dropdown) -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Category</label>
                        <select name="prdcatid" class="forminput">
                            <option value="">Select Category</option>
                            <?php while ($row = mysqli_fetch_assoc($resf)) { ?>
                                <option value="<?php echo $row['pc_id']; ?>" <?php echo $product['category_id'] == $row['pc_id'] ? 'selected' : ''; ?>>
                                    <?php echo $row['category']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Product Sub-category -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Sub-category</label>
                        <input type="text" name="subcategory" class="forminput" value="<?php echo $product['subcategory_name']; ?>" />
                    </div>

                    <!-- Price -->
                    <div class="col-md-6">
                        <label class="formlabel">Price</label>
                        <input type="number" name="price" class="forminput" value="<?php echo $product['price']; ?>">
                    </div>

                    <!-- Discounted Price -->
                    <div class="col-md-6">
                        <label class="formlabel">Discounted Price</label>
                        <input type="number" name="discountedPrice" class="forminput" value="<?php echo $product['discounted_price']; ?>">
                    </div>

                    <!-- Description -->
                    <div class="col-md-12">
                        <label class="formlabel">Description</label>
                        <textarea name="description" class="forminput"><?php echo $product['descr']; ?></textarea>
                    </div>

                    <!-- Product Type Dropdown -->
                    <div class="col-md-6 mt-3">
                        <label class="formlabel">Product Variant</label>
                        <select name="productVariant" id="productVariant" class="forminput" required>
                            <option value="grocery" <?= ($product['product_variant'] == 'grocery') ? 'selected' : '' ?>>Grocery/Tools Product</option>
                            <option value="fashion" <?= ($product['product_variant'] == 'fashion') ? 'selected' : '' ?>>Fashion Product</option>
                        </select>
                    </div>

                    <!-- Color Options -->
                    <!-- <?php
                            $colorOptions = ['Red', 'Green', 'Blue', 'Black', 'White', 'Yellow', 'brown', 'orange', 'pink'];
                            $sizeOptions = ['S', 'M', 'L', 'XL', 'XXL'];
                            ?> -->

                    <!-- Container for Dynamic Color + Size Blocks -->
                    <div class="row" id="variantContainer" style="display:none;">
                        <!-- Dynamic blocks will appear here -->
                    </div>


                    <!-- Add Variant Dropdown (Only for Fashion) -->
                    <div class="mb-3 mt-3" id="fashionSection" style="display: none;">
                        <label><strong>Add Color Variant:</strong></label>
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <select id="colorSelector" class="form-select w-50">
                                <option value="">-- Select Color --</option>
                                <?php foreach ($colorOptions as $color): ?>
                                    <option value="<?= $color ?>"><?= ucfirst($color) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-success btn-sm" onclick="addColorBlock()">+ Add</button>
                        </div>
                    </div>

                    <!-- Where variant blocks appear -->
                    <div class="row" id="variantContainer" style="display:none;"></div>

                    <!-- Rating -->
                    <div class="col-md-6">
                        <label class="formlabel">Rating</label>
                        <input type="number" name="rating" class="forminput" step="0.1" min="0" max="5" value="<?php echo $product['rating']; ?>">
                    </div>

                    <!-- Stock Quantity -->
                    <div class="col-md-6">
                        <label class="formlabel">Stock Quantity</label>
                        <input type="number" name="stockQuantity" class="forminput" value="<?php echo $product['stock_quantity']; ?>">
                    </div>

                    <!-- Barcode -->
                    <div class="col-md-6">
                        <label class="formlabel">Barcode</label>
                        <input type="text" name="barcode" class="forminput" value="<?php echo $product['barcode']; ?>">
                    </div>

                    <!-- SKU -->
                    <div class="col-md-6">
                        <label class="formlabel">SKU</label>
                        <input type="text" name="sku" class="forminput" value="<?php echo $product['sku']; ?>">
                    </div>

                    <!-- Vendor -->
                    <div class="col-md-6">
                        <label class="formlabel">Vendor</label>
                        <input type="text" name="vendor" class="forminput" value="<?php echo $product['vendor']; ?>">
                    </div>

                    <!-- Product Type -->
                    <div class="col-md-6">
                        <label class="formlabel">Product Type</label>
                        <input type="text" name="productType" class="forminput" value="<?php echo $product['product_type']; ?>">
                    </div>

                    <!-- Submit Button -->
                    <div class="col-md-12">
                        <button type="submit" class="formbutton mt-4" name="formsubmit">Update Product</button>
                    </div>

                </div>
            </div>

        </form>
    </div>
</div>



<script>
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

    function addColorBlock(preselectedColor = null, prefilledData = {}) {
        const selectedColor = preselectedColor || document.getElementById('colorSelector').value;
        if (!selectedColor) return;

        // Check if this color block already exists
        if (document.querySelector(`[data-color="${selectedColor}"]`)) {
            if (!preselectedColor) alert(`${selectedColor} already added!`);
            return;
        }

        const container = document.getElementById('variantContainer');

        // Start building block
        let html = `
    <div class="col-md-6 mb-4 variant-block" data-color="${selectedColor}">
        <div class="p-3 border rounded shadow-sm bg-light position-relative h-100 mt-2">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" 
                aria-label="Remove" onclick="removeBlock('${selectedColor}')"></button>
            <h6 class="mb-3 text-capitalize">${selectedColor}</h6>
            <input type="hidden" name="colors[]" value="${selectedColor}">`;

        // Loop through sizes
        sizeOptions.forEach(size => {
            const isChecked = prefilledData[size] !== undefined ? 'checked' : '';
            const quantity = prefilledData[size] !== undefined ? prefilledData[size] : 1;

            html += `
        <div class="d-flex align-items-center mb-2">
            <input type="checkbox" name="variants[${selectedColor}][${size}][enabled]" ${isChecked}>
            <label class="ms-2 me-2">${size}</label>
            <div class="qty-group d-flex align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateQty(this, -1)">-</button>
                <input type="number" name="variants[${selectedColor}][${size}][quantity]" 
                    value="${quantity}" min="1" class="form-control mx-1" style="width: 60px;">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateQty(this, 1)">+</button>
            </div>
        </div>`;
        });

        html += `</div></div>`;
        container.insertAdjacentHTML('beforeend', html);

        // Reset dropdown after adding
        if (!preselectedColor) {
            document.getElementById('colorSelector').value = '';
        }
    }
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show fashion sections if needed
        const variantType = document.getElementById('productVariant');
        const fashionSection = document.getElementById('fashionSection');
        const variantContainer = document.getElementById('variantContainer');

        function toggleFashionSection() {
            if (variantType.value === 'fashion') {
                fashionSection.style.display = 'block';
                variantContainer.style.display = 'block';
            } else {
                fashionSection.style.display = 'none';
                variantContainer.style.display = 'none';
            }
        }

        toggleFashionSection();
        variantType.addEventListener('change', toggleFashionSection);

        // Preload existing variants if present
        for (const color in existingVariants) {
            addColorBlock(color, existingVariants[color]);
        }
    });
</script>


<script>
    const sizeOptions = <?= json_encode($sizeOptions) ?>;
    const colorOptions = <?= json_encode($colorOptions) ?>;
    const existingVariants = <?= json_encode($variants) ?>;
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



<?php include('include/footer.php'); ?>