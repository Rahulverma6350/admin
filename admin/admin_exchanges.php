
<?php
session_start();
include('include/db.php');
include('include/header.php');

$query = "SELECT e.*, u.name AS user_name, p.product_name, p.product_image 
          FROM exchanges e
          JOIN user_reg u ON e.user_id = u.id
          JOIN products p ON e.product_id = p.p_id
          WHERE e.status = 'Pending'
          ORDER BY e.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<div class="mainHeading">
    <h3>admin_exchanges</h3>
</div>

<div class="content">
    <div class="mainContent">
    <table id="myTable" class="table table-striped">
    <thead>
    <tr>
      <th>s.no</th>
      <th>Product</th>
      <th>User</th>
      <th>Old (Size/Color)</th>
      <th>New (Size/Color)</th>
      <th>Request Date</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>

    <?php
    $i = 1;
    while ($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?php echo $i ?></td>
        <td>
          <img src="img/<?= $row['product_image'] ?>" width="70"><br>
          <?= $row['product_name'] ?>
        </td>
        <td><?= $row['user_name'] ?></td>
        <td><?= strtoupper($row['original_size']) ?> / <?= ucfirst($row['original_color']) ?></td>
        <td><?= strtoupper($row['new_size']) ?> / <?= ucfirst($row['new_color']) ?></td>
        <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
        <td>
          <form method="POST" action="approve_exchange.php" onsubmit="return confirm('Confirm exchange approval?')">
            <input type="hidden" name="exchange_id" value="<?= $row['id'] ?>">
            <button type="submit" name="approve" class="btn btn-success">Approve</button>
          </form>
        </td>
      </tr>
    <?php 
  $i++;
  } ?>
  </tbody>
</table>
    </div>
</div>


<?php
include("include/footer.php");
?>


