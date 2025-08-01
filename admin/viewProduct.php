<?php
include('include/db.php');

// Fetch products and related data
$sql = "SELECT * FROM products p ORDER BY p.p_id DESC";

$res = mysqli_query($conn, $sql);
if (!$res) {
    die("Error fetching products: " . mysqli_error($conn));
}

include('include/header.php');
?>

<div class="mainHeading">
    <h3>View Products</h3>
</div>

<div class="content">

    <div class="mainContent">
        <table id="myTable" class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>p_id</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Sub-category</th>
                    <th>Price</th>
                    <th>Product Image</th>
                    <th>variant</th>
                    <th>Fatal</th>
                    <th>Action</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                // Fetch products including product_name
                $sql = "SELECT p.*, c.category FROM products p JOIN product_categories c ON p.category_id = c.pc_id ORDER BY p.p_id DESC";
                $res = mysqli_query($conn, $sql);

                // Loop through each product and display it in the table
                while ($row = mysqli_fetch_assoc($res)) {
                ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo htmlspecialchars($row['p_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['subcategory_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                        <td><img src="img/<?php echo htmlspecialchars($row['product_image']); ?>" alt="Product Image" width="50" height="50"></td>
                        <td>
                            <?php echo htmlspecialchars($row['product_variant']); ?>
                        </td>
                        <!-- <td>
                            <?php echo htmlspecialchars($row['FatalProduct']); ?>
                        </td> -->

                        <td>
                            <button class="btn toggle-fatal-btn 
                             <?php echo $row['FatalProduct'] == 1 ? 'btn-success' : 'btn-danger'; ?>"
                                data-id="<?php echo $row['p_id']; ?>"
                                data-status="<?php echo $row['FatalProduct']; ?>">
                                <?php echo $row['FatalProduct'] == 1 ? 'Show' : 'Hide'; ?>
                            </button>
                        </td>

                        <td>
                            <a href="editProduct.php?p_id=<?php echo $row['p_id']; ?>"> <i class="fas fa-edit"></i> </a>
                            <a href="deleteProduct.php?did=<?php echo $row['p_id']; ?>" class="ms-1"><i class="fa fa-trash text-success" aria-hidden="true"></i></a>
                        </td>
                        <td>
                            <?php if ($row['status'] == 1) {
                                echo '<p><a href="active.php?id=' . $row['p_id'] . '&status=0" class="btn btn-info">Active</a></p>';
                            } else {
                                echo '<p><a href="active.php?id=' . $row['p_id'] . '&status=1" class="btn btn-danger">Inactive</a></p>';
                            } ?>
                        </td>
                    </tr>
                <?php
                    $i++;
                }
                ?>
            </tbody>
        </table>

        <?php include('include/footer.php'); ?>



<script>
            $(document).on('click', '.toggle-fatal-btn', function() {
                var button = $(this);
                var productId = button.data('id');
                var currentStatus = button.data('status');
                var newStatus = currentStatus == 1 ? 0 : 1;

                // Get the current count of "Show" products
                var showCount = $(".toggle-fatal-btn.btn-success").length;

                // Allow only 6 "Show" products
                if (newStatus == 1 && showCount >= 6) {
                    alert('You can only show 6 products at a time!');
                    return false;
                }

                $.ajax({
                    url: 'update_fatal_status.php',
                    type: 'POST',
                    data: {
                        id: productId,
                        status: newStatus
                    },
                    success: function(response) {
                        console.log(response); // Debugging: Log the response
                        if (response === 'success') {
                            button.data('status', newStatus);
                            button.removeClass('btn-success btn-danger');
                            if (newStatus == 1) {
                                button.addClass('btn-success').text('Show');
                            } else {
                                button.addClass('btn-danger').text('Hide');
                            }
                        } else {
                            alert('Update failed!');
                        }
                    },
                    error: function() {
                        alert('There was an error processing your request.');
                    }
                });
            });
        </script>