<?php
include 'include/db.php';

$dateLimit = date('Y-m-d H:i:s', strtotime('-7 days'));

$sql = "SELECT * FROM products 
        WHERE submitDate >= '$dateLimit' 
        AND status = 1 
        ORDER BY submitDate DESC 
        LIMIT 10";

$res = mysqli_query($conn, $sql);

while ($product = mysqli_fetch_assoc($res)) {
    $product_id = $product['p_id'];
?>
<style>
    .wishlist-active{
        background:red;
}
</style>
    <div class="col mb-30">
        <div class="product__items product-item" data-product-id="<?= $product_id; ?>">
            <div class="product__items--thumbnail">
                <a class="product__items--link" href="product-details.php?id=<?= $product_id; ?>">
                    <img class="product__items--img product__primary--img" src="admin/img/<?= $product['product_image']; ?>" alt="product-img">
                    <img class="product__items--img product__secondary--img" src="admin/img/<?= $product['hover_image']; ?>" alt="product-img">
                </a>
                <div class="product__badge">
                    <span class="product__badge--items sale">New</span>
                </div>
                <ul class="product__items--action d-flex justify-content-center">
                    <li class="product__items--action__list">
                        <a class="product__items--action__btn quick-view-btn" data-product-id="<?= $product_id; ?>" href="product-details.php?id=<?= $product_id; ?>">
                            👁
                        </a>
                    </li>
                    <li class="product__items--action__list wishlist-btn" 
                        data-prd-id="<?= $product_id; ?>" 
                        data-has-variant="<?= $variantData['total'] > 0 ? '1' : '0'; ?>" 
                        data-product-id="<?= $product_id; ?>">
                        <?php
    $getPrdId = $product_id;
    $isInWishlist = false;
    if (isset($_SESSION['user_id']) && !empty($_SESSION['wishlist_added' . $getPrdId])) {
        $isInWishlist = true;
    }
    ?>
                        <a class="product__items--action__btn<?= $isInWishlist ? ' wishlist-active' : ''; ?>">
                            ❤️
                        </a>
                    </li>
                </ul>
            </div>

            <div class="product__items--content text-center">

                <!-- Variant check -->
                <?php
                $variantCheck = mysqli_query($conn, "SELECT COUNT(*) as total FROM product_variants WHERE product_id = '$product_id'");
                $variantData = mysqli_fetch_assoc($variantCheck);

                if ($variantData['total'] > 0) {
                ?>
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
                                        cursor: pointer;">
                                    </label>
                                </li>
                            <?php } ?>
                        </ul>
                    </fieldset>

                    <fieldset class="variant__input--fieldset">
                        <legend>Size:</legend>
                        <select class="size-select" id="sizeDropdown<?= $product_id; ?>" data-product-id="<?= $product_id; ?>">
                            <option value="">Select Size</option>
                        </select>
                    </fieldset>
                <?php } ?>

                <!-- Quantity -->
                <div class="product__variant--list quantity d-flex align-items-center mb-20" data-product-id="<?= $product_id; ?>">
                    <div class="quantity__box">
                        <button type="button" class="quantity__value decrease">-</button>
                        <label>
                            <input type="number" class="quantity__number" value="1" min="1">
                        </label>
                        <button type="button" class="quantity__value increase">+</button>
                    </div>
                </div>

                <div class="stock-status text-danger">
                    <?php if ($product['stock_quantity'] == 0): ?>
                        Out of Stock
                    <?php endif; ?>
                </div>

                <h3 class="product__items--content__title h4">
                    <a href="product-details.php?id=<?= $product_id; ?>"><?= $product['product_name']; ?></a>
                </h3>
                <div class="product__items--price">
                    <span class="current__price">₹ <?= $product['discounted_price']; ?></span>
                    <span class="old__price">₹ <?= $product['price']; ?></span>
                </div>

                <?php if($product['stock_quantity'] > 0): ?>
                    <button class="product__items--action__cart--btn primary__btn add-to-cart-btn" 
                        data-product-id="<?= $product_id; ?>" 
                        id="add-to-cart-btn-<?= $product_id; ?>">
                        Add to Cart
                    </button>
                <?php endif; ?>

            </div>
        </div>
    </div>
<?php } ?>



   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
