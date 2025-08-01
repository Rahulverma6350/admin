<?php
session_start();
include('include/db.php');

// Generate session ID for guest users
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}
$sessionId = $_SESSION['session_id'];
$responseMsg = "";

// ✅ Handle Add/Remove Wishlist with color & size
if (isset($_GET['prductId'])) {
    $getPrdId = $_GET['prductId'];
    $color = $_GET['color'] ?? '';
    $size = $_GET['size'] ?? '';

    if (isset($_SESSION['user_id'])) {
        // Logged-in user
        $userId = $_SESSION['user_id'];
        $check = mysqli_query($conn, "SELECT * FROM wishlist WHERE product_id = '$getPrdId' AND user_id = '$userId' AND color = '$color' AND size = '$size'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO wishlist (product_id, user_id, color, size) VALUES ('$getPrdId', '$userId', '$color', '$size')");
            $responseMsg = "Product added to wishlist!";
            $_SESSION['wishlist_added' . $getPrdId] = true;
        } else {
            mysqli_query($conn, "DELETE FROM wishlist WHERE product_id = '$getPrdId' AND user_id = '$userId' AND color = '$color' AND size = '$size'");
            $responseMsg = "Product removed from wishlist!";
            $_SESSION['wishlist_added' . $getPrdId] = false;
        }
    } else {
        // Guest user
        $check = mysqli_query($conn, "SELECT * FROM wishlist WHERE product_id = '$getPrdId' AND session_id = '$sessionId' AND color = '$color' AND size = '$size'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO wishlist (product_id, session_id, color, size) VALUES ('$getPrdId', '$sessionId', '$color', '$size')");
            $responseMsg = "Product added to wishlist!";
        } else {
            mysqli_query($conn, "DELETE FROM wishlist WHERE product_id = '$getPrdId' AND session_id = '$sessionId' AND color = '$color' AND size = '$size'");
            $responseMsg = "Product removed from wishlist!";
        }
    }
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    echo $responseMsg;
    exit;
} else {
    include('header.php');
    // ... render page normally
}

// ✅ Fetch Wishlist Products with Product Details
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $sql = "SELECT wishlist.*, products.*, pv.color AS variant_color, pv.size AS variant_size 
            FROM wishlist 
            INNER JOIN products ON wishlist.product_id = products.p_id 
            LEFT JOIN product_variants pv ON pv.product_id = wishlist.product_id 
                AND pv.color = wishlist.color AND pv.size = wishlist.size 
            WHERE wishlist.user_id = '$userId'";
} else {
    $sql = "SELECT wishlist.*, products.*, pv.color AS variant_color, pv.size AS variant_size 
            FROM wishlist 
            INNER JOIN products ON wishlist.product_id = products.p_id 
            LEFT JOIN product_variants pv ON pv.product_id = wishlist.product_id 
                AND pv.color = wishlist.color AND pv.size = wishlist.size 
            WHERE wishlist.session_id = '$sessionId'";
    $_SESSION['wishlist_added_' . $getPrdId] = false;
}

$res = mysqli_query($conn, $sql);
?>

