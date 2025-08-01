<style>
    /* Styling for the notification */
 #notification {
    display: none; 
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    padding: 10px 13px;
    background-color: #f51c1c; 
    color: white;  
    border-radius: 4px;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.14);
    text-align: center;
    width: 280px;
    z-index: 9999; 
 }

 /* Bottom line animation */
 #notification:after {
    content: "";
    position: absolute; 
    left: 0;
    bottom: -1px;
    width: 100%;
    height: 2.5px; 
    border-radius: 4px;
    background-color: rgba(18, 26, 37, 0.45); 
    animation: slideIn 1.5s ease-in-out;
}

 @keyframes slideIn {
    0% { width: 0%; }
    100% { width: 100%; }
 }
 .wishlist-active{
        background:red;
}

</style>


<section>
    <!-- for product add and remove in wishlist   -->
    <div id="notification">
    </div>
</section>
<script>
    const isUserLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>
<!-- wishlist  -->
<script>
$(document).on('click', '.wishlist-btn', function () {
    const productId = $(this).data('prd-id');
    const hasVariant = $(this).data('has-variant') == "1";
    const anchor = $(this).find('a');
     console.log(anchor[0]); // ✅ Yeh sahi tareeka hai

    if (hasVariant) {
        // Variant selected values
        const selectedColor = $(`input[name='color_${productId}']:checked`).val();
        const selectedSize = $(`#sizeDropdown${productId}`).val();

        if (!selectedColor) {
            alert("Please select a color.");
            return;
        }

        if (!selectedSize) {
            alert("Please select a size.");
            return;
        }

        // Send AJAX with productId + color + size
        $.ajax({
            url: 'wishlist.php',
            method: 'GET',
            data: {
                prductId: productId,
                color: selectedColor,
                size: selectedSize
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                $('#notification').text(response);  
                $('#notification').fadeIn().delay(1500).fadeOut();
                if (response.includes('added')) {
                console.log("yes");
                if (isUserLoggedIn) {
                   anchor.addClass('wishlist-active');
                }
                } else if (response.includes('removed')) {
                anchor.removeClass('wishlist-active');} 
                updateWishlistCount();
            },
                error: function() {
                    alert("Error adding product to wishlist.");
                }
        });

    } else {
        // Simple product — old logic
        $.ajax({
            url: 'wishlist.php',
            method: 'GET',
            data: {
                prductId: productId
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                $('#notification').text(response);  
                $('#notification').fadeIn().delay(1500).fadeOut();
                if (response.includes('added')) {
                console.log("yes");
                if (isUserLoggedIn) {
                   anchor.addClass('wishlist-active');
                }
                } else if (response.includes('removed')) {
                anchor.removeClass('wishlist-active');}  
                updateWishlistCount();
            },
                error: function() {
                    alert("Error adding product to wishlist.");
                }
        });
    }
});

</script> 


<!-- wislist count  -->
<!-- <script>
    function updateWishlistCount() {
    $.ajax({
        url: "wishlist_count.php",  // Wishlist count fetch karega
        type: "GET",
        success: function(count) {
            $("#wishlistCountValue").text(count); // Count update karega
        }
    });
}
</script> -->
<!-- function updateWishlistCount() {
    $.ajax({
        url: 'wishlistcount.php',
        method: 'GET',
        success: function (response) {
            $('#wishlistCount').text(response); // Assuming you have span#wishlistCount
        }
    });
} -->
