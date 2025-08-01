<?php
include('include/db.php');

$sql = "SELECT * FROM product_categories";
$res = mysqli_query($conn, $sql);

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM product_categories WHERE pc_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    // Redirect to avoid resubmission
    header("Location:viewPrdcat.php");
    exit();
}

include('include/header.php');
?>

<div class="mainHeading">
    <h3>View Product Category</h3>
</div>

<div class="content">

    <div class="mainContent">

        <table id="myTable" class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Category</th>
                    <th>Sub Categories</th>
                    <th>Action</th>
                    <th>Status</th>

                </tr>
            </thead>
            <tbody>
                <?php
                $count = 1;
                while ($row = mysqli_fetch_assoc($res)) {
                ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>
                            <ul>
                                <?php
                                $subcategories = explode(", ", $row['sub_category']);
                                foreach ($subcategories as $sub) {
                                    echo "<li>" . htmlspecialchars($sub) . "</li>";
                                }
                                ?>
                            </ul>
                        </td>
                        <td>
                            <a href="editPrdcat.php?id=<?php echo $row['pc_id']; ?>"> <i class="fas fa-edit"></i> </a>
                            <a href="viewPrdcat.php?delete_id=<?php echo $row['pc_id']; ?>" onclick="return confirm('Are you sure you want to delete this category?');">
    <i class="fa fa-trash text-danger" aria-hidden="true"></i>
</a>

                        </td>
                        <td>
                            <?php if ($row['status'] == 1) {
                                echo '<p><a href="prdcatid.php?id=' . $row['pc_id'] . '&status=0" class="btn btn-info">Active</a></p>';
                            } else {
                                echo '<p><a href="prdcatid.php?id=' . $row['pc_id'] . '&status=1" class="btn btn-danger">Inactive</a></p>';
                            } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php include('include/footer.php'); ?>