<main class="main__content_wrapper">


    <!-- Start breadcrumb section -->
    <section class="breadcrumb__section breadcrumb__bg">
        <div class="container">
            <div class="row row-cols-1">
                <div class="col">
                    <div class="breadcrumb__content">
                        <h1 class="breadcrumb__content--title text-white mb-10">Wishlist</h1>
                        <ul class="breadcrumb__content--menu d-flex">
                            <li class="breadcrumb__content--menu__items"><a class="text-white" href="index.php">Home</a></li>
                            <li class="breadcrumb__content--menu__items"><span class="text-white">Wishlist</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End breadcrumb section -->

    <!-- cart section start -->
    <section class="cart__section section--padding">
        <div class="container">
            <div class="cart__section--inner">
                <form action="#">
                    <h2 class="cart__title mb-40">Wishlist</h2>
                    <div class="cart__table">

                        <!-- ✅ Wishlist Product Table -->
                        <table class="cart__table--inner">
                            <thead class="cart__table--header">
                                <tr class="cart__table--header__items">
                                    <th class="cart__table--header__list">Product</th>
                                    <th class="cart__table--header__list">Price</th>
                                    <th class="cart__table--header__list text-center">STOCK STATUS</th>
                                    <th class="cart__table--header__list text-right">ADD TO CART</th>
                                </tr>
                            </thead>
                            <tbody class="cart__table--body" id="products">

                                <?php while ($fetPrd = mysqli_fetch_assoc($res)) { ?>
                                    <tr class="cart__table--body__items" id="product-<?php echo $fetPrd['p_id'] ?>">
                                        <td class="cart__table--body__list">
                                            <div class="cart__product d-flex align-items-center">
                                                <button
                                                    class="cart__remove--btn removeWishlist"
                                                    data-prd-id="<?php echo $fetPrd['p_id'] ?>"
                                                    data-color="<?php echo $fetPrd['color'] ?>"
                                                    data-size="<?php echo $fetPrd['size'] ?>"
                                                    aria-label="remove"
                                                    type="button"
                                                    id="product-<?php echo $fetPrd['p_id'] . '-' . $fetPrd['color'] . '-' . $fetPrd['size']; ?>">

                                                    <svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 24 24" width="16px" height="16px">
                                                        <path d="M 4.7070312 3.2929688 L 3.2929688 4.7070312 L 10.585938 12 L 3.2929688 19.292969 L 4.7070312 20.707031 L 12 13.414062 L 19.292969 20.707031 L 20.707031 19.292969 L 13.414062 12 L 20.707031 4.7070312 L 19.292969 3.2929688 L 12 10.585938 L 4.7070312 3.2929688 z"></path>
                                                    </svg>
                                                </button>

                                                <div class="cart__thumbnail">
                                                    <a href="product-details.php?id=<?php echo $fetPrd['p_id'] ?>"><img class="border-radius-5" src="admin/img/<?php echo $fetPrd['product_image'] ?>" alt="cart-product"></a>
                                                </div>
                                                <div class="cart__content">
                                                    <h4 class="cart__content--title">
                                                        <a href="product-details.php?id=<?php echo $fetPrd['p_id'] ?>">
                                                            <?= $fetPrd['product_name']; ?><br>
                                                            <?php if (!empty($fetPrd['variant_color']) && !empty($fetPrd['variant_size'])): ?>
                                                                <small>Color: <?= htmlspecialchars($fetPrd['variant_color']); ?> | Size: <?= htmlspecialchars($fetPrd['variant_size']); ?></small>
                                                            <?php endif; ?>
                                                        </a>
                                                    </h4>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cart__table--body__list">
                                            <span class="cart__price">₹<?php echo $fetPrd['discounted_price']; ?></span>
                                        </td>
                                        <td class="cart__table--body__list text-center">
                                            <span class="in__stock text__secondary">In Stock</span>
                                        </td>
                                        <td class="cart__table--body__list text-right">
                                            <span
                                                class="add__to--cart__text primary__btn add-to-cart-btn"
                                                data-product-id="<?= $fetPrd['p_id']; ?>"
                                                data-color="<?= $fetPrd['variant_color']; ?>"
                                                data-size="<?= $fetPrd['variant_size']; ?>">
                                                Add to cart
                                            </span>

                                        </td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>

                        <div class="continue__shopping d-flex justify-content-between">
                            <a class="continue__shopping--link" href="index.php">Continue shopping</a>
                            <a class="continue__shopping--clear" href="shop.php">View All Products</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="newsletter__banner--section section--padding pt-0">
        <div class="container">
            <div class="newsletter__banner--thumbnail position__relative">
                <img class="newsletter__banner--thumbnail__img" src="assets/img/banner/banner-bg7.webp" alt="newsletter-banner">
                <div class="newsletter__content newsletter__subscribe">
                    <h5 class="newsletter__content--subtitle text-white">Want to offer regularly ?</h5>
                    <h2 class="newsletter__content--title text-white h3 mb-25">Subscribe Our Newsletter <br>
                        for Get Daily Update</h2>
                    <form class="newsletter__subscribe--form position__relative" action="#">
                        <label>
                            <input class="newsletter__subscribe--input" placeholder="Enter your email address" type="email">
                        </label>
                        <button class="newsletter__subscribe--button primary__btn" type="submit">Subscribe
                            <svg class="newsletter__subscribe--button__icon" xmlns="http://www.w3.org/2000/svg" width="9.159" height="7.85" viewbox="0 0 9.159 7.85">
                                <path data-name="Icon material-send" d="M3,12.35l9.154-3.925L3,4.5,3,7.553l6.542.872L3,9.3Z" transform="translate(-3 -4.5)" fill="currentColor"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- End Newsletter banner section -->

    <!-- Start brand logo section -->
    <div class="brand__logo--section bg__secondary section--padding">
        <div class="container-fluid">
            <div class="row row-cols-1">
                <div class="col">
                    <div class="brand__logo--section__inner d-flex justify-content-center align-items-center">
                        <div class="brand__logo--items">
                            <img class="brand__logo--items__thumbnail--img" src="assets/img/logo/brand-logo1.webp" alt="brand logo">
                        </div>
                        <div class="brand__logo--items">
                            <img class="brand__logo--items__thumbnail--img" src="assets/img/logo/brand-logo2.webp" alt="brand logo">
                        </div>
                        <div class="brand__logo--items">
                            <img class="brand__logo--items__thumbnail--img" src="assets/img/logo/brand-logo3.webp" alt="brand logo">
                        </div>
                        <div class="brand__logo--items">
                            <img class="brand__logo--items__thumbnail--img" src="assets/img/logo/brand-logo4.webp" alt="brand logo">
                        </div>
                        <div class="brand__logo--items">
                            <img class="brand__logo--items__thumbnail--img" src="assets/img/logo/brand-logo5.webp" alt="brand logo">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End brand logo section -->

</main>
<?php include('footer.php') ?>

<!-- remove product from wishlist  -->
<script>
    $(document).on('click', '.removeWishlist', function() {
        var productId = $(this).data('prd-id');
        var color = $(this).data('color');
        var size = $(this).data('size');
        let row = $(this).closest("tr");

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to remove this item from your wishlist?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "deleteProduct.php",
                    method: "POST",
                    data: {
                        prductId: productId,
                        color: color,
                        size: size
                    },
                    success: function(response) {
                        // $('#product-' + productId + '-' + color + '-' + size).remove(); 
                        $('#product-' + productId + '-' + color + '-' + size).fadeOut('slow');


                        updateWishlistCount();
                        Swal.fire({
                            title: 'Removed!',
                            text: 'The product has been removed from your wishlist.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        row.remove();
                    },
                    error: function() {
                        Swal.fire('Error!', 'Something went wrong while deleting the product.', 'error');
                    }
                });
            }
        });
    });


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
</script>

