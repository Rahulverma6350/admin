<?php
include('include/db.php');
// Query to fetch reviews along with status
$sql = "SELECT
    products_review.id,
    products_review.name,
    products_review.email,
    products_review.review,
    products_review.rating,
    products_review.added_on,
    products_review.status
FROM
    products_review
JOIN
    user_reg ON products_review.user_id = user_reg.id
ORDER BY
    products_review.added_on DESC";
$res = mysqli_query($conn, $sql);
include('include/header.php');
?>
<div class="mainHeading">
    <h3>View Reviews</h3>
</div>
<div class="content">
    <div class="mainContent">
        <table id="myTable" class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = 1;
                while ($viewreview = mysqli_fetch_assoc($res)) {
                    $formatted_date = date("F j, Y", strtotime($viewreview['added_on']));
                ?>
                    <tr>
                        <td><?php echo $count; ?></td>
                        <td><?php echo htmlspecialchars($viewreview['name']); ?></td>
                        <td><?php echo htmlspecialchars($viewreview['email']); ?></td>
                        <td><?php echo htmlspecialchars($viewreview['rating']); ?> / 5</td>
                        <td><?php echo htmlspecialchars($viewreview['review']); ?></td>
                        <td><?php echo $formatted_date; ?></td>
                        <td>
                            <?php
                            $status = $viewreview['status'];
                            $buttonClass = ($status == 1) ? 'btn-info' : 'btn-danger';
                            $buttonText = ($status == 1) ? 'Approved' : 'Unapproved';
                            echo '<a href="change_status.php?review_id=' . $viewreview['id'] . '&status=' . $status . '" class="btn ' . $buttonClass . '">' . $buttonText . '</a>';
                            ?>
                        </td>
                        <td>
                            <a href="delete_review.php?review_id=<?php echo $viewreview['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this review?');"> Delete </a>
                        </td>
                    </tr>
                <?php
                    $count++;
                } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('include/footer.php'); ?>