<script>
    $(document).ready(function() {
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

        // Quantity Increase/Decrease
        $(".color-label").click(function() {
            $(".color-label").css("border", "2px solid transparent");
            $(this).css("border", "3px solid orange");
        });

        // Add to Cart AJAX
    $(document).on("click", ".add-to-cart-btn", function(e) {
    e.preventDefault();

    var productBox = $(this).closest(".product__items");
    var productId = $(this).data("product-id");
    var quantity = productBox.find(".quantity__number").val();
    var hasVariants = productBox.find(".color-input").length > 0;

    if (hasVariants) {
        var selectedColor = productBox.find(".color-input:checked").val();
        var selectedSize = productBox.find(".size-select").val();

        if (!selectedColor) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: 'Please select color first',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
            return;
        }

        if (!selectedSize) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: 'Please select size',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
            return;
        }
    }

    var color = productBox.find(".color-input:checked").val() || '';
    var size = productBox.find(".size-select").val() || '';

    $.ajax({
        url: "cartscript.php",
        type: "POST",
        data: {
            product_id: productId,
            quantity: quantity,
            size: size,
            color: color
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Added to Cart',
                text: 'Product added to cart successfully!',
                showConfirmButton: false,
                timer: 2000
            });
            updateCartCount();
        },
        error: function() {
            alert("Error adding product to cart.");
        }
    });
});


    });
</script>

<!-- get sizes by colour  -->
<script>
$(document).ready(function() {

// 🔄 Color change → fetch sizes
$(document).on('change', '.color-input', function () {
    var selectedColor = $(this).val();
    var productId = $(this).data('product-id');
    var productBox = $(this).closest(".product__items");
    var sizeDropdown = $('#sizeDropdown' + productId);

    // Reset size and quantity
    productBox.find(".size-select").val(""); // Reset size
    productBox.find(".quantity__number").val(1).removeAttr("max"); // Reset quantity

    // Fetch sizes
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

// 🔄 Size change → fetch available quantity
$(".quantity__value").click(function () {
    var $btn = $(this);
    var $parent = $btn.closest(".product__variant--list");
    var $input = $parent.find(".quantity__number");
    var currentValue = parseInt($input.val());
    var min = parseInt($input.attr("min")) || 1;
    var productId = $parent.data("product-id");

    var $productBox = $btn.closest(".product__items"); // common container
    var selectedColor = $productBox.find(".color-input:checked").val();
    var selectedSize = $productBox.find(".size-select").val();

    // ----------- IF VARIANT SELECTED ----------- 
    if (selectedColor && selectedSize) {
        $.ajax({
            url: "get_quantity.php",
            type: "POST",
            data: {
                product_id: productId,
                color: selectedColor,
                size: selectedSize
            },
            success: function (stock) {
                stock = parseInt(stock);
                $input.attr("max", stock);

                if ($btn.hasClass("increase")) {
                    if (currentValue < stock) {
                        $input.val(currentValue + 1);
                    } else {
                        $input.val(stock);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: `Only ${stock} item(s) available in stock`,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                    }
                }

                if ($btn.hasClass("decrease") && currentValue > min) {
                    $input.val(currentValue - 1);
                }
            }
        });
    }

        // ----------- IF SIMPLE PRODUCT -----------
        else {
            $.ajax({
                url: 'check_stock.php',
                type: 'POST',
                data: { product_id: productId },

                success: function (stock) {
    stock = parseInt(stock);
    $input.attr("max", stock);

    // Yeh condition sabse pehle laga
    if (stock === 0) {
        $input.val(0);
        $input.prop("disabled", true);
        $btn.prop("disabled", true);
        $("#stock-status-" + productId).html("<span class='text-danger'>Out of Stock</span>");
        return;
    } else {
        // stock > 0 ho to enable bhi kar de
        $input.prop("disabled", false);
        $btn.prop("disabled", false);
        $("#stock-status-" + productId).html(""); // Clear previous message
    }

    if (stock === 0) {
    $("#add-to-cart-btn-" + productId).hide(); // hide the button
    $("#stock-status-" + productId).html("<span class='text-danger'>Out of Stock</span>");
} else {
    $("#add-to-cart-btn-" + productId).show(); // show it again if restocked
    $("#stock-status-" + productId).html("");
}

    // Increase quantity logic
    if ($btn.hasClass("increase")) {
        if (currentValue < stock) {
            $input.val(currentValue + 1);
        } else {
            $input.val(stock);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `Only ${stock} item(s) available in stock`,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        }
    }

    // Decrease quantity logic
    if ($btn.hasClass("decrease") && currentValue > min) {
        $input.val(currentValue - 1);
    }
}

            });
        }
    });
});

</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>






