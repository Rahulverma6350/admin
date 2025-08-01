<style>
    .live-search-results {
    position: absolute;
    background: white;
    z-index: 9999;
    width: 100%;
    border: 1px solid #ccc;
    max-height: 250px;
    overflow-y: auto;
}

.search-suggestion-list {
    list-style: none;
    padding: 0;
    margin: 0;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.search-suggestion-list li {
    padding: 8px 10px;
    cursor: pointer;
    transition: background 0.2s;
}

.search-suggestion-list li:hover {
    background: #f1f1f1;
}

</style>

<?php

include 'include/db.php'; // your DB connection file

if (isset($_POST['query'])) {
    $search = mysqli_real_escape_string($conn, $_POST['query']);

    $sql = "SELECT p_id, product_name, price, product_image FROM products WHERE product_name LIKE '%$search%' LIMIT 5";
    $result = mysqli_query($conn, $sql);

    $output = "<ul class='search-suggestion-list'>";
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $productId = $row['p_id'];
            $name = htmlspecialchars($row['product_name']);
            $price = number_format($row['price'], 2);
            $image = $row['product_image'] ? $row['product_image'] : 'admin/img/'; // default image

            $output .= "
                <li class='search-item' data-id='$productId'>
                    <div class='search-item-inner' style='display: flex; align-items: center; gap: 10px;'>
                        <img src='admin/img/$image' alt='$name' style='width: 40px; height: 40px; object-fit: cover; border-radius: 5px;'>
                        <div class='search-info'>
                            <div class='search-name' style='font-weight: 500;'>$name</div>
                            <div class='search-price' style='font-size: 14px; color: #555;'>₹$price</div>
                        </div>
                    </div>
                </li>
            ";
        }
    } else {
        $output .= "<li class='no-result'>No product found</li>";
    }
    $output .= "</ul>";
    echo $output;
}
?>
