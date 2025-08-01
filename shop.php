<?php
include('header.php');
include('include/db.php');
include('wislist_script.php');

// Get filters from URL
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$subcategory = isset($_GET['subcategory']) ? mysqli_real_escape_string($conn, $_GET['subcategory']) : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';

// Base SQL query
$sql = "SELECT p.* FROM products p 
        JOIN product_categories c ON p.category_id = c.pc_id  
        WHERE p.status = 1";

// Apply category and subcategory filters
if (!empty($category)) {
    $sql .= " AND c.category = '$category'";
}
if (!empty($subcategory)) {
    $sql .= " AND p.subcategory_name = '$subcategory'";
}

// Apply price filter
if ($min_price > 0) {
    $sql .= " AND p.discounted_price >= $min_price";
}
if ($max_price > 0) {
    $sql .= " AND p.discounted_price <= $max_price";
}

// Sorting logic
switch ($sort_by) {
    case 'a_to_z':
        $sql .= " ORDER BY p.product_name ASC";
        break;
    case 'z_to_a':
        $sql .= " ORDER BY p.product_name DESC";
        break;
    case 'price_low':
        $sql .= " ORDER BY p.discounted_price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.discounted_price DESC";
        break;
    default:
        $sql .= " ORDER BY p.p_id DESC"; // Latest products
        break;
}

// Execute final query
$resProducts = mysqli_query($conn, $sql);

// Check for errors
if (!$resProducts) {
    die("Query failed: " . mysqli_error($conn));
}


?>

<style>
    .quantity__box {
        justify-content: center;
    }
