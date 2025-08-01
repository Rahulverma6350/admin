<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Furea - Furniture </title>
  <meta name="description" content="Morden Bootstrap HTML5 Template">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">
    
   <!-- ======= All CSS Plugins here ======== -->
  <link rel="stylesheet" href="assets/css/plugins/swiper-bundle.min.css">
  <link rel="stylesheet" href="assets/css/plugins/glightbox.min.css">
  <link href="../../../css2.css?family=Josefin+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">

  <!-- Plugin css -->
  <!-- <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css"> -->
  
  <!-- Custom Style CSS -->
  <link rel="stylesheet" href="assets/css/style.css">

   <!-- ajax  -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   
 <style>
  .user-auth-status {
    display: flex;
    align-items: center;
}

.user-trigger {
    
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

.arrow {
    font-size: 12px;
    margin-left: 6px;
}

.user-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    display: none;
    min-width: 160px;
    z-index: 999;
}

.user-dropdown a {
    display: block;
    padding: 10px 14px;
    text-decoration: none;
    color: #333;
}

.user-dropdown a:hover {
    background-color: #f0f0f0;
}
.arrow-icon {
    font-size: 12px;
    margin-left: 6px;
    transition: transform 0.3s ease; /* animate rotation */
}

/* ✅ Show dropdown on hover */
.user-auth-status:hover .user-dropdown {
    display: block;
}
.user-auth-status:hover .arrow-icon {
    transform: rotate(180deg);
}

 </style>
</head>

<body>

    <!-- Start header area -->
    <header class="header__section header__transparent">
        <!-- Start Header topbar -->
        <div class="header__topbar bg__primary">
            <div class="container-fluid">
                <div class="header__topbar--inner d-flex align-items-center justify-content-between">
                    <div class="header__shipping">
                        <p class="header__shipping--text text-white">Get Up To 80% off In your first Offer!</p>
                    </div>
                   
                </div>
            </div>
        </div>
        <!-- Start Header topbar -->

        <!-- Start main header -->
        <div class="main__header header__sticky">
            <div class="container-fluid">
                <div class="main__header--inner position__relative d-flex justify-content-between align-items-center">
                    <div class="offcanvas__header--menu__open ">
                        <a class="offcanvas__header--menu__open--btn" href="javascript:void(0)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon offcanvas__header--menu__open--svg" viewbox="0 0 512 512"><path fill="currentColor" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M80 160h352M80 256h352M80 352h352"></path></svg>
                            <span class="visually-hidden">Offcanvas Menu Open</span>
                        </a>
                    </div>
                    <div class="main__logo">
                        <h1 class="main__logo--title"><a class="main__logo--link" href="index.php"><img class="main__logo--img" src="assets/img/logo/nav-log.webp" alt="logo-img"></a></h1>
                    </div>
                    <div class="header__menu d-none d-lg-block">
                    <nav class="header__menu--navigation">

                    <?php
$currentPage = basename($_SERVER['PHP_SELF']);
                    ?>
    <ul class="d-flex">
        <li class="header__menu--items">
            <a class="header__menu--link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="header__menu--items mega__menu--items">
            <a class="header__menu--link <?php echo ($currentPage == 'shop.php') ? 'active' : ''; ?>" href="shop.php">Shop</a>
        </li>
        <li class="header__menu--items">
            <a class="header__menu--link <?php echo ($currentPage == 'about.php') ? 'active' : ''; ?>" href="about.php">About Us</a>
        </li>
        <li class="header__menu--items">
            <a class="header__menu--link <?php echo ($currentPage == 'blog.php') ? 'active' : ''; ?>" href="blog.php">Blog</a>
        </li>
        <li class="header__menu--items">
            <a class="header__menu--link <?php echo ($currentPage == 'contact.php') ? 'active' : ''; ?>" href="contact.php">Contact</a>
        </li>
    </ul>
</nav>

                    </div>
                <div class="user-auth-status">
    <?php if (isset($_SESSION['name'])): ?>
        <div class="user-trigger">
             <?php echo htmlspecialchars($_SESSION['name']); ?>
            <svg class="arrow-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M5 7L10 12L15 7" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

        </div>
        <div class="user-dropdown">
            <a href="my-account.php">My Account</a>
            <a href="my-account.php">My Orders</a>
            <a href="logout.php">Logout</a>
        </div>
    <?php else: ?>
        <a href="login.php" class="btn btn-info new-btn btn-sm">Login</a>
    <?php endif; ?>
</div>

                    <div class="header__account">
                        <ul class="d-flex">
                            <li class="header__account--items  header__account--search__items d-md-none">
                                <a class="header__account--btn search__open--btn" href="javascript:void(0)">
                                    <svg class="header__search--button__svg" xmlns="http://www.w3.org/2000/svg" width="26.51" height="23.443" viewbox="0 0 512 512"><path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path></svg>  
                                    <span class="visually-hidden">Search</span>
                                </a>
                            </li>
                            <li class="header__account--items <?php echo basename($_SERVER['PHP_SELF']) == 'my-account.php' ? 'active' : ''; ?>">
    <a class="header__account--btn" href="my-account.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="26.51" height="23.443" viewBox="0 0 512 512">
            <path d="M344 144c-3.92 52.87-44 96-88 96s-84.15-43.12-88-96c-4-55 35-96 88-96s92 42 88 96z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"></path>
            <path d="M256 304c-87 0-175.3 48-191.64 138.6C62.39 453.52 68.57 464 80 464h352c11.44 0 17.62-10.48 15.65-21.4C431.3 352 343 304 256 304z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path>
        </svg> 
        <span class="visually-hidden">My Account</span>
    </a>
