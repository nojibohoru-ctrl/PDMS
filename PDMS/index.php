<?php session_start(); ?>
<?php include "config.php"; ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>PDMS Login</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div id="loginPage" class="login-container">
    <div class="login-box">
      <h1>PDMS</h1>
      <p>Purchasing and Delivery Management System</p>
      <form action="actions/login.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" name="username" id="username" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
          <label for="role">User Type</label>
          <select name="role" id="role" required>
            <option value="">Select Role</option>
            <option value="admin">Administrator</option>
            <option value="purchasing">Purchasing Staff</option>
            <option value="delivery">Delivery Staff</option>
            <option value="manager">Manager</option>
          </select>
        </div>
        <button type="submit" class="btn">Log In</button>
      </form>
      <?php if(isset($_GET['error'])): ?>
        <p style="color:red; text-align:center; margin-top:10px;">Invalid credentials or role.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
