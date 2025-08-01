<?php
include('include/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}

if (isset($_SESSION['loginSessionId'])) {
    // Access the variable
    $getsession  = $_SESSION['loginSessionId'];
} else {
    // Handle the error, e.g., redirect to login page
    header("Location: login.php");
    exit();
}
// Prepare the SQL statement to fetch username, email, and image

$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
if (!$stmt) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
}

$stmt->bind_param("i", $getsession); // Assuming 'id' is an integer

// Execute the statement
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    exit();
}

$result = $stmt->get_result();
// echo "Rows found: " . $result->num_rows; // Debugging line

if ($result->num_rows > 0) {
    // Fetch the user data
    $row = $result->fetch_assoc();
    // echo "Fetched Data: " . print_r($row, true) . "<br>"; // Debugging line

    $email = $row['email'];
    $username = $row['name'];

    $profile = $row['img']; // Directly use the profile image 
    
} else {
    echo "No user found with the given ID."; // Debugging line
    exit(); // Exit if no user is found 
}

// Close the statement
$stmt->close();

// Update functionality
if (isset($_POST['submitBtn'])) {
    // Initialize $mediaprofile with the old profile image
    $mediaprofile = isset($_POST['old_img']) ? $_POST['old_img'] : null;

    // Check if a new image has been uploaded
    if (isset($_FILES['new_profile']) && $_FILES['new_profile']['error'] == 0) {
        $new_conImg = $_FILES['new_profile']['name'];
        $new_conImgPath = $_FILES['new_profile']['tmp_name'];

        $folderName = '' . $new_conImg;
        if (move_uploaded_file($new_conImgPath, $folderName)) {
            $mediaprofile = $new_conImg; // Update the photo variable if upload is successful
        } else {
            echo "Failed to move uploaded file."; // Debugging line
        }
    }
    
    // Update user information
    $username = $_POST['name'];
    $email = $_POST['email'];
    $profile= $_POST['old_img'];

    $updataProfile = "UPDATE admin SET img=?, name=?, email=? WHERE id=?";
    $stmtUpdate = $conn->prepare($updataProfile);

    if (!$stmtUpdate) {
        echo "Prepare update failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }

    $stmtUpdate->bind_param("sssi", $mediaprofile, $username, $email, $getsession);

    if (!$stmtUpdate->execute()) {
        echo "Update failed: (" . $stmtUpdate->errno . ") " . $stmtUpdate->error; // Debugging line
    } else {
        // echo "Profile updated successfully.";
    }

    $stmtUpdate->close();
}   
?>


<!DOCTYPE html>
<html lang="en" data-topbar-color="dark">

<head>
    <meta charset="utf-8" />
    <title>Admin | Furea</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Plugins css -->
    <link href="assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/selectize/css/selectize.bootstrap3.css" rel="stylesheet" type="text/css" />

    <!-- Theme Config Js -->
    <script src="assets/js/head.js"></script>

    <!-- Bootstrap css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />

    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- custom-css  -->
    <link rel="stylesheet" href=".\assets\css\style.css">

    <!-- bootstrap  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- data-table  -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- fontawesome  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- ajax  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

</head>

<body>

    <!-- Begin page -->
    <div id="wrapper">


        <!-- ========== Menu ========== -->
        <div class="app-menu">

            <!-- Brand Logo -->
            <div class="logo-box">
                <!-- Brand Logo Light -->
                <a href="index.php" class="logo-light">
                    <img src="assets/images/nav-log.webp" alt="logo" class="logo-lg">
                    <img src="assets/images/nav-log.webp" alt="small logo" class="logo-sm">
                </a>

                <!-- Brand Logo Dark -->
                <a href="index.php" class="logo-dark">
                    <img src="assets/images/nav-log.webp" alt="nav-log.webp" class="logo-lg">
                    <img src="assets/images/nav-log.webp" alt="nav-log.webp" class="logo-sm">
                </a>
            </div>

            <!-- menu-left -->
            <div class="scrollbar">

                <!-- User box -->
                <div class="user-box text-center">
                    <img src="assets/images/users/user-1.jpg" alt="user-img" title="Mat Helme" class="rounded-circle avatar-md">
                    <div class="dropdown">
                        <a href="javascript: void(0);" class="dropdown-toggle h5 mb-1 d-block" data-bs-toggle="dropdown">Geneva Kennedy</a>
                        <div class="dropdown-menu user-pro-dropdown">

                            <!-- item-->
                            <a href="" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                <i class="fe-user me-1"></i>
                                <span>My Account</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="fe-settings me-1"></i>
                                <span>Settings</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="fe-lock me-1"></i>
                                <span>Lock Screen</span>
                            </a>

                            <!-- item-->
                            <!-- <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-log-out me-1"></i>
                                    <span>Logout</span>
                                </a> -->

                        </div>
                    </div>
                    <p class="text-muted mb-0">Admin Head</p>
                </div>

                <ul class="menu">
                    <li class="menu-title">Navigation</li>

                    <!-- Dashboard  -->
                    <li class="menu-item">
                        <a href="#menuDashboards" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> Dashboard </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="menuDashboards">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a href="index.php" class="menu-link">
                                        <span class="menu-text">Dashboard 1</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- logo  -->
                    <li class="menu-item">
                        <a href="#logo" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text">Logo </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="logo">
                            <ul class="sub-menu">
                      
                                <li class="sub-menu-item">
                                    <a href="logoView.php" class="menu-link">
                                        <span class="menu-text">View</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Home Header -->
                    <li class="menu-item">
                        <a href="#homeHeader" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> Home-header </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="homeHeader">
                            <ul class="sub-menu">
                                <!-- <li class="sub-menu-item">
                    <a href="addHeader.php" class="menu-link">
                        <span class="menu-text">Add</span>
                    </a>
                </li> -->
                                <li class="sub-menu-item">
                                    <a href="viewHeader.php" class="menu-link">
                                        <span class="menu-text">View</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- contact-page  -->
                    <li class="menu-item">
                        <a href="#contact" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> Contact page </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="contact">
                            <ul class="sub-menu">
                                <!-- <li class="sub-menu-item">
                    <a href="addContactpage.php" class="menu-link">
                        <span class="menu-text">Add</span>
                    </a>
                </li> -->
                                <li class="sub-menu-item">
                                    <a href="viewContactpage.php" class="menu-link">
                                        <span class="menu-text">View</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- product-cat  -->
                    <li class="menu-item">
                        <a href="#prdcat" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> Product category </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="prdcat">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a href="addPrdcat.php" class="menu-link">
                                        <span class="menu-text">Add</span>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a href="viewPrdcat.php" class="menu-link">
                                        <span class="menu-text">View</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- product-page  -->
                    <li class="menu-item">
                        <a href="#product" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> Products </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="product">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a href="addProduct.php" class="menu-link">
                                        <span class="menu-text">Add</span>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a href="viewProduct.php" class="menu-link">
                                        <span class="menu-text">View</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- blog  -->
                    <li class="menu-item">
                        <a href="#blog" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> Blog </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="blog">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a href="blog.php" class="menu-link">
                                        <span class="menu-text">Add</span>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a href="viewBlog.php" class="menu-link">
                                        <span class="menu-text">View</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- about team member  -->
                    <li class="menu-item">
                        <a href="#about" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> About member </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="about">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a href="aboutUs_team.php" class="menu-link">
                                        <span class="menu-text">Add</span>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a href="viewAboutUs_team.php" class="menu-link">
                                        <span class="menu-text">View</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>


                    
                    <!-- about team member  -->
                    <li class="menu-item">
                        <a href="#review" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> Product review </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="review">
                            <ul class="sub-menu">
                               
                                <li class="sub-menu-item">
                                    <a href="viewreview.php" class="menu-link">
                                        <span class="menu-text">View</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- review member  -->
                    <li class="menu-item">
                        <a href="#query" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> Query </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="query">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a href="#" class="menu-link">
                                        <span class="menu-text">Add</span>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a href="viewQuery.php" class="menu-link">
                                        <span class="menu-text">View</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <!-- Orders team member  -->
                    <li class="menu-item">
                        <a href="#orders" data-bs-toggle="collapse" class="menu-link">
                            <span class="menu-icon"><i data-feather="airplay"></i></span>
                            <span class="menu-text"> Orders </span>
                            <i class="fa-solid fa-angle-down text-success ms-auto"></i>
                        </a>

                        <div class="collapse" id="orders">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a href="admin_orders.php" class="menu-link">
                                        <span class="menu-text">admin_orders</span>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a href="admin_return_requests.php" class="menu-link">
                                        <span class="menu-text">return</span>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a href="admin_exchanges.php" class="menu-link">
                                        <span class="menu-text">exchange</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>



                </ul>

                <div class="clearfix"></div>
            </div>
        </div>
        <!-- ========== Left menu End ========== -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">

            <!-- ========== Topbar Start ========== -->
            <div class="navbar-custom">
                <div class="topbar">
                    <div class="topbar-menu d-flex align-items-center gap-1">

                        <!-- Topbar Brand Logo -->
                        <div class="logo-box">
                            <!-- Brand Logo Light -->
                            <a href="index.php" class="logo-light">
                                <img src="assets/images/logo-light.png" alt="logo" class="logo-lg">
                                <img src="assets/images/logo-sm.png" alt="small logo" class="logo-sm">
                            </a>

                            <!-- Brand Logo Dark -->
                            <a href="index.php" class="logo-dark">
                                <img src="assets/images/logo-dark.png" alt="dark logo" class="logo-lg">
                                <img src="assets/images/logo-sm.png" alt="small logo" class="logo-sm">
                            </a>
                        </div>

                        <!-- Sidebar Menu Toggle Button -->
                        <button class="button-toggle-menu">
                            <i class="mdi mdi-menu"></i>
                        </button>




                    </div>

                    <ul class="topbar-menu d-flex align-items-center">


                        <!-- Fullscreen Button -->
                        <li class="d-none d-md-inline-block">
                            <a class="nav-link waves-effect waves-light" href="#" data-toggle="fullscreen">
                                <i class="fe-maximize font-22"></i>
                            </a>
                        </li>

                        <!-- Search Dropdown (for Mobile/Tablet) -->
                        <li class="dropdown d-lg-none">
                            <a class="nav-link dropdown-toggle waves-effect waves-light arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <i class="ri-search-line font-22"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-animated dropdown-lg p-0">
                                <form class="p-3">
                                    <input type="search" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                </form>
                            </div>
                        </li>



                        <!-- Notofication dropdown -->
                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle waves-effect waves-light arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <i class="fe-bell font-22"></i>
                                <span class="badge bg-danger rounded-circle noti-icon-badge">9</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg py-0">
                                <div class="p-2 border-top-0 border-start-0 border-end-0 border-dashed border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0 font-16 fw-semibold"> Notification</h6>
                                        </div>
                                        <div class="col-auto">
                                            <a href="javascript: void(0);" class="text-dark text-decoration-underline">
                                                <small>Clear All</small>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-1" style="max-height: 300px;" data-simplebar>

                                    <h5 class="text-muted font-13 fw-normal mt-2">Today</h5>
                                    <!-- item-->

                                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card unread-noti shadow-none mb-1">
                                        <div class="card-body">
                                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="notify-icon bg-primary">
                                                        <i class="mdi mdi-comment-account-outline"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 text-truncate ms-2">
                                                    <h5 class="noti-item-title fw-semibold font-14">Datacorp <small class="fw-normal text-muted ms-1">1 min ago</small></h5>
                                                    <small class="noti-item-subtitle text-muted">Caleb Flakelar commented on Admin</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>

                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card read-noti shadow-none mb-1">
                                        <div class="card-body">
                                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="notify-icon bg-info">
                                                        <i class="mdi mdi-account-plus"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 text-truncate ms-2">
                                                    <h5 class="noti-item-title fw-semibold font-14">Admin <small class="fw-normal text-muted ms-1">1 hours ago</small></h5>
                                                    <small class="noti-item-subtitle text-muted">New user registered</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>

                                    <h5 class="text-muted font-13 fw-normal mt-0">Yesterday</h5>

                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card read-noti shadow-none mb-1">
                                        <div class="card-body">
                                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="notify-icon">
                                                        <img src="assets/images/users/avatar-2.jpg" class="img-fluid rounded-circle" alt="" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 text-truncate ms-2">
                                                    <h5 class="noti-item-title fw-semibold font-14">Cristina Pride <small class="fw-normal text-muted ms-1">1 day ago</small></h5>
                                                    <small class="noti-item-subtitle text-muted">Hi, How are you? What about our next meeting</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>

                                    <h5 class="text-muted font-13 fw-normal mt-0">30 Dec 2021</h5>

                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card read-noti shadow-none mb-1">
                                        <div class="card-body">
                                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="notify-icon bg-primary">
                                                        <i class="mdi mdi-comment-account-outline"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 text-truncate ms-2">
                                                    <h5 class="noti-item-title fw-semibold font-14">Datacorp</h5>
                                                    <small class="noti-item-subtitle text-muted">Caleb Flakelar commented on Admin</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>

                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card read-noti shadow-none mb-1">
                                        <div class="card-body">
                                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="notify-icon">
                                                        <img src="assets/images/users/avatar-4.jpg" class="img-fluid rounded-circle" alt="" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 text-truncate ms-2">
                                                    <h5 class="noti-item-title fw-semibold font-14">Karen Robinson</h5>
                                                    <small class="noti-item-subtitle text-muted">Wow ! this admin looks good and awesome design</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>

                                    <div class="text-center">
                                        <i class="mdi mdi-dots-circle mdi-spin text-muted h3 mt-0"></i>
                                    </div>
                                </div>

                                <!-- All-->
                                <a href="javascript:void(0);" class="dropdown-item text-center text-primary notify-item border-top border-light py-2">
                                    View All
                                </a>

                            </div>
                        </li>

                        <!-- Light/Darj Mode Toggle Button -->
                        <li class="d-none d-sm-inline-block">
                            <div class="nav-link waves-effect waves-light" id="light-dark-mode">
                                <i class="ri-moon-line font-22"></i>
                            </div>
                        </li>

                        <!-- User Dropdown -->
                        <li class="dropdown">
                            <a class="nav-link dropdown-toggle nav-user me-0 waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <img src="<?php echo $profile ?>" alt="user-image" class="rounded-circle">
                                <span class="ms-1 d-none d-md-inline-block">
                                    <?php echo $row['name']; ?>
                                    <i class="mdi mdi-chevron-down"></i>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                                <!-- item-->
                                <div class="dropdown-header noti-title">
                                    <h6 class="text-overflow m-0">Welcome !</h6>
                                </div>

                                <!-- item-->
                                <a href="" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    <i class="fe-user"></i>
                                    <span>My Account</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-settings"></i>
                                    <span>Settings</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-lock"></i>
                                    <span>Lock Screen</span>
                                </a>

                                <div class="dropdown-divider"></div>

                                <!-- item-->
                                <a href="logout.php" class="dropdown-item notify-item">
                                    <i class="fe-log-out"></i>
                                    <span>Logout</span>
                                </a>

                            </div>
                        </li>

                        <!-- Right Bar offcanvas button (Theme Customization Panel) -->
                        <li>
                            <a class="nav-link waves-effect waves-light" data-bs-toggle="offcanvas" href="#theme-settings-offcanvas">
                                <i class="fe-settings font-22"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>


            <!-- ========== Topbar End ========== -->

            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">My Profile</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="post" enctype="multipart/form-data">

                               <img src="<?php echo $profile ?>" alt="" style="width: 120px; height: auto;border-radius: 10px;"><br>
                               <input type="hidden" name="old_img" value="<?php echo htmlspecialchars($profile); ?>">

                                <!-- <img src="" alt="" style="width: 25%"><br> -->
                                <label for="profile">Add Profile</label>
                                <input type="file" name="new_profile" id="profile" class="form-control">

                                <label for="">Username</label>
                                <input type="text" name="name" id="username" class="form-control" value="<?php echo $row['name']; ?>">


                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?php echo $row['email']; ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="submitBtn" class="btn btn-primary">Save changes</button>
                        </div>

                        </form>

                    </div>
                </div>
            </div>