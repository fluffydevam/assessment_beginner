<?php 
// nav.php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: /assessment_beginner/login.php");
    exit;
}
?>
<div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; align-items:center;">
  <a href="/assessment_beginner/index.php">Dashboard</a>
  <a href="/assessment_beginner/pages/clients_list.php">Clients</a>
  <a href="/assessment_beginner/pages/services_list.php">Services</a>
  <a href="/assessment_beginner/pages/bookings_list.php">Bookings</a>
  <a href="/assessment_beginner/pages/tools_list_assign.php">Tools</a>
  <a href="/assessment_beginner/pages/payments_list.php">Payments</a>
  <a href="/assessment_beginner/logout.php" style="color:red; margin-left:auto;">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
</div>
<hr>