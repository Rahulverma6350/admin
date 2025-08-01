<?php
    include('include/header.php');
    include('include/db.php');


    // Delete logo
    if (isset($_GET['delete'])) {
        $deleteId = $_GET['delete'];

        // Fetch image name to delete from folder if needed
        $imgQuery = $conn->prepare("SELECT image FROM logo WHERE id = ?");
        $imgQuery->bind_param("i", $deleteId);
        $imgQuery->execute();
        $imgResult = $imgQuery->get_result();
        if ($imgResult->num_rows > 0) {
            $imgRow = $imgResult->fetch_assoc();
            $imagePath = "img/" . $imgRow['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath); // delete image file
            }
        }
        // Delete from database
        $delStmt = $conn->prepare("DELETE FROM logo WHERE id = ?");
        $delStmt->bind_param("i", $deleteId);
        if ($delStmt->execute()) {
            echo "<script>alert('Logo deleted successfully'); window.location.href='logoView.php';</script>";
            exit();
        }
    }

    // Update logo when form is submitted
    if (isset($_POST['update'])) {
        $logoId = $_POST['id'];
        $oldImg = $_POST['old_image'];
        $img = $oldImg;

        if (!empty($_FILES['image']['name'])) {
            $imgname = $_FILES['image']['name'];
            $tmp_name = $_FILES['image']['tmp_name'];
            $filepath = "img/" . $imgname;

            if (move_uploaded_file($tmp_name, $filepath)) {
                $img = $imgname;
            }
        }

        // Social media inputs and copyright
        $social = [
            "facebook" => $_POST['facebook'],
            "twitter" => $_POST['twitter'],
            "youtube" => $_POST['youtube'],
            "linkdin" => $_POST['linkdin']
        ];
        $social_json = json_encode($social);
        $copyright = $_POST['copyright'];

        $update = "UPDATE logo SET image = ?, social = ?, copyright = ? WHERE id = ?";
        $query = $conn->prepare($update);
        $query->bind_param("sssi", $img, $social_json, $copyright, $logoId);
        if ($query->execute()) {
            echo "<script>alert('Logo updated successfully'); window.location.href = 'logoView.php';</script>";
            exit();
        }
    }

    // Fetch all logos
    $sql = "SELECT * FROM logo";
    $result = $conn->query($sql);
?>


<div class="mainHeading">
    <h3>View Product Category</h3>
</div>

<div class="content">

    <div class="mainContent">

        <table id="myTable" class="table table-striped">
            <thead>
                <tr>
                    <th>Serial No.</th>
                    <th>Logo</th>
                    <th>Social media</th>
                    <th>copyright</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while ($row = $result->fetch_assoc()) { 
                    $socialLinks = json_decode($row['social'], true);
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><img src="img/<?php echo $row['image']; ?>" width="80px"></td>
                    <td>
    <strong>Facebook:</strong> <?php echo $socialLinks['facebook'] ?? 'N/A'; ?><br>
    <strong>Twitter:</strong> <?php echo $socialLinks['twitter'] ?? 'N/A'; ?><br>
    <strong>YouTube:</strong> <?php echo $socialLinks['youtube'] ?? 'N/A'; ?><br>
    <strong>linkdin:</strong> <?php echo $socialLinks['linkdin'] ?? 'N/A'; ?>
</td>
<td>
    <?php echo $row['copyright']; ?>
</td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                            <i class="fas fa-edit"></i>
                        </button>

                        <a href="logoView.php?delete=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this logo?');">
                           <button class="btn" style="background:red;"><i class="fa fa-trash text-danger" aria-hidden="true" style="color:white;"></i></button>
                           
                        </a>
                    </td>
                </tr>

                <!-- Modal for Edit -->
                <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="old_image" value="<?php echo $row['image']; ?>">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Logo</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <img src="img/<?php echo $row['image']; ?>" width="80px" class="mb-2 d-block">
                                    <div class="mb-3">
                                        <label class="form-label">Upload New Image</label>
                                        <input type="file" name="image" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Facebook Link</label>
                                        <input type="text" name="facebook" class="form-control" value="<?php echo $socialLinks['facebook'] ?? ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Twitter Link</label>
                                        <input type="text" name="twitter" class="form-control" value="<?php echo $socialLinks['twitter'] ?? ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">YouTube Link</label>
                                        <input type="text" name="youtube" class="form-control" value="<?php echo $socialLinks['youtube'] ?? ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">linkdin Link</label>
                                        <input type="text" name="linkdin" class="form-control" value="<?php echo $socialLinks['linkdin'] ?? ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Copyright Text</label>
                                        <input type="text" name="copyright" class="form-control" value="<?php echo $row['copyright'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('include/footer.php'); ?>
 