</style>
<main class="main__content_wrapper">

    <!-- Start breadcrumb section -->
    <section class="breadcrumb__section breadcrumb__bg">
        <div class="container-fluid">
            <div class="row row-cols-1">
                <div class="col">
                    <div class="breadcrumb__content">
                        <h1 class="breadcrumb__content--title text-white mb-10">Shop Left</h1>
                        <ul class="breadcrumb__content--menu d-flex">
                            <li class="breadcrumb__content--menu__items"><a class="text-white" href="index.php">Home</a></li>
                            <li class="breadcrumb__content--menu__items"><span class="text-white">Shop Left Sidebar</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End breadcrumb section -->

    <!-- Start shop section -->
    <section class="shop__section section--padding">
        <div class="container-fluid">
            <div class="row" id="product-list">
                <div class="col-xl-3 col-lg-2">
                    <div class="shop__sidebar--widget widget__area d-md-none">
                        <div class="single__widget widget__bg">
                            <h2 class="widget__title position__relative h3">Search</h2>
                            <!-- ✅ Search Form -->
                            <form id="shop-search-form" method="POST">
                                <input id="shop-search-input" name="query" type="text" placeholder="Search products...">
                                <button type="submit">Search</button>
                            </form>


                        </div>
                        <div class="single__widget widget__bg">
                            <h2 class="widget__title position__relative h3">Categories</h2>
                            <ul class="widget__categories--menu">
                                <?php

                                $sql = "SELECT * FROM `product_categories` where `status`=1";
                                $resCat = mysqli_query($conn, $sql);

                                $categories = [];

                                while ($row = mysqli_fetch_assoc($resCat)) {
                                    $categories[$row['category']][] = $row['sub_category'];
                                }

                                foreach ($categories as $category => $subCategories) {
                                ?>
                                    <li class="widget__categories--menu__list">
                                        <label class="widget__categories--menu__label d-flex align-items-center">
                                            <img class="widget__categories--menu__img" src="assets/img/product/small-product1.webp" alt="categories-img">
                                            <span class="widget__categories--menu__text"><?php echo htmlspecialchars($category); ?></span>
                                            <svg class="widget__categories--menu__arrowdown--icon" xmlns="http://www.w3.org/2000/svg" width="12.355" height="8.394">
                                                <path d="M15.138,8.59l-3.961,3.952L7.217,8.59,6,9.807l5.178,5.178,5.178-5.178Z" transform="translate(-6 -8.59)" fill="currentColor"></path>
                                            </svg>
                                        </label>

                                        <ul class="widget__categories--sub__menu">
                                            <?php
                                            foreach ($subCategories as $subCat) {
                                                $subCatArray = explode(',', $subCat);
                                                foreach ($subCatArray as $sub) { ?>
                                                    <li class="widget__categories--sub__menu--list">
                                                        <a class="widget__categories--sub__menu--link d-flex align-items-center"
                                                            href="shop.php?category=<?php echo urlencode($category); ?>&subcategory=<?php echo urlencode(trim($sub)); ?>">
                                                            <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product2.webp" alt="categories-img">
                                                            <span class="widget__categories--sub__menu--text"><?php echo htmlspecialchars(trim($sub)); ?></span>
                                                        </a>
                                                    </li>
                                            <?php }
                                            } ?>
                                        </ul>

                                    </li>
                                <?php } ?>
                            </ul>

                        </div>
                        <div class="text-right" style="margin-bottom:20px;">
                            <a href="shop.php">
                                <button style="color: #ffffff;
    background: #f51c1c;border: none; font-size: 14px;padding: 4px 10px;letter-spacing: 2px;">Reset</button>
                            </a>
                        </div>
                        <div class="single__widget price__filter widget__bg">
                            <h2 class="widget__title position__relative h3">Filter By Price</h2>
                            <form class="price__filter--form" action="shop.php" method="GET">
                                <input type="hidden" name="category" value="<?php echo isset($_GET['category']) ? htmlspecialchars($_GET['category']) : ''; ?>">
                                <input type="hidden" name="subcategory" value="<?php echo isset($_GET['subcategory']) ? htmlspecialchars($_GET['subcategory']) : ''; ?>">

                                <div class="price__filter--form__inner mb-15 d-flex align-items-center">
                                    <div class="price__filter--group">
                                        <label class="price__filter--label" for="Filter-Price-GTE1">From</label>
                                        <div class="price__filter--input border-radius-5 d-flex align-items-center">
                                            <span class="price__filter--currency">$</span>
                                            <input class="price__filter--input__field border-0" id="Filter-Price-GTE1" name="min_price" type="number" placeholder="0" min="0" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="price__divider">
                                        <span>-</span>
                                    </div>
                                    <div class="price__filter--group">
                                        <label class="price__filter--label" for="Filter-Price-LTE1">To</label>
                                        <div class="price__filter--input border-radius-5 d-flex align-items-center">
                                            <span class="price__filter--currency">$</span>
                                            <input class="price__filter--input__field border-0" id="Filter-Price-LTE1" name="max_price" type="number" min="0" placeholder="250.00" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                <button class="price__filter--btn primary__btn" type="submit">Filter</button>
                            </form>

                        </div>

                        <div class="single__widget widget__bg">
                            <h2 class="widget__title position__relative h3">Top Rated Product</h2>
                            <div class="product__grid--inner">
                                <div class="product__items product__items--grid d-flex align-items-center">
                                    <div class="product__items--grid__thumbnail position__relative">
                                        <a class="product__items--link" href="product-details.php">
                                            <img class="product__items--img product__primary--img" src="assets/img/product/small-product1.webp" alt="product-img">
                                            <img class="product__items--img product__secondary--img" src="assets/img/product/small-product2.webp" alt="product-img">
                                        </a>
                                    </div>
                                    <div class="product__items--grid__content">
                                        <h3 class="product__items--content__title h4"><a href="product-details.php">Modern Chair</a></h3>
                                        <div class="product__items--price">
                                            <span class="current__price">$165.00</span>
                                        </div>
                                        <div class="product__items--color">
                                            <ul class="product__items--color__wrapper d-flex">
                                                <li class="product__items--color__list"><a class="product__items--color__link one" href="javascript:void(0)"><span class="visually-hidden">Color 1</span></a></li>
                                                <li class="product__items--color__list"><a class="product__items--color__link two" href="javascript:void(0)"><span class="visually-hidden">Color 2</span></a></li>
                                                <li class="product__items--color__list"><a class="product__items--color__link three" href="javascript:void(0)"><span class="visually-hidden">Color 3</span></a></li>
                                                <li class="product__items--color__list"><a class="product__items--color__link four" href="javascript:void(0)"><span class="visually-hidden">Color 4</span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="product__items product__items--grid d-flex align-items-center">
                                    <div class="product__items--grid__thumbnail position__relative">
                                        <a class="product__items--link" href="product-details.php">
                                            <img class="product__items--img product__primary--img" src="assets/img/product/small-product3.webp" alt="product-img">
                                            <img class="product__items--img product__secondary--img" src="assets/img/product/small-product4.webp" alt="product-img">
                                        </a>
                                    </div>
                                    <div class="product__items--grid__content">
                                        <h3 class="product__items--content__title h4"><a href="product-details.php">Plastic Chair</a></h3>
                                        <div class="product__items--price">
                                            <span class="current__price">$165.00</span>
                                        </div>
                                        <div class="product__items--color">
                                            <ul class="product__items--color__wrapper d-flex">
                                                <li class="product__items--color__list"><a class="product__items--color__link one" href="javascript:void(0)"><span class="visually-hidden">Color 1</span></a></li>
                                                <li class="product__items--color__list"><a class="product__items--color__link two" href="javascript:void(0)"><span class="visually-hidden">Color 2</span></a></li>
                                                <li class="product__items--color__list"><a class="product__items--color__link three" href="javascript:void(0)"><span class="visually-hidden">Color 3</span></a></li>
                                                <li class="product__items--color__list"><a class="product__items--color__link four" href="javascript:void(0)"><span class="visually-hidden">Color 4</span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="product__items product__items--grid d-flex align-items-center">
                                    <div class="product__items--grid__thumbnail position__relative">
                                        <a class="product__items--link" href="product-details.php">
                                            <img class="product__items--img product__primary--img" src="assets/img/product/small-product5.webp" alt="product-img">
                                            <img class="product__items--img product__secondary--img" src="assets/img/product/small-product6.webp" alt="product-img">
                                        </a>
                                    </div>
                                    <div class="product__items--grid__content">
                                        <h3 class="product__items--content__title h4"><a href="product-details.php">Design Rooms</a></h3>
                                        <div class="product__items--price">
                                            <span class="current__price">$165.00</span>
                                        </div>
                                        <div class="product__items--color">
                                            <ul class="product__items--color__wrapper d-flex">
                                                <li class="product__items--color__list"><a class="product__items--color__link one" href="javascript:void(0)"><span class="visually-hidden">Color 1</span></a></li>
                                                <li class="product__items--color__list"><a class="product__items--color__link two" href="javascript:void(0)"><span class="visually-hidden">Color 2</span></a></li>
                                                <li class="product__items--color__list"><a class="product__items--color__link three" href="javascript:void(0)"><span class="visually-hidden">Color 3</span></a></li>
                                                <li class="product__items--color__list"><a class="product__items--color__link four" href="javascript:void(0)"><span class="visually-hidden">Color 4</span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="shop__header bg__gray--color d-flex align-items-center justify-content-between mb-30">
                        <button class="widget__filter--btn d-none d-md-flex align-items-center">
                            <svg class="widget__filter--btn__icon" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="28" d="M368 128h80M64 128h240M368 384h80M64 384h240M208 256h240M64 256h80"></path>
                                <circle cx="336" cy="128" r="28" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="28"></circle>
                                <circle cx="176" cy="256" r="28" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="28"></circle>
                                <circle cx="336" cy="384" r="28" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="28"></circle>
                            </svg>
                            <span class="widget__filter--btn__text">Filter</span>
                        </button>
                        <div class="product__view--mode d-flex align-items-center">

                            <div class="product__view--mode__list product__short--by align-items-center d-none d-lg-flex">
                                <form method="GET" id="sortForm">
                                    <input type="hidden" name="category" value="<?= isset($_GET['category']) ? $_GET['category'] : '' ?>">
                                    <input type="hidden" name="subcategory" value="<?= isset($_GET['subcategory']) ? $_GET['subcategory'] : '' ?>">
                                    <input type="hidden" name="min_price" value="<?= isset($_GET['min_price']) ? $_GET['min_price'] : '' ?>">
                                    <input type="hidden" name="max_price" value="<?= isset($_GET['max_price']) ? $_GET['max_price'] : '' ?>">

                                    <label class="product__view--label">Sort By :</label>
                                    <div class="select shop__header--select">
                                        <select class="product__view--select" name="sort_by" onchange="document.getElementById('sortForm').submit();">
                                            <option value="">Sort by latest</option>
                                            <option value="a_to_z" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'a_to_z') ? 'selected' : '' ?>>Sort by A to Z</option>
                                            <option value="z_to_a" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'z_to_a') ? 'selected' : '' ?>>Sort by Z to A</option>
                                            <option value="price_low" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_low') ? 'selected' : '' ?>>Sort by Price Low to High</option>
                                            <option value="price_high" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_high') ? 'selected' : '' ?>>Sort by Price High to Low</option>
                                        </select>
                                    </div>
                                </form>

                            </div>
                            <div class="product__view--mode__list">
                                <div class="product__grid--column__buttons d-flex justify-content-center">
                                    <button class="product__grid--column__buttons--icons active" data-toggle="tab" aria-label="product grid btn" data-target="#product_grid">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewbox="0 0 9 9">
                                            <g transform="translate(-1360 -479)">
                                                <rect id="Rectangle_5725" data-name="Rectangle 5725" width="4" height="4" transform="translate(1360 479)" fill="currentColor"></rect>
                                                <rect id="Rectangle_5727" data-name="Rectangle 5727" width="4" height="4" transform="translate(1360 484)" fill="currentColor"></rect>
                                                <rect id="Rectangle_5726" data-name="Rectangle 5726" width="4" height="4" transform="translate(1365 479)" fill="currentColor"></rect>
                                                <rect id="Rectangle_5728" data-name="Rectangle 5728" width="4" height="4" transform="translate(1365 484)" fill="currentColor"></rect>
                                            </g>
                                        </svg>
                                    </button>

                                </div>
                            </div>
                        </div>
                        <p class="product__showing--count">Showing 1–9 of 21 results</p>
                    </div>
                    <div class="shop__product--wrapper">
                        <div class="tab_content">
                            <div id="product_grid" class="tab_pane active show">
                                <div class="product__section--inner product__grid--inner">
                                    <!-- <div class="row row-cols-xxl-4 row-cols-xl-3 row-cols-lg-3 row-cols-md-3 row-cols-2 mb--n30" id="product-list"> -->
                                    <!-- ✅ Product Grid -->
                                    <!-- ✅ Only this section should update -->
                                    <div id="product-list" class="row  row-cols-xxl-4 row-cols-xl-3 row-cols-lg-3 row-cols-md-3 row-cols-2 mb--n30">
                                        <?php
                                        while ($product = mysqli_fetch_assoc($resProducts)) {
                                            $product_id = $product['p_id'];
                                            include 'shop_col.php'; // your reusable product card
                                        }
                                        ?>
                                    </div>
                                    <!-- </div> -->
                                </div>
                                <div id="product_list" class="tab_pane">
                                    <div class="product__section--inner">
                                        <div class="row row-cols-1 mb--n30">
                                            <div class="col mb-30">
                                                <div class="product__items product__list--items border-radius-5 d-flex align-items-center">
                                                    <div class="product__list--items__left d-flex align-items-center">
                                                        <div class="product__items--thumbnail product__list--items__thumbnail">
                                                            <a class="product__items--link" href="product-details.php">
                                                                <img class="product__items--img product__primary--img" src="assets/img/product/product11.webp" alt="product-img">
                                                                <img class="product__items--img product__secondary--img" src="assets/img/product/product10.webp" alt="product-img">
                                                            </a>
                                                            <div class="product__badge">
                                                                <span class="product__badge--items sale">Sale</span>
                                                            </div>
                                                        </div>
                                                        <div class="product__list--items__content">
                                                            <span class="product__items--content__subtitle mb-5">Wooden</span>
                                                            <h4 class="product__list--items__content--title mb-15"><a href="product-details.php">Larger Minimal Wooden Chair</a></h4>
                                                            <p class="product__list--items__content--desc m-0">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quia voluptas dolore doloribus architecto sequi corporis deleniti officia culpa dolor esse consectetur eligendi.</p>
                                                        </div>
                                                    </div>
                                                    <div class="product__list--items__right">
                                                        <span class="product__list--current__price">$299.00</span>
                                                        <ul class="rating product__list--rating d-flex">
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list"><span class="rating__list--text">( 5.0)</span></li>
                                                        </ul>
                                                        <div class="product__list--action">
                                                            <a class="product__list--action__cart--btn primary__btn" href="cart.php">
                                                                <svg class="product__list--action__cart--btn__icon" xmlns="http://www.w3.org/2000/svg" width="16.897" height="17.565" viewbox="0 0 18.897 21.565">
                                                                    <path d="M16.84,8.082V6.091a4.725,4.725,0,1,0-9.449,0v4.725a.675.675,0,0,0,1.35,0V9.432h5.4V8.082h-5.4V6.091a3.375,3.375,0,0,1,6.75,0v4.691a.675.675,0,1,0,1.35,0V9.433h3.374V21.581H4.017V9.432H6.041V8.082H2.667V21.641a1.289,1.289,0,0,0,1.289,1.29h16.32a1.289,1.289,0,0,0,1.289-1.29V8.082Z" transform="translate(-2.667 -1.366)" fill="currentColor"></path>
                                                                </svg>
                                                                <span class="product__list--action__cart--text"> Add To Cart</span>
                                                            </a>
                                                            <ul class="product__list--action__wrapper d-flex align-items-center">
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" data-open="modal1" href="javascript:void(0)">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="30.51" height="25.443" viewbox="0 0 512 512">
                                                                            <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path>
                                                                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Quick View</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="wishlist.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="24.403" height="20.204" viewbox="0 0 24.403 20.204">
                                                                            <g transform="translate(0)">
                                                                                <g data-name="Group 473" transform="translate(0 0)">
                                                                                    <path data-name="Path 242" d="M17.484,35.514h0a6.858,6.858,0,0,0-5.282,2.44,6.765,6.765,0,0,0-5.282-2.44A6.919,6.919,0,0,0,0,42.434c0,6.549,11.429,12.943,11.893,13.19a.556.556,0,0,0,.618,0c.463-.247,11.893-6.549,11.893-13.19A6.919,6.919,0,0,0,17.484,35.514ZM12.2,54.388C10.41,53.338,1.236,47.747,1.236,42.434A5.684,5.684,0,0,1,6.919,36.75a5.56,5.56,0,0,1,4.757,2.564.649.649,0,0,0,1.05,0,5.684,5.684,0,0,1,10.441,3.12C23.168,47.809,13.993,53.369,12.2,54.388Z" transform="translate(0 -35.514)" fill="currentColor"></path>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                        <span class="visually-hidden">Wishlist</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="compare.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="25.654" height="18.388" viewbox="0 0 25.654 18.388">
                                                                            <path data-name="Path 287" d="M25.47,86.417l-3.334-3.334a.871.871,0,0,0-.62-.257H18.724a.476.476,0,0,0-.337.813l1.833,1.833H17.538l-3.77-3.77,3.77-3.77h2.683l-1.833,1.834a.476.476,0,0,0,.337.812h2.791a.881.881,0,0,0,.62-.257l3.335-3.335a.63.63,0,0,0,0-.887l-1.424-1.424a.376.376,0,1,0-.531.532l1.337,1.336L21.6,79.79a.124.124,0,0,1-.088.036H19.389l1.748-1.748a.526.526,0,0,0-.372-.9H17.382a.376.376,0,0,0-.266.11l-3.88,3.881-.9-.9,4.177-4.177a.633.633,0,0,1,.45-.187h3.8a.526.526,0,0,0,.372-.9L19.39,73.26h2.126a.125.125,0,0,1,.089.037l.665.665a.376.376,0,1,0,.531-.532l-.665-.664a.881.881,0,0,0-.621-.258H18.724a.476.476,0,0,0-.337.812l1.833,1.833H16.962a1.379,1.379,0,0,0-.982.407L11.8,79.737,7.627,75.56a1.38,1.38,0,0,0-.982-.407H.626A.627.627,0,0,0,0,75.78v1.525a.627.627,0,0,0,.626.626H6.069l3.77,3.77-3.77,3.77H.626A.627.627,0,0,0,0,86.1v1.525a.627.627,0,0,0,.626.626H6.644a1.384,1.384,0,0,0,.982-.407L11.8,83.666,13.135,85a.376.376,0,0,0,.531-.531L6.49,77.29a.376.376,0,0,0-.266-.11H.752V75.9H6.644a.633.633,0,0,1,.451.187L17.116,86.114a.376.376,0,0,0,.266.11h3.383a.526.526,0,0,0,.372-.9L19.39,83.578h2.126a.125.125,0,0,1,.089.037l3.246,3.246L21.6,90.107a.125.125,0,0,1-.089.037H19.39L21.137,88.4a.526.526,0,0,0-.372-.9h-3.8a.635.635,0,0,1-.451-.187l-1.605-1.605a.376.376,0,1,0-.531.531l1.606,1.606a1.382,1.382,0,0,0,.982.407H20.22l-1.833,1.833a.476.476,0,0,0,.337.813h2.792a.871.871,0,0,0,.62-.257L25.47,87.3A.628.628,0,0,0,25.47,86.417ZM7.1,87.311a.645.645,0,0,1-.451.187H.752V86.224H6.225a.376.376,0,0,0,.266-.11l3.88-3.88.9.9Z" transform="translate(0 -72.508)" fill="currentColor"></path>
                                                                        </svg>
                                                                        <span class="visually-hidden">Compare</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col mb-30">
                                                <div class="product__items product__list--items border-radius-5 d-flex align-items-center">
                                                    <div class="product__list--items__left d-flex align-items-center">
                                                        <div class="product__items--thumbnail product__list--items__thumbnail">
                                                            <a class="product__items--link" href="product-details.php">
                                                                <img class="product__items--img product__primary--img" src="assets/img/product/product1.webp" alt="product-img">
                                                                <img class="product__items--img product__secondary--img" src="assets/img/product/product2.webp" alt="product-img">
                                                            </a>
                                                        </div>
                                                        <div class="product__list--items__content">
                                                            <span class="product__items--content__subtitle mb-5">Modern</span>
                                                            <h4 class="product__list--items__content--title mb-15"><a href="product-details.php">White Minimalist Combo Sofa</a></h4>
                                                            <p class="product__list--items__content--desc m-0">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quia voluptas dolore doloribus architecto sequi corporis deleniti officia culpa dolor esse consectetur eligendi.</p>
                                                        </div>
                                                    </div>
                                                    <div class="product__list--items__right">
                                                        <span class="product__list--current__price">$320.00</span>
                                                        <ul class="rating product__list--rating d-flex">
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list"><span class="rating__list--text">( 5.0)</span></li>
                                                        </ul>
                                                        <div class="product__list--action">
                                                            <a class="product__list--action__cart--btn primary__btn" href="cart.php">
                                                                <svg class="product__list--action__cart--btn__icon" xmlns="http://www.w3.org/2000/svg" width="16.897" height="17.565" viewbox="0 0 18.897 21.565">
                                                                    <path d="M16.84,8.082V6.091a4.725,4.725,0,1,0-9.449,0v4.725a.675.675,0,0,0,1.35,0V9.432h5.4V8.082h-5.4V6.091a3.375,3.375,0,0,1,6.75,0v4.691a.675.675,0,1,0,1.35,0V9.433h3.374V21.581H4.017V9.432H6.041V8.082H2.667V21.641a1.289,1.289,0,0,0,1.289,1.29h16.32a1.289,1.289,0,0,0,1.289-1.29V8.082Z" transform="translate(-2.667 -1.366)" fill="currentColor"></path>
                                                                </svg>

                                                                <span class="product__list--action__cart--text"> Add To Cart</span>
                                                            </a>
                                                            <ul class="product__list--action__wrapper d-flex align-items-center">
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" data-open="modal1" href="javascript:void(0)">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="30.51" height="25.443" viewbox="0 0 512 512">
                                                                            <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path>
                                                                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Quick View</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="wishlist.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="24.403" height="20.204" viewbox="0 0 24.403 20.204">
                                                                            <g transform="translate(0)">
                                                                                <g data-name="Group 473" transform="translate(0 0)">
                                                                                    <path data-name="Path 242" d="M17.484,35.514h0a6.858,6.858,0,0,0-5.282,2.44,6.765,6.765,0,0,0-5.282-2.44A6.919,6.919,0,0,0,0,42.434c0,6.549,11.429,12.943,11.893,13.19a.556.556,0,0,0,.618,0c.463-.247,11.893-6.549,11.893-13.19A6.919,6.919,0,0,0,17.484,35.514ZM12.2,54.388C10.41,53.338,1.236,47.747,1.236,42.434A5.684,5.684,0,0,1,6.919,36.75a5.56,5.56,0,0,1,4.757,2.564.649.649,0,0,0,1.05,0,5.684,5.684,0,0,1,10.441,3.12C23.168,47.809,13.993,53.369,12.2,54.388Z" transform="translate(0 -35.514)" fill="currentColor"></path>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                        <span class="visually-hidden">Wishlist</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="compare.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="25.654" height="18.388" viewbox="0 0 25.654 18.388">
                                                                            <path data-name="Path 287" d="M25.47,86.417l-3.334-3.334a.871.871,0,0,0-.62-.257H18.724a.476.476,0,0,0-.337.813l1.833,1.833H17.538l-3.77-3.77,3.77-3.77h2.683l-1.833,1.834a.476.476,0,0,0,.337.812h2.791a.881.881,0,0,0,.62-.257l3.335-3.335a.63.63,0,0,0,0-.887l-1.424-1.424a.376.376,0,1,0-.531.532l1.337,1.336L21.6,79.79a.124.124,0,0,1-.088.036H19.389l1.748-1.748a.526.526,0,0,0-.372-.9H17.382a.376.376,0,0,0-.266.11l-3.88,3.881-.9-.9,4.177-4.177a.633.633,0,0,1,.45-.187h3.8a.526.526,0,0,0,.372-.9L19.39,73.26h2.126a.125.125,0,0,1,.089.037l.665.665a.376.376,0,1,0,.531-.532l-.665-.664a.881.881,0,0,0-.621-.258H18.724a.476.476,0,0,0-.337.812l1.833,1.833H16.962a1.379,1.379,0,0,0-.982.407L11.8,79.737,7.627,75.56a1.38,1.38,0,0,0-.982-.407H.626A.627.627,0,0,0,0,75.78v1.525a.627.627,0,0,0,.626.626H6.069l3.77,3.77-3.77,3.77H.626A.627.627,0,0,0,0,86.1v1.525a.627.627,0,0,0,.626.626H6.644a1.384,1.384,0,0,0,.982-.407L11.8,83.666,13.135,85a.376.376,0,0,0,.531-.531L6.49,77.29a.376.376,0,0,0-.266-.11H.752V75.9H6.644a.633.633,0,0,1,.451.187L17.116,86.114a.376.376,0,0,0,.266.11h3.383a.526.526,0,0,0,.372-.9L19.39,83.578h2.126a.125.125,0,0,1,.089.037l3.246,3.246L21.6,90.107a.125.125,0,0,1-.089.037H19.39L21.137,88.4a.526.526,0,0,0-.372-.9h-3.8a.635.635,0,0,1-.451-.187l-1.605-1.605a.376.376,0,1,0-.531.531l1.606,1.606a1.382,1.382,0,0,0,.982.407H20.22l-1.833,1.833a.476.476,0,0,0,.337.813h2.792a.871.871,0,0,0,.62-.257L25.47,87.3A.628.628,0,0,0,25.47,86.417ZM7.1,87.311a.645.645,0,0,1-.451.187H.752V86.224H6.225a.376.376,0,0,0,.266-.11l3.88-3.88.9.9Z" transform="translate(0 -72.508)" fill="currentColor"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Compare</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col mb-30">
                                                <div class="product__items product__list--items border-radius-5 d-flex align-items-center">
                                                    <div class="product__list--items__left d-flex align-items-center">
                                                        <div class="product__items--thumbnail product__list--items__thumbnail">
                                                            <a class="product__items--link" href="product-details.php">
                                                                <img class="product__items--img product__primary--img" src="assets/img/product/product3.webp" alt="product-img">
                                                                <img class="product__items--img product__secondary--img" src="assets/img/product/product4.webp" alt="product-img">
                                                            </a>
                                                            <div class="product__badge">
                                                                <span class="product__badge--items sale">Sale</span>
                                                            </div>
                                                        </div>
                                                        <div class="product__list--items__content">
                                                            <span class="product__items--content__subtitle mb-5">Chair</span>
                                                            <h4 class="product__list--items__content--title mb-15"><a href="product-details.php">Modern Swivel Chair</a></h4>
                                                            <p class="product__list--items__content--desc m-0">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quia voluptas dolore doloribus architecto sequi corporis deleniti officia culpa dolor esse consectetur eligendi.</p>
                                                        </div>
                                                    </div>
                                                    <div class="product__list--items__right">
                                                        <span class="product__list--current__price">$280.00</span>
                                                        <ul class="rating product__list--rating d-flex">
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list"><span class="rating__list--text">( 5.0)</span></li>
                                                        </ul>
                                                        <div class="product__list--action">
                                                            <a class="product__list--action__cart--btn primary__btn" href="cart.php">
                                                                <svg class="product__list--action__cart--btn__icon" xmlns="http://www.w3.org/2000/svg" width="16.897" height="17.565" viewbox="0 0 18.897 21.565">
                                                                    <path d="M16.84,8.082V6.091a4.725,4.725,0,1,0-9.449,0v4.725a.675.675,0,0,0,1.35,0V9.432h5.4V8.082h-5.4V6.091a3.375,3.375,0,0,1,6.75,0v4.691a.675.675,0,1,0,1.35,0V9.433h3.374V21.581H4.017V9.432H6.041V8.082H2.667V21.641a1.289,1.289,0,0,0,1.289,1.29h16.32a1.289,1.289,0,0,0,1.289-1.29V8.082Z" transform="translate(-2.667 -1.366)" fill="currentColor"></path>
                                                                </svg>

                                                                <span class="product__list--action__cart--text"> Add To Cart</span>
                                                            </a>
                                                            <ul class="product__list--action__wrapper d-flex align-items-center">
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" data-open="modal1" href="javascript:void(0)">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="30.51" height="25.443" viewbox="0 0 512 512">
                                                                            <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path>
                                                                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Quick View</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="wishlist.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="24.403" height="20.204" viewbox="0 0 24.403 20.204">
                                                                            <g transform="translate(0)">
                                                                                <g data-name="Group 473" transform="translate(0 0)">
                                                                                    <path data-name="Path 242" d="M17.484,35.514h0a6.858,6.858,0,0,0-5.282,2.44,6.765,6.765,0,0,0-5.282-2.44A6.919,6.919,0,0,0,0,42.434c0,6.549,11.429,12.943,11.893,13.19a.556.556,0,0,0,.618,0c.463-.247,11.893-6.549,11.893-13.19A6.919,6.919,0,0,0,17.484,35.514ZM12.2,54.388C10.41,53.338,1.236,47.747,1.236,42.434A5.684,5.684,0,0,1,6.919,36.75a5.56,5.56,0,0,1,4.757,2.564.649.649,0,0,0,1.05,0,5.684,5.684,0,0,1,10.441,3.12C23.168,47.809,13.993,53.369,12.2,54.388Z" transform="translate(0 -35.514)" fill="currentColor"></path>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                        <span class="visually-hidden">Wishlist</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="compare.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="25.654" height="18.388" viewbox="0 0 25.654 18.388">
                                                                            <path data-name="Path 287" d="M25.47,86.417l-3.334-3.334a.871.871,0,0,0-.62-.257H18.724a.476.476,0,0,0-.337.813l1.833,1.833H17.538l-3.77-3.77,3.77-3.77h2.683l-1.833,1.834a.476.476,0,0,0,.337.812h2.791a.881.881,0,0,0,.62-.257l3.335-3.335a.63.63,0,0,0,0-.887l-1.424-1.424a.376.376,0,1,0-.531.532l1.337,1.336L21.6,79.79a.124.124,0,0,1-.088.036H19.389l1.748-1.748a.526.526,0,0,0-.372-.9H17.382a.376.376,0,0,0-.266.11l-3.88,3.881-.9-.9,4.177-4.177a.633.633,0,0,1,.45-.187h3.8a.526.526,0,0,0,.372-.9L19.39,73.26h2.126a.125.125,0,0,1,.089.037l.665.665a.376.376,0,1,0,.531-.532l-.665-.664a.881.881,0,0,0-.621-.258H18.724a.476.476,0,0,0-.337.812l1.833,1.833H16.962a1.379,1.379,0,0,0-.982.407L11.8,79.737,7.627,75.56a1.38,1.38,0,0,0-.982-.407H.626A.627.627,0,0,0,0,75.78v1.525a.627.627,0,0,0,.626.626H6.069l3.77,3.77-3.77,3.77H.626A.627.627,0,0,0,0,86.1v1.525a.627.627,0,0,0,.626.626H6.644a1.384,1.384,0,0,0,.982-.407L11.8,83.666,13.135,85a.376.376,0,0,0,.531-.531L6.49,77.29a.376.376,0,0,0-.266-.11H.752V75.9H6.644a.633.633,0,0,1,.451.187L17.116,86.114a.376.376,0,0,0,.266.11h3.383a.526.526,0,0,0,.372-.9L19.39,83.578h2.126a.125.125,0,0,1,.089.037l3.246,3.246L21.6,90.107a.125.125,0,0,1-.089.037H19.39L21.137,88.4a.526.526,0,0,0-.372-.9h-3.8a.635.635,0,0,1-.451-.187l-1.605-1.605a.376.376,0,1,0-.531.531l1.606,1.606a1.382,1.382,0,0,0,.982.407H20.22l-1.833,1.833a.476.476,0,0,0,.337.813h2.792a.871.871,0,0,0,.62-.257L25.47,87.3A.628.628,0,0,0,25.47,86.417ZM7.1,87.311a.645.645,0,0,1-.451.187H.752V86.224H6.225a.376.376,0,0,0,.266-.11l3.88-3.88.9.9Z" transform="translate(0 -72.508)" fill="currentColor"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Compare</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col mb-30">
                                                <div class="product__items product__list--items border-radius-5 d-flex align-items-center">
                                                    <div class="product__list--items__left d-flex align-items-center">
                                                        <div class="product__items--thumbnail product__list--items__thumbnail">
                                                            <a class="product__items--link" href="product-details.php">
                                                                <img class="product__items--img product__primary--img" src="assets/img/product/product5.webp" alt="product-img">
                                                                <img class="product__items--img product__secondary--img" src="assets/img/product/product6.webp" alt="product-img">
                                                            </a>
                                                        </div>
                                                        <div class="product__list--items__content">
                                                            <span class="product__items--content__subtitle mb-5">Wooden</span>
                                                            <h4 class="product__list--items__content--title mb-15"><a href="product-details.php">Modern Stylish Single Sofa</a></h4>
                                                            <p class="product__list--items__content--desc m-0">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quia voluptas dolore doloribus architecto sequi corporis deleniti officia culpa dolor esse consectetur eligendi.</p>
                                                        </div>
                                                    </div>
                                                    <div class="product__list--items__right">
                                                        <span class="product__list--current__price">$255.00</span>
                                                        <ul class="rating product__list--rating d-flex">
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list"><span class="rating__list--text">( 5.0)</span></li>
                                                        </ul>
                                                        <div class="product__list--action">
                                                            <a class="product__list--action__cart--btn primary__btn" href="cart.php">
                                                                <svg class="product__list--action__cart--btn__icon" xmlns="http://www.w3.org/2000/svg" width="16.897" height="17.565" viewbox="0 0 18.897 21.565">
                                                                    <path d="M16.84,8.082V6.091a4.725,4.725,0,1,0-9.449,0v4.725a.675.675,0,0,0,1.35,0V9.432h5.4V8.082h-5.4V6.091a3.375,3.375,0,0,1,6.75,0v4.691a.675.675,0,1,0,1.35,0V9.433h3.374V21.581H4.017V9.432H6.041V8.082H2.667V21.641a1.289,1.289,0,0,0,1.289,1.29h16.32a1.289,1.289,0,0,0,1.289-1.29V8.082Z" transform="translate(-2.667 -1.366)" fill="currentColor"></path>
                                                                </svg>

                                                                <span class="product__list--action__cart--text"> Add To Cart</span>
                                                            </a>
                                                            <ul class="product__list--action__wrapper d-flex align-items-center">
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" data-open="modal1" href="javascript:void(0)">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="30.51" height="25.443" viewbox="0 0 512 512">
                                                                            <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path>
                                                                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Quick View</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="wishlist.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="24.403" height="20.204" viewbox="0 0 24.403 20.204">
                                                                            <g transform="translate(0)">
                                                                                <g data-name="Group 473" transform="translate(0 0)">
                                                                                    <path data-name="Path 242" d="M17.484,35.514h0a6.858,6.858,0,0,0-5.282,2.44,6.765,6.765,0,0,0-5.282-2.44A6.919,6.919,0,0,0,0,42.434c0,6.549,11.429,12.943,11.893,13.19a.556.556,0,0,0,.618,0c.463-.247,11.893-6.549,11.893-13.19A6.919,6.919,0,0,0,17.484,35.514ZM12.2,54.388C10.41,53.338,1.236,47.747,1.236,42.434A5.684,5.684,0,0,1,6.919,36.75a5.56,5.56,0,0,1,4.757,2.564.649.649,0,0,0,1.05,0,5.684,5.684,0,0,1,10.441,3.12C23.168,47.809,13.993,53.369,12.2,54.388Z" transform="translate(0 -35.514)" fill="currentColor"></path>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                        <span class="visually-hidden">Wishlist</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="compare.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="25.654" height="18.388" viewbox="0 0 25.654 18.388">
                                                                            <path data-name="Path 287" d="M25.47,86.417l-3.334-3.334a.871.871,0,0,0-.62-.257H18.724a.476.476,0,0,0-.337.813l1.833,1.833H17.538l-3.77-3.77,3.77-3.77h2.683l-1.833,1.834a.476.476,0,0,0,.337.812h2.791a.881.881,0,0,0,.62-.257l3.335-3.335a.63.63,0,0,0,0-.887l-1.424-1.424a.376.376,0,1,0-.531.532l1.337,1.336L21.6,79.79a.124.124,0,0,1-.088.036H19.389l1.748-1.748a.526.526,0,0,0-.372-.9H17.382a.376.376,0,0,0-.266.11l-3.88,3.881-.9-.9,4.177-4.177a.633.633,0,0,1,.45-.187h3.8a.526.526,0,0,0,.372-.9L19.39,73.26h2.126a.125.125,0,0,1,.089.037l.665.665a.376.376,0,1,0,.531-.532l-.665-.664a.881.881,0,0,0-.621-.258H18.724a.476.476,0,0,0-.337.812l1.833,1.833H16.962a1.379,1.379,0,0,0-.982.407L11.8,79.737,7.627,75.56a1.38,1.38,0,0,0-.982-.407H.626A.627.627,0,0,0,0,75.78v1.525a.627.627,0,0,0,.626.626H6.069l3.77,3.77-3.77,3.77H.626A.627.627,0,0,0,0,86.1v1.525a.627.627,0,0,0,.626.626H6.644a1.384,1.384,0,0,0,.982-.407L11.8,83.666,13.135,85a.376.376,0,0,0,.531-.531L6.49,77.29a.376.376,0,0,0-.266-.11H.752V75.9H6.644a.633.633,0,0,1,.451.187L17.116,86.114a.376.376,0,0,0,.266.11h3.383a.526.526,0,0,0,.372-.9L19.39,83.578h2.126a.125.125,0,0,1,.089.037l3.246,3.246L21.6,90.107a.125.125,0,0,1-.089.037H19.39L21.137,88.4a.526.526,0,0,0-.372-.9h-3.8a.635.635,0,0,1-.451-.187l-1.605-1.605a.376.376,0,1,0-.531.531l1.606,1.606a1.382,1.382,0,0,0,.982.407H20.22l-1.833,1.833a.476.476,0,0,0,.337.813h2.792a.871.871,0,0,0,.62-.257L25.47,87.3A.628.628,0,0,0,25.47,86.417ZM7.1,87.311a.645.645,0,0,1-.451.187H.752V86.224H6.225a.376.376,0,0,0,.266-.11l3.88-3.88.9.9Z" transform="translate(0 -72.508)" fill="currentColor"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Compare</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col mb-30">
                                                <div class="product__items product__list--items border-radius-5 d-flex align-items-center">
                                                    <div class="product__list--items__left d-flex align-items-center">
                                                        <div class="product__items--thumbnail product__list--items__thumbnail">
                                                            <a class="product__items--link" href="product-details.php">
                                                                <img class="product__items--img product__primary--img" src="assets/img/product/product7.webp" alt="product-img">
                                                                <img class="product__items--img product__secondary--img" src="assets/img/product/product8.webp" alt="product-img">
                                                            </a>
                                                            <div class="product__badge">
                                                                <span class="product__badge--items sale">Sale</span>
                                                            </div>
                                                        </div>
                                                        <div class="product__list--items__content">
                                                            <span class="product__items--content__subtitle mb-5">Plastic</span>
                                                            <h4 class="product__list--items__content--title mb-15"><a href="product-details.php">New Furniture Tree Planet</a></h4>
                                                            <p class="product__list--items__content--desc m-0">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quia voluptas dolore doloribus architecto sequi corporis deleniti officia culpa dolor esse consectetur eligendi.</p>
                                                        </div>
                                                    </div>
                                                    <div class="product__list--items__right">
                                                        <span class="product__list--current__price">$275.00</span>
                                                        <ul class="rating product__list--rating d-flex">
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list"><span class="rating__list--text">( 5.0)</span></li>
                                                        </ul>
                                                        <div class="product__list--action">
                                                            <a class="product__list--action__cart--btn primary__btn" href="cart.php">
                                                                <svg class="product__list--action__cart--btn__icon" xmlns="http://www.w3.org/2000/svg" width="16.897" height="17.565" viewbox="0 0 18.897 21.565">
                                                                    <path d="M16.84,8.082V6.091a4.725,4.725,0,1,0-9.449,0v4.725a.675.675,0,0,0,1.35,0V9.432h5.4V8.082h-5.4V6.091a3.375,3.375,0,0,1,6.75,0v4.691a.675.675,0,1,0,1.35,0V9.433h3.374V21.581H4.017V9.432H6.041V8.082H2.667V21.641a1.289,1.289,0,0,0,1.289,1.29h16.32a1.289,1.289,0,0,0,1.289-1.29V8.082Z" transform="translate(-2.667 -1.366)" fill="currentColor"></path>
                                                                </svg>

                                                                <span class="product__list--action__cart--text"> Add To Cart</span>
                                                            </a>
                                                            <ul class="product__list--action__wrapper d-flex align-items-center">
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" data-open="modal1" href="javascript:void(0)">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="30.51" height="25.443" viewbox="0 0 512 512">
                                                                            <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path>
                                                                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Quick View</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="wishlist.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="24.403" height="20.204" viewbox="0 0 24.403 20.204">
                                                                            <g transform="translate(0)">
                                                                                <g data-name="Group 473" transform="translate(0 0)">
                                                                                    <path data-name="Path 242" d="M17.484,35.514h0a6.858,6.858,0,0,0-5.282,2.44,6.765,6.765,0,0,0-5.282-2.44A6.919,6.919,0,0,0,0,42.434c0,6.549,11.429,12.943,11.893,13.19a.556.556,0,0,0,.618,0c.463-.247,11.893-6.549,11.893-13.19A6.919,6.919,0,0,0,17.484,35.514ZM12.2,54.388C10.41,53.338,1.236,47.747,1.236,42.434A5.684,5.684,0,0,1,6.919,36.75a5.56,5.56,0,0,1,4.757,2.564.649.649,0,0,0,1.05,0,5.684,5.684,0,0,1,10.441,3.12C23.168,47.809,13.993,53.369,12.2,54.388Z" transform="translate(0 -35.514)" fill="currentColor"></path>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                        <span class="visually-hidden">Wishlist</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="compare.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="25.654" height="18.388" viewbox="0 0 25.654 18.388">
                                                                            <path data-name="Path 287" d="M25.47,86.417l-3.334-3.334a.871.871,0,0,0-.62-.257H18.724a.476.476,0,0,0-.337.813l1.833,1.833H17.538l-3.77-3.77,3.77-3.77h2.683l-1.833,1.834a.476.476,0,0,0,.337.812h2.791a.881.881,0,0,0,.62-.257l3.335-3.335a.63.63,0,0,0,0-.887l-1.424-1.424a.376.376,0,1,0-.531.532l1.337,1.336L21.6,79.79a.124.124,0,0,1-.088.036H19.389l1.748-1.748a.526.526,0,0,0-.372-.9H17.382a.376.376,0,0,0-.266.11l-3.88,3.881-.9-.9,4.177-4.177a.633.633,0,0,1,.45-.187h3.8a.526.526,0,0,0,.372-.9L19.39,73.26h2.126a.125.125,0,0,1,.089.037l.665.665a.376.376,0,1,0,.531-.532l-.665-.664a.881.881,0,0,0-.621-.258H18.724a.476.476,0,0,0-.337.812l1.833,1.833H16.962a1.379,1.379,0,0,0-.982.407L11.8,79.737,7.627,75.56a1.38,1.38,0,0,0-.982-.407H.626A.627.627,0,0,0,0,75.78v1.525a.627.627,0,0,0,.626.626H6.069l3.77,3.77-3.77,3.77H.626A.627.627,0,0,0,0,86.1v1.525a.627.627,0,0,0,.626.626H6.644a1.384,1.384,0,0,0,.982-.407L11.8,83.666,13.135,85a.376.376,0,0,0,.531-.531L6.49,77.29a.376.376,0,0,0-.266-.11H.752V75.9H6.644a.633.633,0,0,1,.451.187L17.116,86.114a.376.376,0,0,0,.266.11h3.383a.526.526,0,0,0,.372-.9L19.39,83.578h2.126a.125.125,0,0,1,.089.037l3.246,3.246L21.6,90.107a.125.125,0,0,1-.089.037H19.39L21.137,88.4a.526.526,0,0,0-.372-.9h-3.8a.635.635,0,0,1-.451-.187l-1.605-1.605a.376.376,0,1,0-.531.531l1.606,1.606a1.382,1.382,0,0,0,.982.407H20.22l-1.833,1.833a.476.476,0,0,0,.337.813h2.792a.871.871,0,0,0,.62-.257L25.47,87.3A.628.628,0,0,0,25.47,86.417ZM7.1,87.311a.645.645,0,0,1-.451.187H.752V86.224H6.225a.376.376,0,0,0,.266-.11l3.88-3.88.9.9Z" transform="translate(0 -72.508)" fill="currentColor"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Compare</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col mb-30">
                                                <div class="product__items product__list--items border-radius-5 d-flex align-items-center">
                                                    <div class="product__list--items__left d-flex align-items-center">
                                                        <div class="product__items--thumbnail product__list--items__thumbnail">
                                                            <a class="product__items--link" href="product-details.php">
                                                                <img class="product__items--img product__primary--img" src="assets/img/product/product9.webp" alt="product-img">
                                                                <img class="product__items--img product__secondary--img" src="assets/img/product/product10.webp" alt="product-img">
                                                            </a>
                                                        </div>
                                                        <div class="product__list--items__content">
                                                            <span class="product__items--content__subtitle mb-5">Wooden</span>
                                                            <h4 class="product__list--items__content--title mb-15"><a href="product-details.php">Single Stylish Mini Chair</a></h4>
                                                            <p class="product__list--items__content--desc m-0">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quia voluptas dolore doloribus architecto sequi corporis deleniti officia culpa dolor esse consectetur eligendi.</p>
                                                        </div>
                                                    </div>
                                                    <div class="product__list--items__right">
                                                        <span class="product__list--current__price">$310.00</span>
                                                        <ul class="rating product__list--rating d-flex">
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list">
                                                                <span class="rating__list--icon">
                                                                    <svg class="rating__list--icon__svg" xmlns="http://www.w3.org/2000/svg" width="11.105" height="11.732" viewbox="0 0 10.105 9.732">
                                                                        <path data-name="star - Copy" d="M9.837,3.5,6.73,3.039,5.338.179a.335.335,0,0,0-.571,0L3.375,3.039.268,3.5a.3.3,0,0,0-.178.514L2.347,6.242,1.813,9.4a.314.314,0,0,0,.464.316L5.052,8.232,7.827,9.712A.314.314,0,0,0,8.292,9.4L7.758,6.242l2.257-2.231A.3.3,0,0,0,9.837,3.5Z" transform="translate(0 -0.018)" fill="currentColor"></path>
                                                                    </svg>
                                                                </span>
                                                            </li>
                                                            <li class="rating__list"><span class="rating__list--text">( 5.0)</span></li>
                                                        </ul>
                                                        <div class="product__list--action">
                                                            <a class="product__list--action__cart--btn primary__btn" href="cart.php">
                                                                <svg class="product__list--action__cart--btn__icon" xmlns="http://www.w3.org/2000/svg" width="16.897" height="17.565" viewbox="0 0 18.897 21.565">
                                                                    <path d="M16.84,8.082V6.091a4.725,4.725,0,1,0-9.449,0v4.725a.675.675,0,0,0,1.35,0V9.432h5.4V8.082h-5.4V6.091a3.375,3.375,0,0,1,6.75,0v4.691a.675.675,0,1,0,1.35,0V9.433h3.374V21.581H4.017V9.432H6.041V8.082H2.667V21.641a1.289,1.289,0,0,0,1.289,1.29h16.32a1.289,1.289,0,0,0,1.289-1.29V8.082Z" transform="translate(-2.667 -1.366)" fill="currentColor"></path>
                                                                </svg>

                                                                <span class="product__list--action__cart--text"> Add To Cart</span>
                                                            </a>
                                                            <ul class="product__list--action__wrapper d-flex align-items-center">
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" data-open="modal1" href="javascript:void(0)">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="30.51" height="25.443" viewbox="0 0 512 512">
                                                                            <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path>
                                                                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Quick View</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="wishlist.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="24.403" height="20.204" viewbox="0 0 24.403 20.204">
                                                                            <g transform="translate(0)">
                                                                                <g data-name="Group 473" transform="translate(0 0)">
                                                                                    <path data-name="Path 242" d="M17.484,35.514h0a6.858,6.858,0,0,0-5.282,2.44,6.765,6.765,0,0,0-5.282-2.44A6.919,6.919,0,0,0,0,42.434c0,6.549,11.429,12.943,11.893,13.19a.556.556,0,0,0,.618,0c.463-.247,11.893-6.549,11.893-13.19A6.919,6.919,0,0,0,17.484,35.514ZM12.2,54.388C10.41,53.338,1.236,47.747,1.236,42.434A5.684,5.684,0,0,1,6.919,36.75a5.56,5.56,0,0,1,4.757,2.564.649.649,0,0,0,1.05,0,5.684,5.684,0,0,1,10.441,3.12C23.168,47.809,13.993,53.369,12.2,54.388Z" transform="translate(0 -35.514)" fill="currentColor"></path>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                        <span class="visually-hidden">Wishlist</span>
                                                                    </a>
                                                                </li>
                                                                <li class="product__list--action__child">
                                                                    <a class="product__list--action__btn" href="compare.php">
                                                                        <svg class="product__list--action__btn--svg" xmlns="http://www.w3.org/2000/svg" width="25.654" height="18.388" viewbox="0 0 25.654 18.388">
                                                                            <path data-name="Path 287" d="M25.47,86.417l-3.334-3.334a.871.871,0,0,0-.62-.257H18.724a.476.476,0,0,0-.337.813l1.833,1.833H17.538l-3.77-3.77,3.77-3.77h2.683l-1.833,1.834a.476.476,0,0,0,.337.812h2.791a.881.881,0,0,0,.62-.257l3.335-3.335a.63.63,0,0,0,0-.887l-1.424-1.424a.376.376,0,1,0-.531.532l1.337,1.336L21.6,79.79a.124.124,0,0,1-.088.036H19.389l1.748-1.748a.526.526,0,0,0-.372-.9H17.382a.376.376,0,0,0-.266.11l-3.88,3.881-.9-.9,4.177-4.177a.633.633,0,0,1,.45-.187h3.8a.526.526,0,0,0,.372-.9L19.39,73.26h2.126a.125.125,0,0,1,.089.037l.665.665a.376.376,0,1,0,.531-.532l-.665-.664a.881.881,0,0,0-.621-.258H18.724a.476.476,0,0,0-.337.812l1.833,1.833H16.962a1.379,1.379,0,0,0-.982.407L11.8,79.737,7.627,75.56a1.38,1.38,0,0,0-.982-.407H.626A.627.627,0,0,0,0,75.78v1.525a.627.627,0,0,0,.626.626H6.069l3.77,3.77-3.77,3.77H.626A.627.627,0,0,0,0,86.1v1.525a.627.627,0,0,0,.626.626H6.644a1.384,1.384,0,0,0,.982-.407L11.8,83.666,13.135,85a.376.376,0,0,0,.531-.531L6.49,77.29a.376.376,0,0,0-.266-.11H.752V75.9H6.644a.633.633,0,0,1,.451.187L17.116,86.114a.376.376,0,0,0,.266.11h3.383a.526.526,0,0,0,.372-.9L19.39,83.578h2.126a.125.125,0,0,1,.089.037l3.246,3.246L21.6,90.107a.125.125,0,0,1-.089.037H19.39L21.137,88.4a.526.526,0,0,0-.372-.9h-3.8a.635.635,0,0,1-.451-.187l-1.605-1.605a.376.376,0,1,0-.531.531l1.606,1.606a1.382,1.382,0,0,0,.982.407H20.22l-1.833,1.833a.476.476,0,0,0,.337.813h2.792a.871.871,0,0,0,.62-.257L25.47,87.3A.628.628,0,0,0,25.47,86.417ZM7.1,87.311a.645.645,0,0,1-.451.187H.752V86.224H6.225a.376.376,0,0,0,.266-.11l3.88-3.88.9.9Z" transform="translate(0 -72.508)" fill="currentColor"></path>
                                                                        </svg>

                                                                        <span class="visually-hidden">Compare</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pagination__area bg__gray--color">
                                <nav class="pagination">
                                    <ul class="pagination__wrapper d-flex align-items-center justify-content-center">
                                        <li class="pagination__list"><a href="shop.php" class="pagination__item--arrow  link ">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22.51" height="20.443" viewbox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="48" d="M244 400L100 256l144-144M120 256h292"></path>
                                                </svg></a>
                                        <li>
                                        <li class="pagination__list"><span class="pagination__item pagination__item--current">1</span></li>
                                        <li class="pagination__list"><a href="shop.php" class="pagination__item link">2</a></li>
                                        <li class="pagination__list"><a href="shop.php" class="pagination__item link">3</a></li>
                                        <li class="pagination__list"><a href="shop.php" class="pagination__item link">4</a></li>
                                        <li class="pagination__list"><a href="shop.php" class="pagination__item--arrow  link ">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22.51" height="20.443" viewbox="0 0 512 512">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="48" d="M268 112l144 144-144 144M392 256H100"></path>
                                                </svg></a>
                                        <li>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
    <!-- End shop section -->

    <!-- Start Newsletter banner section -->
    <section class="newsletter__banner--section section--padding pt-0">
        <div class="container-fluid">
            <div class="newsletter__banner--thumbnail position__relative">
                <img class="newsletter__banner--thumbnail__img" src="assets/img/banner/banner-bg2.webp" alt="newsletter-banner">
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


<!-- serch_shop_products  -->
<script>
    $(document).ready(function() {
        $('#shop-search-form').on('submit', function(e) {
            e.preventDefault(); // stop form from reloading

            let searchQuery = $('#shop-search-input').val();

            $.ajax({
                url: 'search_shop_products.php',
                method: 'POST',
                data: {
                    query: searchQuery
                },
                success: function(response) {
                    $('#product-list').html(response); // ✅ only replace products
                }

            });
        });
    });
</script>