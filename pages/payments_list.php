<?php
include "../db.php";
require_once __DIR__ . "/../db.php";

$sql = "
SELECT p.*, b.booking_id, c.full_name AS client_name, s.service_name
FROM payments p
JOIN bookings b ON p.booking_id = b.booking_id
JOIN clients c ON b.client_id = c.client_id
JOIN services s ON b.service_id = s.service_id
ORDER BY p.payment_id DESC
";
$result = mysqli_query($conn, $sql);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Payments List</title></head>
<body>
<?php include "../nav.php"; ?>
 
<h2>Payments List</h2>
 
<table border="1" cellpadding="8">
  <tr>
    <th>Payment ID</th>
    <th>Booking ID</th>
    <th>Client Name</th>
    <th>Service</th>
    <th>Amount Paid</th>
    <th>Method</th>
    <th>Payment Date</th>
  </tr>
  <?php while($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
      <td><?php echo $row['payment_id']; ?></td>
      <td><?php echo $row['booking_id']; ?></td>
      <td><?php echo htmlspecialchars($row['client_name']); ?></td>
      <td><?php echo htmlspecialchars($row['service_name']); ?></td>
      <td>₱<?php echo number_format($row['amount_paid'], 2); ?></td>
      <td><?php echo htmlspecialchars($row['method']); ?></td>
      <td><?php echo $row['payment_date']; ?></td>
    </tr>
  <?php } ?>
</table>
</body>
</html>