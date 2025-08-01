<?php
session_start();

include('header.php');
include('include/db.php');
include('wislist_script.php');


// Get product ID from URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE p_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $preFetch = $result->fetch_assoc();
}





if (isset($_POST['submitReview'])) {
    // Get values from form and sanitize them
    $product_id = $_GET['id']; // From URL
    $user_id = $_SESSION['user_id']; // Logged-in user ID

    // Collect and sanitize form inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $review = mysqli_real_escape_string($conn, $_POST['review']);

    $status = 1; // Approved by default
    $added_on = date('Y-m-d H:i:s');

    // Insert query with name and email
    $sql = "INSERT INTO products_review 
            (product_id, user_id, name, email, rating, review, status, added_on) 
            VALUES 
            ('$product_id', '$user_id', '$name', '$email', '$rating', '$review', '$status', '$added_on')";

    // Execute and handle result
    if (mysqli_query($conn, $sql)) {
        // echo "<p style='color: green;'>Review submitted successfully!</p>";

        // Redirect to prevent resubmission
        // header("Location: " . $_SERVER['REQUEST_URI']);
        // exit();
    } else {
        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
    }
}


// Blog-table
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Step 1: Fetch rating counts
$ratings = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$sql = "SELECT rating, COUNT(*) as count 
        FROM products_review 
        WHERE status = 1 AND product_id = '$product_id' 
        GROUP BY rating";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $ratings[(int)$row['rating']] = (int)$row['count'];
}

// Step 2: Total reviews and average
$total = array_sum($ratings);
$average = 0;
if ($total > 0) {
    foreach ($ratings as $star => $count) {
        $average += $star * $count;
    }
    $average = round($average / $total, 1);
}

// Step 3: Percentage width for bars
$percentages = [];
foreach ($ratings as $star => $count) {
    $percentages[$star] = $total > 0 ? round(($count / $total) * 100) : 0;
}

?>

