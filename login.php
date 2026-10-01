<?php
session_start();
require_once "db.php";

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = mysqli_prepare($conn, "SELECT user_id, username, password FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit;
            }
        }
        $error = "Invalid username or password!";
    } catch (Exception $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8">
<link rel="stylesheet" href="style.css">
<title>Login</title>

</head>
<body>
<h2>System Login</h2>
<p style="color:red;"><?php echo $error; ?></p>
<form method="post">
  <label>Username</label><br>
  <input type="text" name="username" required><br><br>
  
  <label>Password</label><br>
  <input type="password" name="password" required><br><br>
  
  <button type="submit" name="login">Login</button>
</form>
</body>
</html>