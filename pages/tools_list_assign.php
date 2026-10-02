<?php
require_once __DIR__ . "/../db.php"; 

$message = "";

// Handle assigning tool to a booking
if (isset($_POST['assign_tool'])) {
    $booking_id = intval($_POST['booking_id']);
    $tool_id = intval($_POST['tool_id']);
    $qty_used = intval($_POST['qty_used']);

    // Check available quantity for the tool
    $tool_query = mysqli_query($conn, "SELECT tool_name, quantity_available FROM tools WHERE tool_id = $tool_id");
    $tool_data = mysqli_fetch_assoc($tool_query);

    if ($tool_data && $tool_data['quantity_available'] >= $qty_used && $qty_used > 0) {
        // Insert into booking_tools
        $stmt = mysqli_prepare($conn, "INSERT INTO booking_tools (booking_id, tool_id, qty_used) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iii", $booking_id, $tool_id, $qty_used);
        mysqli_stmt_execute($stmt);

        // Deduct from available quantity
        mysqli_query($conn, "UPDATE tools SET quantity_available = quantity_available - $qty_used WHERE tool_id = $tool_id");

        $message = "Tool successfully assigned!";
    } else {
        $message = "Error: Not enough quantity available for this tool!";
    }
}

// Fetch tools inventory
$tools_result = mysqli_query($conn, "SELECT * FROM tools ORDER BY tool_id DESC");

// Fetch active bookings for the assignment dropdown
$bookings_result = mysqli_query($conn, "
    SELECT b.booking_id, c.full_name AS client_name, s.service_name 
    FROM bookings b
    JOIN clients c ON b.client_id = c.client_id
    JOIN services s ON b.service_id = s.service_id
    ORDER BY b.booking_id DESC
");

// Fetch assignment history log
$assignments_result = mysqli_query($conn, "
    SELECT bt.*, t.tool_name, c.full_name AS client_name, b.booking_id
    FROM booking_tools bt
    JOIN tools t ON bt.tool_id = t.tool_id
    JOIN bookings b ON bt.booking_id = b.booking_id
    JOIN clients c ON b.client_id = c.client_id
    ORDER BY bt.booking_tool_id DESC
");
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Tools & Assignments</title></head>
<body>
<?php include "../nav.php"; ?>
 
<h2>Tools Inventory & Assignment</h2>
<p style="color:green;"><?php echo $message; ?></p>

<h3>Assign Tool to Booking</h3>
<form method="post">
  <label>Select Booking</label><br>
  <select name="booking_id" required>
    <option value="">-- Choose Booking --</option>
    <?php while($b = mysqli_fetch_assoc($bookings_result)) { ?>
      <option value="<?php echo $b['booking_id']; ?>">
        Booking #<?php echo $b['booking_id']; ?> - <?php echo htmlspecialchars($b['client_name']); ?> (<?php echo htmlspecialchars($b['service_name']); ?>)
      </option>
    <?php } ?>
  </select><br><br>

  <label>Select Tool</label><br>
  <select name="tool_id" required>
    <option value="">-- Choose Tool --</option>
    <?php 
    // Rewind or re-query tools if needed, but since we only query once, let's fetch inside or use mysql_data_seek
    mysqli_data_seek($tools_result, 0);
    while($t = mysqli_fetch_assoc($tools_result)) { 
    ?>
      <option value="<?php echo $t['tool_id']; ?>">
        <?php echo htmlspecialchars($t['tool_name']); ?> (Available: <?php echo $t['quantity_available']; ?> / Total: <?php echo $t['quantity_total']; ?>)
      </option>
    <?php } ?>
  </select><br><br>

  <label>Quantity Used</label><br>
  <input type="number" name="qty_used" min="1" value="1" required><br><br>

  <button type="submit" name="assign_tool">Assign Tool</button>
</form>

<hr>

<h3>Tools Inventory</h3>
<table border="1" cellpadding="8">
  <tr>
    <th>ID</th>
    <th>Tool Name</th>
    <th>Total Quantity</th>
    <th>Available Quantity</th>
  </tr>
  <?php 
  mysqli_data_seek($tools_result, 0);
  while($tool = mysqli_fetch_assoc($tools_result)) { 
  ?>
    <tr>
      <td><?php echo $tool['tool_id']; ?></td>
      <td><?php echo htmlspecialchars($tool['tool_name']); ?></td>
      <td><?php echo $tool['quantity_total']; ?></td>
      <td><?php echo $tool['quantity_available']; ?></td>
    </tr>
  <?php } ?>
</table>

<hr>

<h3>Recent Tool Assignments Log</h3>
<table border="1" cellpadding="8">
  <tr>
    <th>Assignment ID</th>
    <th>Booking ID</th>
    <th>Client</th>
    <th>Tool Name</th>
    <th>Qty Used</th>
    <th>Date Assigned</th>
  </tr>
  <?php while($assign = mysqli_fetch_assoc($assignments_result)) { ?>
    <tr>
      <td><?php echo $assign['booking_tool_id']; ?></td>
      <td><?php echo $assign['booking_id']; ?></td>
      <td><?php echo htmlspecialchars($assign['client_name']); ?></td>
      <td><?php echo htmlspecialchars($assign['tool_name']); ?></td>
      <td><?php echo $assign['qty_used']; ?></td>
      <td><?php echo $assign['created_at']; ?></td>
    </tr>
  <?php } ?>
</table>

</body>
</html>