<main class="main__content_wrapper">

    <!-- Start breadcrumb section -->
    <section class="breadcrumb__section breadcrumb__bg">
        <div class="container">
            <div class="row row-cols-1">
                <div class="col">
                    <div class="breadcrumb__content">
                        <h1 class="breadcrumb__content--title text-white mb-10">Product Details</h1>
                        <ul class="breadcrumb__content--menu d-flex">
                            <li class="breadcrumb__content--menu__items"><a class="text-white" href="index.php">Home</a></li>
                            <li class="breadcrumb__content--menu__items"><span class="text-white">Product Details</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End breadcrumb section -->

    <!-- Start product details section -->
    <section class="product__details--section section--padding">
        <div class="container">
            <div class="row row-cols-lg-2 row-cols-md-2">
                <div class="col">
                    <div class="product__details--media">
                        <div class="product__media--preview  swiper">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="product__media--preview__items">
                                        <a class="product__media--preview__items--link glightbox" data-gallery="product-media-preview" href="assets/img/product/big-product1.webp"><img class="product__media--preview__items--img" src="admin/img/<?php echo $preFetch['product_image']; ?>" alt="product-media-img"></a>
                                        <div class="product__media--view__icon">
                                            <a class="product__media--view__icon--link glightbox" href="assets/img/product/big-product1.webp" data-gallery="product-media-preview">
                                                <svg class="product__media--view__icon--svg" xmlns="http://www.w3.org/2000/svg" width="22.51" height="22.443" viewbox="0 0 512 512">
                                                    <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path>
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path>
                                                </svg>
                                                <span class="visually-hidden">Media Gallery</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="product__details--info">
                        <form action="#" method="post">
                            <h2 class="product__details--info__title mb-15"><?php echo $preFetch['product_name']; ?></h2>

                            <div class="product__details--info__price mb-10">
                                <span class="current__price">$<?php echo $preFetch['price']; ?></span>
                                <?php if ($preFetch['discounted_price'] > 0) { ?>
                                    <span class="old__price">$<?php echo $preFetch['discounted_price']; ?></span>
                                <?php } ?>
                            </div>

                            <p class="product__details--info__desc mb-20">
                                <?php
                                $description = strip_tags($preFetch['descr']); // Remove any HTML tags for safety
                                $words = explode(' ', $description); // Split into words
                                $shortDesc = implode(' ', array_slice($words, 0, 30)); // Take first 30 words
                                echo $shortDesc . (count($words) > 30 ? '...' : '');
                                ?>
                            </p>

                            <div class="product__variant product__items">
                                <?php
                                $isOutOfStock = false;
                                $hasVariants = false;
                                $product_id = $preFetch['p_id'];

                                // 1. Check if product has variants
                                $variantCheck = mysqli_query($conn, "SELECT COUNT(*) as total FROM product_variants WHERE product_id = '$product_id'");
                                $variantData = mysqli_fetch_assoc($variantCheck);

                                if ($variantData['total'] > 0) {
                                    $hasVariants = true;

                                    // 2. Check stock for variants
                                    $stockCheck = mysqli_query($conn, "SELECT SUM(quantity) as total_stock FROM product_variants WHERE product_id = '$product_id'");
                                    $stockData = mysqli_fetch_assoc($stockCheck);

                                    if ($stockData['total_stock'] == 0) {
                                        $isOutOfStock = true;
                                    }
                                } else {
                                    // 3. Check stock for simple product
                                    if (isset($preFetch['stock_quantity']) && $preFetch['stock_quantity'] == 0) {
                                        $isOutOfStock = true;
                                    }
                                }

                                // 4. Show stock status
                                if ($isOutOfStock) {
                                    echo '<div class="stock-status text-danger mb-2">Out of Stock</div>';
                                } else {
                                    // 5. Show variants if available
                                    if ($hasVariants) {
                                        // Color options
                                        echo '<fieldset class="variant__input--fieldset mb-3">
                        <legend>Color:</legend>
                        <ul class="variant__color d-flex new-color" id="product_color">';

                                        $colorQuery = mysqli_query($conn, "SELECT DISTINCT color FROM product_variants WHERE product_id = '$product_id'");
                                        while ($row = mysqli_fetch_assoc($colorQuery)) {
                                            $color = $row['color'];
                                            echo '<li class="variant__color--list me-2">
                        <input id="color' . $color . '" name="color" type="radio" class="color-input" value="' . $color . '" data-product-id="' . $product_id . '" style="display: none;">
                        <label for="color' . $color . '" class="color-label color-box" style="background-color:' . strtolower($color) . '; width: 30px; height: 30px; border-radius: 50%; border: 2px solid transparent; display:inline-block; cursor:pointer;"></label>
                    </li>';
                                        }
                                        echo '</ul></fieldset>';

                                        // Size dropdown (empty initially, AJAX se populate hoga)
                                        echo '<fieldset class="variant__input--fieldset mb-3">
                        <legend>Size:</legend>
                        <select class="form-select size-select" id="sizeDropdown' . $product_id . '" name="size" data-product-id="' . $product_id . '">
                            <option value="">Select Size</option>
                        </select>
                    </fieldset>';
                                    }
                                ?>

                                    <!-- Quantity -->
                                    <div class="product__variant--list quantity d-flex align-items-center mb-20" data-product-id="<?= $product_id; ?>">
                                        <div class="quantity__box">
                                            <button type="button" class="quantity__value decrease">-</button>
                                            <input type="number" name="quantity" class="quantity__number" value="1" min="1">
                                            <button type="button" class="quantity__value increase">+</button>
                                        </div>
                                    </div>

                                    <!-- Add to Cart -->
                                    <button type="button" class="quickview__cart--btn primary__btn add-to-cart-btn"
                                        data-product-id="<?= $product_id; ?>"
                                        data-has-variants="<?= $hasVariants ? '1' : '0'; ?>">
                                        Add To Cart
                                    </button>
                                     <br>

                                <?php } // end else block for Out of Stock 
                                ?>
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
                                    <a class="product__items--action__btn<?= $isInWishlist ? ' wishlist-active' : ''; ?>    ">

                                        ❤️ <span class="visually-hidden">Wishlist</span>
                                    </a>
                                </li>

                                
                            </div>
                            <div class="quickview__social d-flex align-items-center mb-15">
                                <label class="quickview__social--title">Social Share:</label>
                                <ul class="quickview__social--wrapper mt-0 d-flex">
                                    <li class="quickview__social--list">
                                        <a class="quickview__social--icon" target="_blank" href="../../../index.htm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="7.667" height="16.524" viewbox="0 0 7.667 16.524">
                                                <path data-name="Path 237" d="M967.495,353.678h-2.3v8.253h-3.437v-8.253H960.13V350.77h1.624v-1.888a4.087,4.087,0,0,1,.264-1.492,2.9,2.9,0,0,1,1.039-1.379,3.626,3.626,0,0,1,2.153-.6l2.549.019v2.833h-1.851a.732.732,0,0,0-.472.151.8.8,0,0,0-.246.642v1.719H967.8Z" transform="translate(-960.13 -345.407)" fill="currentColor"></path>
                                            </svg>
                                            <span class="visually-hidden">Facebook</span>
                                        </a>
                                    </li>
                                    <li class="quickview__social--list">
                                        <a class="quickview__social--icon" target="_blank" href="../../../index-1.htm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16.489" height="13.384" viewbox="0 0 16.489 13.384">
                                                <path data-name="Path 303" d="M966.025,1144.2v.433a9.783,9.783,0,0,1-.621,3.388,10.1,10.1,0,0,1-1.845,3.087,9.153,9.153,0,0,1-3.012,2.259,9.825,9.825,0,0,1-4.122.866,9.632,9.632,0,0,1-2.748-.4,9.346,9.346,0,0,1-2.447-1.11q.4.038.809.038a6.723,6.723,0,0,0,2.24-.376,7.022,7.022,0,0,0,1.958-1.054,3.379,3.379,0,0,1-1.958-.687,3.259,3.259,0,0,1-1.186-1.666,3.364,3.364,0,0,0,.621.056,3.488,3.488,0,0,0,.885-.113,3.267,3.267,0,0,1-1.374-.631,3.356,3.356,0,0,1-.969-1.186,3.524,3.524,0,0,1-.367-1.5v-.057a3.172,3.172,0,0,0,1.544.433,3.407,3.407,0,0,1-1.1-1.214,3.308,3.308,0,0,1-.4-1.609,3.362,3.362,0,0,1,.452-1.694,9.652,9.652,0,0,0,6.964,3.538,3.911,3.911,0,0,1-.075-.772,3.293,3.293,0,0,1,.452-1.694,3.409,3.409,0,0,1,1.233-1.233,3.257,3.257,0,0,1,1.685-.461,3.351,3.351,0,0,1,2.466,1.073,6.572,6.572,0,0,0,2.146-.828,3.272,3.272,0,0,1-.574,1.083,3.477,3.477,0,0,1-.913.8,6.869,6.869,0,0,0,1.958-.546A7.074,7.074,0,0,1,966.025,1144.2Z" transform="translate(-951.23 -1140.849)" fill="currentColor"></path>
                                            </svg>
                                            <span class="visually-hidden">Twitter</span>
                                        </a>
                                    </li>
                                    <li class="quickview__social--list">
                                        <a class="quickview__social--icon" target="_blank" href="../../../en/index.htm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16.482" height="16.481" viewbox="0 0 16.482 16.481">
                                                <path data-name="Path 284" d="M879,926.615a4.479,4.479,0,0,1,.622-2.317,4.666,4.666,0,0,1,1.676-1.677,4.482,4.482,0,0,1,2.317-.622,4.577,4.577,0,0,1,2.43.678,7.58,7.58,0,0,1,5.048.961,7.561,7.561,0,0,1,3.786,6.593,8,8,0,0,1-.094,1.206,4.676,4.676,0,0,1,.7,2.411,4.53,4.53,0,0,1-.622,2.326,4.62,4.62,0,0,1-1.686,1.686,4.626,4.626,0,0,1-4.756-.075,7.7,7.7,0,0,1-1.187.094,7.623,7.623,0,0,1-7.647-7.647,7.46,7.46,0,0,1,.094-1.187A4.424,4.424,0,0,1,879,926.615Zm4.107,1.714a2.473,2.473,0,0,0,.282,1.234,2.41,2.41,0,0,0,.782.829,5.091,5.091,0,0,0,1.215.565,15.981,15.981,0,0,0,1.582.424q.678.151.979.235a3.091,3.091,0,0,1,.593.235,1.388,1.388,0,0,1,.452.348.738.738,0,0,1,.16.481.91.91,0,0,1-.48.753,2.254,2.254,0,0,1-1.271.321,2.105,2.105,0,0,1-1.253-.292,2.262,2.262,0,0,1-.65-.838,2.42,2.42,0,0,0-.414-.546.853.853,0,0,0-.584-.17.893.893,0,0,0-.669.283.919.919,0,0,0-.273.659,1.654,1.654,0,0,0,.217.782,2.456,2.456,0,0,0,.678.763,3.64,3.64,0,0,0,1.158.574,5.931,5.931,0,0,0,1.639.235,5.767,5.767,0,0,0,2.072-.339,2.982,2.982,0,0,0,1.356-.961,2.306,2.306,0,0,0,.471-1.431,2.161,2.161,0,0,0-.443-1.375,3.009,3.009,0,0,0-1.2-.894,10.118,10.118,0,0,0-1.865-.575,11.2,11.2,0,0,1-1.309-.311,2.011,2.011,0,0,1-.8-.452.992.992,0,0,1-.3-.744,1.143,1.143,0,0,1,.565-.97,2.59,2.59,0,0,1,1.488-.386,2.538,2.538,0,0,1,1.074.188,1.634,1.634,0,0,1,.622.49,3.477,3.477,0,0,1,.414.753,1.568,1.568,0,0,0,.4.594.866.866,0,0,0,.574.2,1,1,0,0,0,.706-.254.828.828,0,0,0,.273-.631,2.234,2.234,0,0,0-.443-1.253,3.321,3.321,0,0,0-1.158-1.046,5.375,5.375,0,0,0-2.524-.527,5.764,5.764,0,0,0-2.213.386,3.161,3.161,0,0,0-1.422,1.083A2.738,2.738,0,0,0,883.106,928.329Z" transform="translate(-878.999 -922)" fill="currentColor"></path>
                                            </svg>
                                            <span class="visually-hidden">Skype</span>
                                        </a>
                                    </li>
                                    <li class="quickview__social--list">
                                        <a class="quickview__social--icon" target="_blank" href="../../../index-4.htm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16.49" height="11.582" viewbox="0 0 16.49 11.582">
                                                <path data-name="Path 321" d="M967.759,1365.592q0,1.377-.019,1.717-.076,1.114-.151,1.622a3.981,3.981,0,0,1-.245.925,1.847,1.847,0,0,1-.453.717,2.171,2.171,0,0,1-1.151.6q-3.585.265-7.641.189-2.377-.038-3.387-.085a11.337,11.337,0,0,1-1.5-.142,2.206,2.206,0,0,1-1.113-.585,2.562,2.562,0,0,1-.528-1.037,3.523,3.523,0,0,1-.141-.585c-.032-.2-.06-.5-.085-.906a38.894,38.894,0,0,1,0-4.867l.113-.925a4.382,4.382,0,0,1,.208-.906,2.069,2.069,0,0,1,.491-.755,2.409,2.409,0,0,1,1.113-.566,19.2,19.2,0,0,1,2.292-.151q1.82-.056,3.953-.056t3.952.066q1.821.067,2.311.142a2.3,2.3,0,0,1,.726.283,1.865,1.865,0,0,1,.557.49,3.425,3.425,0,0,1,.434,1.019,5.72,5.72,0,0,1,.189,1.075q0,.095.057,1C967.752,1364.1,967.759,1364.677,967.759,1365.592Zm-7.6.925q1.49-.754,2.113-1.094l-4.434-2.339v4.66Q958.609,1367.311,960.156,1366.517Z" transform="translate(-951.269 -1359.8)" fill="currentColor"></path>
                                            </svg>
                                            <span class="visually-hidden">Youtube</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="guarantee__safe--checkout">
                                <h5 class="guarantee__safe--checkout__title">Guaranteed Safe Checkout</h5>
                                <img class="guarantee__safe--checkout__img" src="assets/img/other/safe-checkout.webp" alt="Payment Image">
                            </div>
                        </form>


                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End product details section -->

    <!-- Start product details tab section -->
    <section class="product__details--tab__section section--padding">
        <div class="container">
            <div class="row row-cols-1">
                <div class="col">
                    <ul class="product__details--tab d-flex mb-30">
                        <li class="product__details--tab__list active" data-toggle="tab" data-target="#description">Description</li>
                        <li class="product__details--tab__list" data-toggle="tab" data-target="#reviews">Product Reviews</li>
                        <!-- <li class="product__details--tab__list" data-toggle="tab" data-target="#information">Additional Info</li> -->
                        <!-- <li class="product__details--tab__list" data-toggle="tab" data-target="#custom">Custom Content</li> -->
                    </ul>
                    <div class="product__details--tab__inner border-radius-10">
                        <div class="tab_content">

                            <div id="description" class="tab_pane active show">
                                <div class="product__tab--content">

                                    <!-- <div class="product_tab--content_right">
                                        <div class="product_tab--content_step mb-20">
                                            <h4 class="product_tab--content_name"><?php echo $preFetch['product_name']; ?></h4>
                                            <p class="product_tab--content_desc"><?php echo $preFetch['descr']; ?></p>
                                        </div>

                                    </div> -->
                                </div>

                            </div>
                        </div>







 
                        <!-- Recent Comment -->
                        <div class="comment__box">
                            <div class="reviews__comment--area2 mb-50">
                                <h3 class="reviews__comment--reply__title mb-25">Recent Comment</h3>
                                <div class="reviews__comment--inner">
                                    <?php

                                    // Setup pagination
                                    // Setup pagination
                                    $commentsPerPage = 3;
                                    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                                    $offset = ($page - 1) * $commentsPerPage;

                                    // ✅ Correct count query with product_id filter
                                    $countQuery = "SELECT COUNT(*) AS total FROM products_review WHERE status = 1 AND product_id = $product_id";
                                    $countResult = mysqli_query($conn, $countQuery);
                                    $totalComments = mysqli_fetch_assoc($countResult)['total'];
                                    $totalPages = ceil($totalComments / $commentsPerPage);


                                    $query = "
    SELECT 
        products_review.name, 
        products_review.review, 
        products_review.rating, 
        products_review.added_on 
    FROM 
        products_review 
    JOIN 
        user_reg ON products_review.user_id = user_reg.id 
    WHERE 
        products_review.status = 1 AND products_review.product_id = $product_id
    ORDER BY 
        products_review.added_on DESC
    LIMIT $commentsPerPage OFFSET $offset
