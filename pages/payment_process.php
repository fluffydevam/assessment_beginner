<?php
include "../db.php";
require_once __DIR__ . "/../db.php";

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$message = "";

// Fetch booking details along with client and service info
$sql = "
SELECT b.*, c.full_name AS client_name, s.service_name
FROM bookings b
JOIN clients c ON b.client_id = c.client_id
JOIN services s ON b.service_id = s.service_id
WHERE b.booking_id = $booking_id
";
$result = mysqli_query($conn, $sql);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    header("Location: bookings_list.php");
    exit;
}

// Calculate total amount already paid for this booking
$paid_query = mysqli_query($conn, "SELECT SUM(amount_paid) AS total_paid FROM payments WHERE booking_id = $booking_id");
$paid_data = mysqli_fetch_assoc($paid_query);
$total_paid = $paid_data['total_paid'] ? floatval($paid_data['total_paid']) : 0.00;
$balance = floatval($booking['total_cost']) - $total_paid;

if (isset($_POST['process_payment'])) {
    $amount_paid = floatval($_POST['amount_paid']);
    $method = $_POST['method'];

    // Error handling: Check if already fully paid or if payment exceeds balance
    if ($balance <= 0) {
        $message = "Error: This booking is already fully paid!";
    } elseif ($amount_paid <= 0) {
        $message = "Error: Amount paid must be greater than zero!";
    } elseif ($amount_paid > $balance) {
        $message = "Error: Amount paid cannot exceed the remaining balance!";
    } else {
        // Insert payment record using prepared statement
        $stmt = mysqli_prepare($conn, "INSERT INTO payments (booking_id, amount_paid, method) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ids", $booking_id, $amount_paid, $method);
        mysqli_stmt_execute($stmt);

        // Update booking status based on remaining balance
        $new_total_paid = $total_paid + $amount_paid;
        if ($new_total_paid >= floatval($booking['total_cost'])) {
            mysqli_query($conn, "UPDATE bookings SET status = 'PAID' WHERE booking_id = $booking_id");
        } else {
            mysqli_query($conn, "UPDATE bookings SET status = 'PARTIAL' WHERE booking_id = $booking_id");
        }

        header("Location: payments_list.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Process Payment</title></head>
<body>
<?php include "../nav.php"; ?>
 
<h2>Process Payment for Booking #<?php echo $booking['booking_id']; ?></h2>
<p style="color:red;"><?php echo $message; ?></p>

<ul>
  <li>Client: <b><?php echo htmlspecialchars($booking['client_name']); ?></b></li>
  <li>Service: <b><?php echo htmlspecialchars($booking['service_name']); ?></b></li>
  <li>Booking Date: <b><?php echo $booking['booking_date']; ?></b></li>
  <li>Total Cost: <b>₱<?php echo number_format($booking['total_cost'], 2); ?></b></li>
  <li>Total Paid: <b>₱<?php echo number_format($total_paid, 2); ?></b></li>
  <li>Balance: <b style="color: <?php echo $balance > 0 ? 'red' : 'green'; ?>;">₱<?php echo number_format($balance, 2); ?></b></li>
  <li>Current Status: <b><?php echo $booking['status']; ?></b></li>
</ul>

<?php if ($balance > 0) { ?>
<form method="post">
  <label>Amount Paid (₱)</label><br>
  <input type="number" step="0.01" name="amount_paid" max="<?php echo $balance; ?>" value="<?php echo $balance; ?>" required><br><br>
 
  <label>Payment Method</label><br>
  <select name="method">
    <option value="CASH">Cash</option>
    <option value="GCASH">GCash</option>
    <option value="BANK TRANSFER">Bank Transfer</option>
    <option value="CREDIT CARD">Credit Card</option>
  </select><br><br>
 
  <button type="submit" name="process_payment">Confirm Payment</button>
</form>
<?php } else { ?>
  <p style="color: green; font-weight: bold;">This booking is already fully paid. Payment form is locked.</p>
<?php } ?>

</body>
</html>