<?php
session_start();
include('include/db.php');
$get = $_GET['cid'];
$blog_data = "SELECT * FROM `blog` WHERE id = '$get'";
$result = mysqli_query($conn, $blog_data);
$fetch = mysqli_fetch_assoc($result);
// Extract title and image from blog data
$product_name = mysqli_real_escape_string($conn, $fetch['tittle']);
$product_image = mysqli_real_escape_string($conn, $fetch['img']); // Adjust key if your image field is named differently
// Fetch related blogs (excluding current one)
$related_query = "SELECT * FROM `blog` WHERE id != '$get' ORDER BY date DESC LIMIT 2";
$related_result = mysqli_query($conn, $related_query);


if (isset($_POST['submitReview'])) {
    // Get values from form and sanitize them
    $product_id = $_GET['cid']; // From URL
    $user_id = $_SESSION['user_id']; // Logged-in user ID
    // Collect and sanitize form inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $review = mysqli_real_escape_string($conn, $_POST['review']);
    $status = 1; // Approved by default
    $added_on = date('Y-m-d H:i:s');
    // Insert query with blog title and image
    $sql = "INSERT INTO products_review
    (product_id, user_id, name, email, rating, review, status, added_on, products_title, products_img)
    VALUES
    ('$product_id', '$user_id', '$name', '$email', '$rating', '$review', '$status', '$added_on', '$product_name', '$product_image')";
    // Execute and handle result
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>Review submitted successfully!</p>";
        // Redirect to prevent resubmission
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
    }
}


// Blog-table
$product_id = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
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
include('header.php');
?>