<!-- aadtocart  -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script>
    $(document).on("click", ".add-to-cart-btn", function() {
        var button = $(this);
        var productId = button.data("product-id");
        var color = button.data("color") || "";
        var size = button.data("size") || "";
        var quantity = 1; // Wishlist se by default 1 quantity jaayegi

        $.ajax({
            url: "cartscript.php",
            type: "POST",
            data: {
                action: "add_to_cart",
                product_id: productId,
                color: color,
                size: size,
                quantity: quantity
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
</script> -->


<script>
    $(document).on("click", ".add-to-cart-btn", function() {
        var button = $(this);
        var productId = button.data("product-id");
        var color = button.data("color") || "";
        var size = button.data("size") || "";
        var quantity = 1;
        $.ajax({
            url: "cartscript.php",
            type: "POST",
            data: {
                action: "add_to_cart",
                product_id: productId,
                color: color,
                size: size,
                quantity: quantity
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Added to Cart',
                    text: 'Product added to cart successfully!',
                    showConfirmButton: false,
                    timer: 1500
                });
                updateCartCount();
                // :white_check_mark: Remove from wishlist after adding to cart
                $.ajax({
                    url: "deleteProductwhilist.php",
                    type: "POST",
                    data: {
                        prductId: productId,
                        color: color,
                        size: size
                    },
                    success: function(res) {
                        // Wishlist row remove from DOM
                        $('#product-' + productId).remove();
                        // Wishlist count update
                        updateWishlistCount();
                    }
                });
            },
            error: function() {
                alert("Error adding product to cart.");
            }
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>