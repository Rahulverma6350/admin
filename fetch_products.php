<?php
session_start();
include('include/db.php');

if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $categoryId = mysqli_real_escape_string($conn, $_GET['category_id']);

    // Fetch products for the selected category
    // Assuming you have a database connection established
    $query = "SELECT * FROM products WHERE category_id = ? AND status = 1"; // Change 'status' to your

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
?>

    <?php
    if ($result && mysqli_num_rows($result) > 0) {
        while ($product = mysqli_fetch_assoc($result)) {
            $product_id = $product['p_id']; // Fixing session overwriting issue
    ?>


            <style>
                .color-box {
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    border: 2px solid transparent;
                    cursor: pointer;
                    transition: 0.3s;
                }

                .color-input:checked+.color-box {
                    border: 3px solid orange;
                }


                .product__variant--list {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }

                .quantity__box {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0px !important;
                    /* Adjust spacing between buttons and input */
                }


                .product__items--content {
                    background: #f7f7f7;
                }

                .wishlist-active {
                    background: red;
                }
            </style>
            <div class="col mb-30">
                <div class="product__items product-item" data-product-id="<?= $product_id; ?>">
                    <div class="product__items--thumbnail">
                        <a class="product__items--link" href="product-details.php?id=<?= $product_id; ?>">
                            <img class="product__items--img product__primary--img" src="admin/img/<?= $product['product_image']; ?>" alt="product-img">
                            <img class="product__items--img product__secondary--img" src="admin/img/<?= $product['hover_image']; ?>" alt="product-img">
                        </a>
                        <!-- <div class="product__badge">
                                <span class="product__badge--items sale">New</span>
                            </div> -->
                        <ul class="product__items--action d-flex justify-content-center">
                            <li class="product__items--action__list">
                                <a class="product__items--action__btn quick-view-btn" data-product-id="<?= $product_id; ?>" href="product-details.php?id=<?= $product_id; ?>">
                                    <svg class="product__items--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="20.51" height="19.443" viewBox="0 0 512 512">
                                        <path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-width="32"></path>
                                        <circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-width="32"></circle>
                                    </svg>
                                    <span class="visually-hidden">Quick View</span>
                                </a>
                            </li>
                            <li class="product__items--action__list wishlist-btn"
                                data-prd-id="<?= $product_id; ?>"
                                data-has-variant="<?= $variantData['total'] > 0 ? '1' : '0'; ?>"
                                data-product-id="<?= $product_id; ?>">
                                <a class="product__items--action__btn <?php
                                                                        $getPrdId = $product_id;
                                                                        if (isset($_SESSION['user_id']) && !empty($_SESSION['wishlist_added_' . $getPrdId])) {
                                                                            echo 'wishlist-active';
                                                                        }
                                                                        ?>">
                                    ❤️ <span class="visually-hidden">Wishlist</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="product__items--content text-center">

                        <!-- Color Selection -->
                        <?php
                        // Check if variants exist for this product
                        $variantCheck = mysqli_query($conn, "SELECT COUNT(*) as total FROM product_variants WHERE product_id = '$product_id'");
                        $variantData = mysqli_fetch_assoc($variantCheck);

                        if ($variantData['total'] > 0) {
                            // If variant exists, show color and size dropdown
                        ?>
                            <!-- Color Selection -->
                            <fieldset class="variant__input--fieldset">
                                <legend>Color:</legend>
                                <ul class="variant__color d-flex" id="product_color">
                                    <?php
                                    $colorQuery = mysqli_query($conn, "SELECT DISTINCT color FROM product_variants WHERE product_id = '$product_id'");
                                    while ($row = mysqli_fetch_assoc($colorQuery)) {
                                        $color = $row['color'];
                                    ?>
                                        <li class="variant__color--list">
                                            <input id="color<?= $product_id . $color; ?>" name="color_<?= $product_id; ?>" type="radio" class="color-input" value="<?= $color; ?>" data-product-id="<?= $product_id; ?>" style="display: none;">
                                            <label for="color<?= $product_id . $color; ?>" class="color-label color-box"
                                                style="background-color: <?= strtolower($color); ?>; width: 30px; height: 30px;
                    display: inline-block; border-radius: 50%; border: 2px solid transparent;
                    cursor: pointer; transition: border 0.3s ease;">
                                            </label>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </fieldset>

                            <!-- Size Dropdown -->
                            <fieldset class="variant__input--fieldset">
                                <legend>Size:</legend>
                                <select class="size-select" id="sizeDropdown<?= $product_id; ?>" data-product-id="<?= $product_id; ?>">
                                    <option value="">Select Size</option>
                                </select>
                            </fieldset>
                        <?php } ?>
                        <!-- Quantity Selection -->
                        <div class="product__variant--list quantity d-flex align-items-center mb-20" data-product-id="<?= $product_id; ?>">
                            <div class="quantity__box">
                                <button type="button" class="quantity__value decrease">-</button>
                                <label>
                                    <input type="number" class="quantity__number" value="1" min="1">
                                </label>
                                <button type="button" class="quantity__value increase">+</button>
                            </div>
                        </div>
                        <!-- simple stock m out of stock hone k baad  -->
                        <div class="stock-status text-danger">
                            <?php if ($product['stock_quantity'] == 0): ?>
                                Out of Stock
                            <?php endif; ?>
                        </div>

                        <!-- yha tk  -->

                        <h3 class="product__items--content__title h4">
                            <a href="product-details.php?id=<?= $product_id; ?>"><?= $product['product_name']; ?></a>
                        </h3>
                        <div class="product__items--price">
                            <span class="current__price">₹ <?= $product['discounted_price']; ?></span>
                            <span class="old__price">₹ <?= $product['price']; ?></span>
                        </div>

                        <!-- Add to Cart Button -->

                        <?php if ($product['stock_quantity'] > 0): ?>
                            <button
                                class="product__items--action__cart--btn primary__btn add-to-cart-btn"
                                data-product-id="<?= $product_id; ?>"
                                id="add-to-cart-btn-<?= $product_id; ?>">
                                Add to Cart
                            </button>
                        <?php endif; ?>


                    </div>
                </div>
            </div>
    <?php }
    } else {
        echo '<p>No products available in this category.</p>';
    }
    ?>
<?php
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // :shopping_trolley: Update Cart Count
        function updateCartCount() {
            $.ajax({
                url: "cart_count.php",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#cartCountValue").text(response.count);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Cart Count Error:", error);
                }
            });
        }
        updateCartCount();
        // :art: Color Select Highlight
        $(document).on("click", ".color-label", function() {
            $(this).closest('.product__items').find(".color-label").css("border", "2px solid transparent");
            $(this).css("border", "3px solid orange");
        });
        // :arrows_counterclockwise: Load Sizes Based on Color
        $(document).on('change', '.color-input', function() {
            var selectedColor = $(this).val();
            var productId = $(this).data('product-id');
            var productBox = $(this).closest(".product__items");
            var sizeDropdown = $('#sizeDropdown' + productId);
            productBox.find(".size-select").val("");
            productBox.find(".quantity__number").val(1).removeAttr("max");
            $.ajax({
                url: 'get_sizes_by_color.php',
                type: 'POST',
                data: {
                    color: selectedColor,
                    product_id: productId
                },
                success: function(response) {
                    sizeDropdown.html(response);
                },
                error: function() {
                    sizeDropdown.html('<option value="">Error loading sizes</option>');
                }
            });
        });
        // :1234: Handle Quantity Buttons
        $(document).on("click", ".quantity__value", function() {
            var $btn = $(this);
            var $variantBox = $btn.closest(".product__variant--list");
            var $productBox = $btn.closest(".product__items");
            var $input = $variantBox.find(".quantity__number");
            var productId = $variantBox.data("product-id");
            var currentValue = parseInt($input.val()) || 1;
            var min = parseInt($input.attr("min")) || 1;
            var selectedColor = $productBox.find(".color-input:checked").val();
            var selectedSize = $productBox.find(".size-select").val();

            function updateInput(stock) {
                $input.attr("max", stock);
                if (stock === 0) {
                    $input.val(0).prop("disabled", true);
                    $btn.prop("disabled", true);
                    $("#stock-status-" + productId).html("<span class='text-danger'>Out of Stock</span>");
                    $("#add-to-cart-btn-" + productId).hide();
                    return;
                }
                if ($btn.hasClass("increase")) {
                    if (currentValue < stock) {
                        $input.val(currentValue + 1);
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: `Only ${stock} item(s) available`,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                }
                if ($btn.hasClass("decrease") && currentValue > min) {
                    $input.val(currentValue - 1);
                }
            }
            if (selectedColor && selectedSize) {
                $.post("get_quantity.php", {
                    product_id: productId,
                    color: selectedColor,
                    size: selectedSize
                }, function(stock) {
                    updateInput(parseInt(stock));
                });
            } else {
                $.post("check_stock.php", {
                    product_id: productId
                }, function(stock) {
                    updateInput(parseInt(stock));
                });
            }
        });
        // :shopping_trolley: Add to Cart
        let cartLocked = false;
        $(document).on('click', '.add-to-cart-btn', function(e) {
            e.preventDefault();
            if (cartLocked) return;
            cartLocked = true;
            setTimeout(() => cartLocked = false, 2000);
            var $btn = $(this);
            var $productBox = $btn.closest(".product__items");
            var productId = $btn.data("product-id");
            var quantity = parseInt($productBox.find(".quantity__number").val()) || 1;
            var hasVariants = $productBox.find(".color-input").length > 0;
            if (hasVariants) {
                var selectedColor = $productBox.find(".color-input:checked").val();
                var selectedSize = $productBox.find(".size-select").val();
                if (!selectedColor) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please select a color',
                        toast: true,
                        position: 'top-end',
                        timer: 3000
                    });
                    return;
                }
                if (!selectedSize) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please select a size',
                        toast: true,
                        position: 'top-end',
                        timer: 3000
                    });
                    return;
                }
            }
            var color = $productBox.find(".color-input:checked").val() || '';
            var size = $productBox.find(".size-select").val() || '';
            console.log("Add to Cart:", {
                product_id: productId,
                quantity,
                color,
                size
            });
            $.ajax({
                url: "cartscript.php",
                type: "POST",
                data: {
                    product_id: productId,
                    quantity: quantity,
                    color: color,
                    size: size
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart',
                        text: 'Product added successfully!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    updateCartCount();
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Add to Cart Failed',
                        toast: true,
                        position: 'top-end',
                        timer: 3000
                    });
                }
            });
        });
    });
</script>