";


                                    $result = mysqli_query($conn, $query);

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $name = htmlspecialchars($row['name']);
                                            $review = nl2br(htmlspecialchars($row['review']));
                                            $formatted_date = date("j F Y", strtotime($row['added_on']));
                                            $rating = (int)$row['rating'];

                                            // Create star display
                                            $stars_html = '';
                                            for ($i = 1; $i <= 5; $i++) {
                                                $stars_html .= $i <= $rating ? '<span style="color: red;">&#9733;</span>' : '<span style="color: #ccc;">&#9733;</span>';
                                            }

                                            echo <<<HTML
<div class="reviews__comment--list d-flex mb-3">
    <div class="reviews__comment--content">
        <h4 class="reviews__comment--content__title2">{$name}</h4>
        <span class="reviews__comment--content__date2">on {$formatted_date}</span>
        <div class="rating-stars mb-2">{$stars_html}</div>
        <p class="reviews__comment--content__desc">{$review}</p>
        <!-- <button class="comment__reply--btn primary__btn" type="button">Reply</button> -->
    </div>
</div>
HTML;
                                        }
                                    } else 
                                        if ($totalComments == 0) {
                                        echo '<div style="...">😔 No reviews found for this product.</div>';
                                    } else {
                                        // Proceed to render reviews
                                    }

                                    ?>

                                </div>

                                <!-- ✅ Pagination UI -->

                                <?php if ($totalPages > 1): ?>
                                    <div class="pagination" style="margin-top: 20px; text-align: center;">
                                        <p>Page <?php echo $page; ?> of <?php echo $totalPages; ?></p>
                                        <?php
                                        $baseLink = '?cid=' . $product_id . '&page=';
                                        $start = max(1, $page - 4);
                                        $end = min($totalPages, $start + 9);

                                        if ($page > 1) {
                                            echo '<a href="' . $baseLink . ($page - 1) . '">Prev</a> ';
                                        }

                                        for ($i = $start; $i <= $end; $i++) {
                                            if ($i == $page) {
                                                echo '<strong style="margin: 0 5px;">' . $i . '</strong>';
                                            } else {
                                                echo '<a style="margin: 0 5px;" href="' . $baseLink . $i . '">' . $i . '</a>';
                                            }
                                        }

                                        if ($page < $totalPages) {
                                            echo '<a href="' . $baseLink . ($page + 1) . '">Next</a>';
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <style>
                                .rating-stars span {
                                    font-size: 18px;
                                    margin-right: 2px;
                                }
                            </style>

                            <style>
                                .rating-stars span {
                                    font-size: 18px;
                                    margin-right: 2px;
                                }

                                .pagination a {
                                    text-decoration: none;
                                    color: red;
                                    padding: 5px 10px;
                                    border: 1px solid #ddd;
                                    border-radius: 4px;
                                }

                                .pagination strong {
                                    font-weight: bold;
                                }
                            </style>

                            <!-- Table user -->

                            <span class="heading">User Rating</span>

                            <?php
                            $average = round($average, 1); // Round to 1 decimal for display
                            $fullStars = floor($average);          // Pure full stars
                            $halfStar = ($average - $fullStars) >= 0.5 ? 1 : 0; // One half star if needed
                            $emptyStars = 5 - $fullStars - $halfStar;

                            // Full stars
                            for ($i = 0; $i < $fullStars; $i++) {
                                echo '<span class="fa fa-star checked"></span>';
                            }

                            // Half star
                            if ($halfStar) {
                                echo '<span class="fa fa-star-half-o checked"></span>';
                            }

                            // Empty stars
                            for ($i = 0; $i < $emptyStars; $i++) {
                                echo '<span class="fa fa-star-o"></span>';
                            }
                            ?>

                            <p><?php echo $average; ?> average based on <?php echo $total; ?> reviews.</p>
                            <hr style="border:3px solid #f1f1f1">


                            <style>
                                .fa {
                                    font-size: 25px;
                                }

                                .checked {
                                    color: red;
                                }
                            </style>


                            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

                            <div class="row">
                                <?php
                                // Render bars from 5 stars to 1 star
                                $colors = [1 => "#f44336", 2 => "#ff9800", 3 => "#00bcd4", 4 => "#2196F3", 5 => "#04AA6D"];
                                for ($i = 5; $i >= 1; $i--) {
                                    echo '
                                      <div class="side">
                                       <div>' . $i . ' star</div>
                                       </div>
                                  <div class="middle">
                                     <div class="bar-container">
                                            <div style="width:' . $percentages[$i] . '%; height:18px; background-color:' . $colors[$i] . ';"></div>
                                      </div>
                                       </div>
                                    <div class="side right">
                                          <div>' . $ratings[$i] . '</div>
                                            </div>
                                             ';
                                }
                                ?>
                            </div>

                            <style>
                                * {
                                    box-sizing: border-box;
                                }

                                .heading {
                                    font-size: 25px;
                                    margin-right: 25px;
                                }

                                .fa {
                                    font-size: 25px;
                                }

                                .checked {
                                    color: red;
                                }

                                .side {
                                    float: left;
                                    width: 15%;
                                    margin-top: 10px;
                                }

                                .middle {
                                    margin-top: 10px;
                                    float: left;
                                    width: 70%;
                                }

                                .right {
                                    text-align: right;
                                }

                                .row:after {
                                    content: "";
                                    display: table;
                                    clear: both;
                                }

                                .bar-container {
                                    width: 100%;
                                    background-color: #f1f1f1;
                                    text-align: center;
                                    color: white;
                                }

                                @media (max-width: 400px) {

                                    .side,
                                    .middle {
                                        width: 100%;
                                    }

                                    .right {
                                        display: none;
                                    }
                                }
                            </style>

                            <!-- Leave A Comment -->
                            <div class="reviews__comment--reply__area">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <!-- Review Form -->
                                   <form action="blog-details.php?cid=<?php echo isset($_GET['cid']) ? htmlspecialchars($_GET['cid']) : ''; ?>" method="post" id="reviewForm">

                                        <h3 class="reviews__comment--reply__title mb-20">Leave A Comment</h3>
                                        <!-- ⭐ Star Rating -->
                                        <div class="col-lg-4 col-md-12 mb-20">
                                            <label class="rating-label d-block mb-2">Rate your experience:</label>
                                            <div class="star-rating" id="starRating">
                                                <span class="star" data-value="1">&#9733;</span>
                                                <span class="star" data-value="2">&#9733;</span>
                                                <span class="star" data-value="3">&#9733;</span>
                                                <span class="star" data-value="4">&#9733;</span>
                                                <span class="star" data-value="5">&#9733;</span>
                                            </div>
                                            <div class="rating-text mt-1 text-muted" id="ratingText"></div>
                                            <input type="hidden" name="rating" id="ratingValue" required>
                                        </div>
                                        <div class="row">



                                            <!-- Name -->
                                            <div class="col-lg-4 col-md-6 mb-20">
                                                <input class="reviews__comment--reply__input" name="name" placeholder="Your Name..." type="text" required value="<?php echo isset($_SESSION['name']) ? ($_SESSION['name']) : ''; ?>">
                                            </div>

                                            <!-- Email -->
                                            <div class="col-lg-4 col-md-6 mb-20">
                                                <input class="reviews__comment--reply__input" name="email" placeholder="Your Email..." type="email" required value="<?php echo isset($_SESSION['email']) ? ($_SESSION['email']) : ''; ?>">
                                            </div>

                                            <!-- ⭐ Star Rating -->
                                            <!-- <div class="col-lg-4 col-md-12 mb-20">
                                                <label class="rating-label d-block mb-2">Rate your experience:</label>
                                                <div class="star-rating" id="starRating">
                                                    <span class="star" data-value="1">&#9733;</span>
                                                    <span class="star" data-value="2">&#9733;</span>
                                                    <span class="star" data-value="3">&#9733;</span>
                                                    <span class="star" data-value="4">&#9733;</span>
                                                    <span class="star" data-value="5">&#9733;</span>
                                                </div>
                                                <div class="rating-text mt-1 text-muted" id="ratingText"></div>
                                                <input type="hidden" name="rating" id="ratingValue" required>
                                            </div> -->




                                            <!-- Review Text -->
                                            <div class="col-12 mb-15">
                                                <textarea class="reviews__comment--reply__textarea" name="review" placeholder="Your review..." required></textarea>
                                            </div>
                                        </div>

                                        <!-- Submit -->
                                        <button class="primary__btn text-white" name="submitReview" type="submit">SUBMIT</button>
                                    </form>

                                    <!-- JS for Star Rating -->
                                    <script>
                                        const stars = document.querySelectorAll('.star-rating .star');
                                        const ratingValue = document.getElementById('ratingValue');
                                        const ratingText = document.getElementById('ratingText');
                                        const descriptions = ["Very Bad", "Bad", "Average", "Good", "Excellent"];

                                        stars.forEach((star, index) => {
                                            star.addEventListener('click', () => {
                                                ratingValue.value = star.dataset.value;
                                                ratingText.innerText = descriptions[index];
                                                stars.forEach(s => s.classList.remove('selected'));
                                                for (let i = 0; i <= index; i++) {
                                                    stars[i].classList.add('selected');
                                                }
                                            });
                                        });
                                    </script>

                                    <!-- CSS for star rating highlight -->
                                    <style>
                                        .star-rating .star {
                                            font-size: 24px;
                                            cursor: pointer;
                                            color: #ccc;
                                        }

                                        .star-rating .star.selected {
                                            color: red;
                                        }
                                    </style>

                                <?php else: ?>
                                    <div class="login-reminder alert alert-warning mt-3">
                                        <span>Please <a href="login.php"><strong>login</strong></a> to submit your review.</span>
                                    </div>
                                <?php endif; ?>
                            </div>
<!-- login css -->
<style>
    .login-reminder {
        background-color: #FFF3CD;
        border: 1px solid #FFEEBA;
        padding: 15px 20px;
        border-radius: 5px;
        color: #856404;
        font-size: 16px;
        margin-top: 20px;
        text-align: center;
    }
    .login-reminder a {
        color: #007BFF;
        font-weight: bold;
        text-decoration: none;
    }
    .login-reminder a:hover {
        text-decoration: underline;
    }
</style>
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    const stars = document.querySelectorAll("#starRating .star");
                                    const ratingInput = document.getElementById("ratingValue");
                                    const ratingText = document.getElementById("ratingText");
                                    const form = document.getElementById("reviewForm");
                                    let selectedRating = 0;

                                    const ratingMessages = ["Very Poor", "Poor", "Average", "Good", "Excellent"];

                                    stars.forEach((star, index) => {
                                        const value = index + 1;

                                        star.addEventListener("mouseover", () => highlightStars(value));
                                        star.addEventListener("mouseout", () => highlightStars(selectedRating));
                                        star.addEventListener("click", () => {
                                            selectedRating = value;
                                            ratingInput.value = selectedRating;
                                            ratingText.textContent = `You rated: ${value} star${value > 1 ? 's' : ''} (${ratingMessages[value - 1]})`;
                                            highlightStars(selectedRating);
                                        });
                                    });

                                    function highlightStars(rating) {
                                        stars.forEach((star, i) => {
                                            star.classList.toggle("hovered", i < rating);
                                            star.classList.toggle("selected", i < rating);
                                        });
                                    }

                                    form.addEventListener("submit", function(e) {
                                        if (ratingInput.value === "") {
                                            e.preventDefault();
                                            alert("Please select a star rating before submitting.");
                                        }
                                    });
                                });
                            </script>
                            <!--end Leave A Comment -->


                        </div>
                    </div>
                </div>


                </div>
            </div>
        </div>
        </div>
    </section>
    <!-- End product details tab section -->



    <!-- Start Newsletter banner section -->
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
</main>