<main class="main__content_wrapper">

    <!-- Start breadcrumb section -->
    <section class="breadcrumb__section breadcrumb__bg">
        <div class="container">
            <div class="row row-cols-1">
                <div class="col">
                    <div class="breadcrumb__content">
                        <h1 class="breadcrumb__content--title text-white mb-10">Blog Details</h1>
                        <ul class="breadcrumb__content--menu d-flex">
                            <li class="breadcrumb__content--menu__items"><a class="text-white" href="index.php">Home</a></li>
                            <li class="breadcrumb__content--menu__items"><span class="text-white">Blog Details</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End breadcrumb section -->

    <!-- Start blog details section -->
    <section class="blog__details--section section--padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="blog__details--wrapper">

                        <div class="entry__blog">
                            <div class="blog__post--header mb-30">
                                <h2 class="post__header--title mb-15"><?php echo $fetch['tittle']; ?></h2>
                                <p class="blog__post--meta">Posted by : admin / On : <?php echo date("F j, Y", strtotime($fetch['date'])); ?> / In : <a class="blog__post--meta__link" href="blog-details.php">Company, Image, Travel</a></p>
                            </div>
                            <div class="blog__thumbnail mb-30">
                                <img class="blog__thumbnail--img border-radius-10" style="height: 510px;
                                object-fit: cover;" src="admin/img/<?php echo $fetch['img']; ?>" alt="blog-img">
                            </div>
                            <div class="blog__details--content">
                                <p>
                                    <?php echo $fetch['summernote']; ?>
                                </p>
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
                                    <form action="blog-details.php?cid=<?php echo htmlspecialchars($_GET['cid']); ?>" method="post" id="reviewForm">
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








                <!-- Search button -->
                <!-- <div class="col-lg-4">
                    <div class="blog__sidebar--widget left widget__area">
                        <div class="single__widget widget__bg">
                            <h2 class="widget__title position__relative h3">Search</h2>
                            <form class="widget__search--form" action="#">
                                <label>
                                    <input class="widget__search--form__input border-0" placeholder="Search by" type="text">
                                </label>
                                <button class="widget__search--form__btn" type="submit">
                                    Search
                                </button>
                            </form>
                        </div>
                        <div class="single__widget widget__bg">
                            <h2 class="widget__title position__relative h3">Categories</h2>
                            <ul class="widget__categories--menu">
                                <li class="widget__categories--menu__list">
                                    <label class="widget__categories--menu__label d-flex align-items-center">
                                        <img class="widget__categories--menu__img" src="assets/img/product/small-product1.webp" alt="categories-img">
                                        <span class="widget__categories--menu__text">Denim Jacket</span>
                                        <svg class="widget__categories--menu__arrowdown--icon" xmlns="http://www.w3.org/2000/svg" width="12.355" height="8.394">
                                            <path d="M15.138,8.59l-3.961,3.952L7.217,8.59,6,9.807l5.178,5.178,5.178-5.178Z" transform="translate(-6 -8.59)" fill="currentColor"></path>
                                        </svg>
                                    </label>
                                    <ul class="widget__categories--sub__menu">
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product2.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Jacket, Women</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product3.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Woolend Jacket</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product4.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Western denim</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product5.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Mini Dresss</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="widget__categories--menu__list">
                                    <label class="widget__categories--menu__label d-flex align-items-center">
                                        <img class="widget__categories--menu__img" src="assets/img/product/small-product2.webp" alt="categories-img">
                                        <span class="widget__categories--menu__text">Oversize Cotton</span>
                                        <svg class="widget__categories--menu__arrowdown--icon" xmlns="http://www.w3.org/2000/svg" width="12.355" height="8.394">
                                            <path d="M15.138,8.59l-3.961,3.952L7.217,8.59,6,9.807l5.178,5.178,5.178-5.178Z" transform="translate(-6 -8.59)" fill="currentColor"></path>
                                        </svg>
                                    </label>
                                    <ul class="widget__categories--sub__menu">
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product2.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Jacket, Women</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product3.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Woolend Jacket</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product4.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Western denim</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product5.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Mini Dresss</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="widget__categories--menu__list">
                                    <label class="widget__categories--menu__label d-flex align-items-center">
                                        <img class="widget__categories--menu__img" src="assets/img/product/small-product3.webp" alt="categories-img">
                                        <span class="widget__categories--menu__text">Dairy & chesse</span>
                                        <svg class="widget__categories--menu__arrowdown--icon" xmlns="http://www.w3.org/2000/svg" width="12.355" height="8.394">
                                            <path d="M15.138,8.59l-3.961,3.952L7.217,8.59,6,9.807l5.178,5.178,5.178-5.178Z" transform="translate(-6 -8.59)" fill="currentColor"></path>
                                        </svg>
                                    </label>
                                    <ul class="widget__categories--sub__menu">
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product2.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Jacket, Women</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product3.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Woolend Jacket</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product4.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Western denim</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product5.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Mini Dresss</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="widget__categories--menu__list">
                                    <label class="widget__categories--menu__label d-flex align-items-center">
                                        <img class="widget__categories--menu__img" src="assets/img/product/small-product4.webp" alt="categories-img">
                                        <span class="widget__categories--menu__text">Shoulder Bag</span>
                                        <svg class="widget__categories--menu__arrowdown--icon" xmlns="http://www.w3.org/2000/svg" width="12.355" height="8.394">
                                            <path d="M15.138,8.59l-3.961,3.952L7.217,8.59,6,9.807l5.178,5.178,5.178-5.178Z" transform="translate(-6 -8.59)" fill="currentColor"></path>
                                        </svg>
                                    </label>
                                    <ul class="widget__categories--sub__menu">
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product2.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Jacket, Women</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product3.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Woolend Jacket</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product4.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Western denim</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product5.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Mini Dresss</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="widget__categories--menu__list">
                                    <label class="widget__categories--menu__label d-flex align-items-center">
                                        <img class="widget__categories--menu__img" src="assets/img/product/small-product5.webp" alt="categories-img">
                                        <span class="widget__categories--menu__text">Denim Jacket</span>
                                        <svg class="widget__categories--menu__arrowdown--icon" xmlns="http://www.w3.org/2000/svg" width="12.355" height="8.394">
                                            <path d="M15.138,8.59l-3.961,3.952L7.217,8.59,6,9.807l5.178,5.178,5.178-5.178Z" transform="translate(-6 -8.59)" fill="currentColor"></path>
                                        </svg>
                                    </label>
                                    <ul class="widget__categories--sub__menu">
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product2.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Jacket, Women</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product3.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Woolend Jacket</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product4.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Western denim</span>
                                            </a>
                                        </li>
                                        <li class="widget__categories--sub__menu--list">
                                            <a class="widget__categories--sub__menu--link d-flex align-items-center" href="blog-details.php">
                                                <img class="widget__categories--sub__menu--img" src="assets/img/product/small-product5.webp" alt="categories-img">
                                                <span class="widget__categories--sub__menu--text">Mini Dresss</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div class="single__widget widget__bg">
                            <h2 class="widget__title position__relative h3">Post article</h2>
                            <div class="articl__post--inner">
                                <div class="articl__post--items d-flex align-items-center">
                                    <div class="articl__post--items__thumbnail position__relative">
                                        <a class="articl__post--items__link display-block" href="blog-details.php">
                                            <img class="articl__post--items__img display-block" src="assets/img/product/small-product1.webp" alt="product-img">
                                        </a>
                                    </div>
                                    <div class="articl__post--items__content">
                                        <h4 class="articl__post--items__content--title"><a href="blog-details.php">The Greatest Team's Favorite Leggings.</a></h4>
                                        <span class="meta__deta text__secondary">Jan 11, 2022</span>
                                    </div>
                                </div>
                                <div class="articl__post--items d-flex align-items-center">
                                    <div class="articl__post--items__thumbnail position__relative">
                                        <a class="articl__post--items__link display-block" href="blog-details.php">
                                            <img class="articl__post--items__img display-block" src="assets/img/product/small-product2.webp" alt="product-img">
                                        </a>
                                    </div>
                                    <div class="articl__post--items__content">
                                        <h4 class="articl__post--items__content--title"><a href="blog-details.php">Top 10 Best Furniture Company.</a></h4>
                                        <span class="meta__deta text__secondary">Jan 11, 2022</span>
                                    </div>
                                </div>
                                <div class="articl__post--items d-flex align-items-center">
                                    <div class="articl__post--items__thumbnail position__relative">
                                        <a class="articl__post--items__link display-block" href="blog-details.php">
                                            <img class="articl__post--items__img display-block" src="assets/img/product/small-product3.webp" alt="product-img">
                                        </a>
                                    </div>
                                    <div class="articl__post--items__content">
                                        <h4 class="articl__post--items__content--title"><a href="blog-details.php">There are History you Should Know.</a></h4>
                                        <span class="meta__deta text__secondary">Jan 11, 2022</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="single__widget widget__bg">
                            <h2 class="widget__title position__relative h3">Tags</h2>
                            <ul class="widget__tagcloud">
                                <li class="widget__tagcloud--list"><a class="widget__tagcloud--link" href="blog-details.php">Wooden</a></li>
                                <li class="widget__tagcloud--list"><a class="widget__tagcloud--link" href="blog-details.php">Chair</a></li>
                                <li class="widget__tagcloud--list"><a class="widget__tagcloud--link" href="blog-details.php">Modern</a></li>
                                <li class="widget__tagcloud--list"><a class="widget__tagcloud--link" href="blog-details.php">Fabric </a></li>
                                <li class="widget__tagcloud--list"><a class="widget__tagcloud--link" href="blog-details.php">Shoulder </a></li>
                                <li class="widget__tagcloud--list"><a class="widget__tagcloud--link" href="blog-details.php">Winter</a></li>
                                <li class="widget__tagcloud--list"><a class="widget__tagcloud--link" href="blog-details.php">Accessories</a></li>
                                <li class="widget__tagcloud--list"><a class="widget__tagcloud--link" href="blog-details.php">Dress </a></li>
                            </ul>
                        </div>
                    </div>
                </div> -->
                <!--end Search -->

                <div class="col-lg-4">
                    <div class="related__post--area mb-50">
                        <div class="section__heading text-center mb-20">
                            <h2 class="section__heading--maintitle h3">Related Articles</h2>
                        </div>

                        <div class="row row-cols-md-12 row-cols-sm-12 row-cols-1 mb--n28">
                            <?php while ($row1 = mysqli_fetch_assoc($related_result)) { ?>
                                <div class="col mb-28">
                                    <div class="related__post--items">
                                        <div class="related__post--thumbnail border-radius-10 mb-20">
                                            <a class="display-block" href="blog-details.php?cid=<?php echo $row1['id']; ?>">
                                                <img class="related__post--img display-block border-radius-10" src="admin/img/<?php echo $row1['img']; ?>?>" alt="related-post">
                                            </a>
                                        </div>
                                        <div class="related__post--text">
                                            <h3 class="related__post--title mb-5">
                                                <a class="related__post--title__link" href="blog-details.php?cid=<?php echo $row1['id']; ?>">
                                                    <?php echo htmlspecialchars($row1['tittle']); ?>
                                                </a>
                                            </h3>
                                            <span class="related__post--deta"><?php echo date("F j, Y", strtotime($row1['date'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- End blog details section -->

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

<!-- login css -->
<style>
    .login-reminder {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        padding: 15px 20px;
        border-radius: 5px;
        color: #856404;
        font-size: 16px;
        margin-top: 20px;
        text-align: center;
    }

    .login-reminder a {
        color: #007bff;
        font-weight: bold;
        text-decoration: none;
    }

    .login-reminder a:hover {
        text-decoration: underline;
    }
</style>


<?php include('footer.php') ?>