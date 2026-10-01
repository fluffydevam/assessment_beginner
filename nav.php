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
<!-- Global External Stylesheet -->
<link rel="stylesheet" href="/assessment_beginner/style.css">

<div class="nav-container">
  <a href="/assessment_beginner/index.php">Dashboard</a>
  <a href="/assessment_beginner/pages/clients_list.php">Clients</a>
  <a href="/assessment_beginner/pages/services_list.php">Services</a>
  <a href="/assessment_beginner/pages/bookings_list.php">Bookings</a>
  <a href="/assessment_beginner/pages/tools_list_assign.php">Tools</a>
  <a href="/assessment_beginner/pages/payments_list.php">Payments</a>
  <a href="/assessment_beginner/logout.php" style="color: #FFB3A7; margin-left: auto;">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
</div>