<?php include('footer.php') ?>

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
        $(".add-to-cart-btn").click(function(e) {
            e.preventDefault();

            var productBox = $(this).closest(".product__items"); // Outer wrapper
            var productId = $(this).data("product-id");
            var quantity = productBox.find(".quantity__number").val();
            var hasVariants = productBox.find(".color-input").length > 0;

            // If variants exist
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

            // Prepare values
            var color = productBox.find(".color-input:checked").val() || '';
            var size = productBox.find(".size-select").val() || '';

            // AJAX request
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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- get sizes by colour  -->
<script>
    $(document).ready(function() {

        // 🔄 Color change → fetch sizes
        $(document).on('change', '.color-input', function() {
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

        $(".quantity__value").click(function() {
            var $btn = $(this);
            var $parent = $btn.closest(".product__variant--list");
            var $input = $parent.find(".quantity__number");
            var currentValue = parseInt($input.val());
            var min = parseInt($input.attr("min")) || 1;
            var productId = $parent.data("product-id");

            // ✅ Use form as a reliable container
            var $form = $btn.closest("form");
            var selectedColor = $form.find(".color-input:checked").val();
            var selectedSize = $form.find(".size-select").val();

            // ✅ IF VARIANT SELECTED
            if (selectedColor && selectedSize) {
                $.ajax({
                    url: "get_quantity.php",
                    type: "POST",
                    data: {
                        product_id: productId,
                        color: selectedColor,
                        size: selectedSize
                    },
                    success: function(stock) {
                        stock = parseInt(stock);
                        $input.attr("max", stock);

                        if (stock === 0) {
                            $input.val(0).prop("disabled", true);
                            $btn.prop("disabled", true);
                            return;
                        }

                        if ($btn.hasClass("increase")) {
                            if (currentValue < stock) {
                                $input.val(currentValue + 0);
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
                            $input.val(currentValue - 0);
                        }
                    }
                });
            } else {
                // 🟥 IF SIMPLE PRODUCT
                $.ajax({
                    url: 'check_stock.php',
                    type: 'POST',
                    data: {
                        product_id: productId
                    },
                    success: function(stock) {
                        stock = parseInt(stock);
                        $input.attr("max", stock);

                        if (stock === 0) {
                            $input.val(0).prop("disabled", true);
                            $btn.prop("disabled", true);
                            $("#stock-status-" + productId).html("<span class='text-danger'>Out of Stock</span>");
                            return;
                        }

                        $input.prop("disabled", false);
                        $btn.prop("disabled", false);
                        $("#stock-status-" + productId).html("");

                        if ($btn.hasClass("increase")) {
                            if (currentValue < stock) {
                                $input.val(currentValue + 0);
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
                            $input.val(currentValue - 0);
                        }
                    }
                });
            }
        });

    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>