</li>

                            <li class="header__account--items d-md-none">
                                <a class="header__account--btn" href="wishlist.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24.526" height="21.82" viewbox="0 0 24.526 21.82">
                                        <path d="M12.263,21.82a1.438,1.438,0,0,1-.948-.356c-.991-.866-1.946-1.681-2.789-2.4l0,0a51.865,51.865,0,0,1-6.089-5.715A9.129,9.129,0,0,1,0,7.371,7.666,7.666,0,0,1,1.946,2.135,6.6,6.6,0,0,1,6.852,0a6.169,6.169,0,0,1,3.854,1.33,7.884,7.884,0,0,1,1.558,1.627A7.885,7.885,0,0,1,13.821,1.33,6.169,6.169,0,0,1,17.675,0,6.6,6.6,0,0,1,22.58,2.135a7.665,7.665,0,0,1,1.945,5.235,9.128,9.128,0,0,1-2.432,5.975,51.86,51.86,0,0,1-6.089,5.715c-.844.719-1.8,1.535-2.794,2.4a1.439,1.439,0,0,1-.948.356ZM6.852,1.437A5.174,5.174,0,0,0,3,3.109,6.236,6.236,0,0,0,1.437,7.371a7.681,7.681,0,0,0,2.1,5.059,51.039,51.039,0,0,0,5.915,5.539l0,0c.846.721,1.8,1.538,2.8,2.411,1-.874,1.965-1.693,2.812-2.415a51.052,51.052,0,0,0,5.914-5.538,7.682,7.682,0,0,0,2.1-5.059,6.236,6.236,0,0,0-1.565-4.262,5.174,5.174,0,0,0-3.85-1.672A4.765,4.765,0,0,0,14.7,2.467a6.971,6.971,0,0,0-1.658,1.918.907.907,0,0,1-1.558,0A6.965,6.965,0,0,0,9.826,2.467a4.765,4.765,0,0,0-2.975-1.03Zm0,0" transform="translate(0 0)" fill="currentColor"></path>
                                    </svg>
                                      
                                    <span class="items__count wishlist" id="wishlistCountValue">0</span> 
                                </a>
                            </li>
                            <li class="header__account--items">
                                <a class="header__account--btn minicart__open--btn" href="cart.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18.897" height="21.565" viewbox="0 0 18.897 21.565">
                                        <path d="M16.84,8.082V6.091a4.725,4.725,0,1,0-9.449,0v4.725a.675.675,0,0,0,1.35,0V9.432h5.4V8.082h-5.4V6.091a3.375,3.375,0,0,1,6.75,0v4.691a.675.675,0,1,0,1.35,0V9.433h3.374V21.581H4.017V9.432H6.041V8.082H2.667V21.641a1.289,1.289,0,0,0,1.289,1.29h16.32a1.289,1.289,0,0,0,1.289-1.29V8.082Z" transform="translate(-2.667 -1.366)" fill="currentColor"></path>
                                    </svg>
                                    <span class="items__count" id="cartCountValue">0</span> 
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- End main header -->

        <!-- Start Offcanvas header menu -->
        <!-- End Offcanvas header menu -->

        <!-- Start Offcanvas stikcy toolbar -->
              <!-- End Offcanvas stikcy toolbar -->
 
        <!-- Start offCanvas minicart -->     
        <!-- End offCanvas minicart -->

        

        <!-- Start serch box area -->
        <div class="predictive__search--box " tabindex="-1">
            <div class="predictive__search--box__inner">
                <h2 class="predictive__search--title">Search Products</h2>
                <form class="predictive__search--form" action="#">
                    <label>
                    <input class="predictive__search--input" id="searchInput" placeholder="Search Here" type="text" autocomplete="off">
<div id="searchResults" class="live-search-results"></div>
</label>

                    <button class="predictive__search--button" aria-label="search button"><svg class="header__search--button__svg" xmlns="http://www.w3.org/2000/svg" width="30.51" height="25.443" viewbox="0 0 512 512"><path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path></svg>  </button>
                </form>
            </div>
            <button class="predictive__search--close__btn" aria-label="search close btn">
                <svg class="predictive__search--close__icon" xmlns="http://www.w3.org/2000/svg" width="40.51" height="30.443" viewbox="0 0 512 512"><path fill="currentColor" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M368 368L144 144M368 144L144 368"></path></svg>
            </button>
        </div>
        <!-- End serch box area -->
    </header>
        <!-- End header area -->

        <script>
            $(document).ready(function() {
    updateWishlistCount(); // Ensure count updates on page load
});

        </script>
        <script>
            $(document).ready(function() {
                updateCartCount(); // Ensure count updates on page load
});

        </script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#searchInput').on('keyup', function () {
        let query = $(this).val();
        if (query.length > 1) {
            $.ajax({
                url: 'search_products.php',
                method: 'POST',
                data: { query: query },
                success: function (data) {
                    $('#searchResults').html(data).show();
                }
            });
        } else {
            $('#searchResults').hide();
        }
    });

    // Handle click on search suggestion
    $(document).on('click', '.search-item', function () {
        let productId = $(this).data('id');
        window.location.href = 'product-details.php?id=' + productId;
    });
});
</script>
