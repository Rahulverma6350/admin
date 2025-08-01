<script>
$("#wishlistBtn").click(function(){
    alert("click on wishlist");
    console.log("click on wishlist");
    
    // Send an AJAX request to the server to update the wishlist
    $.ajax({
        url: 'wishlist_handler.php',  // Server-side script
        method: 'POST',
        data: { action: 'add_to_wishlist' },
        success: function(response) {
            console.log('Wishlist updated');
            // Optionally, redirect to wishlist page after success
            window.location.href = "wishlist.php";
        }
    });
});

</script>


