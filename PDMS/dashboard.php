<?php
session_start();
if(!isset($_SESSION['user'])) header('Location: index.php');
include 'config.php';
$user = $_SESSION['user'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>PDMS Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>PDMS Dashboard</h1>
      <div class="user-info">
        <span id="userRole"><?php echo ucfirst($role); ?></span>
        <span id="userName"><?php echo htmlspecialchars($user); ?></span>
        <a href="logout.php"><button class="logout-btn">Logout</button></a>
      </div>
    </div>

    <div class="nav-tabs" id="navTabs">
      <?php
        $tabs = ['overview'=>'Overview'];
        if($role=='admin' || $role=='purchasing') {
            $tabs += ['purchaseOrders'=>'Purchase Orders','suppliers'=>'Suppliers'];
        }
        if($role=='admin' || $role=='delivery') {
            $tabs += ['deliveries'=>'Deliveries'];
        }
        if($role=='admin') {
            $tabs += ['users'=>'User Management','settings'=>'Settings'];
        }
        $tabs += ['reports'=>'Reports'];
        foreach($tabs as $id => $label){
            echo "<button class='nav-tab' onclick=\"document.querySelectorAll('.content-section').forEach(s=>s.classList.remove('active'));document.getElementById('$id').classList.add('active')\">$label</button>";
        }
      ?>
    </div>

    <div id="overview" class="content-section active">
      <h2>Dashboard Overview</h2>
      <div class="stats-grid">
        <?php
          $orderCount = mysqli_fetch_row(mysqli_query($conn, 'SELECT COUNT(*) FROM purchase_orders'))[0];
          $pending = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM purchase_orders WHERE status='Pending'"))[0];
          $supCount = mysqli_fetch_row(mysqli_query($conn, 'SELECT COUNT(*) FROM suppliers'))[0];
          $inTransit = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM deliveries WHERE status='In Transit'"))[0];
          $stats = [
            ['Total Orders',$orderCount],
            ['Pending Approval',$pending],
            ['Active Suppliers',$supCount],
            ['Deliveries In Transit',$inTransit]
          ];
          foreach($stats as $s){
            echo "<div class='stat-card'><h3>{$s[0]}</h3><div class='number'>{$s[1]}</div></div>";
          }
        ?>
      </div>

      <h3>Recent Activity</h3>
      <table>
        <thead><tr><th>Date</th><th>Activity</th><th>User</th><th>Status</th></tr></thead>
        <tbody>
          <?php
            $activities = [];
            // Simple recent activities from orders/deliveries
            $q = mysqli_query($conn, "SELECT date, CONCAT('Order ', id, ' created') as activity, 'system' as user, status FROM purchase_orders ORDER BY date DESC LIMIT 5");
            while($r = mysqli_fetch_assoc($q)) {
                echo "<tr><td>{$r['date']}</td><td>{$r['activity']}</td><td>{$r['user']}</td><td><span class='badge'>{$r['status']}</span></td></tr>";
            }
          ?>
        </tbody>
      </table>
    </div>

    <div id="purchaseOrders" class="content-section">
      <h2>Purchase Orders</h2>
      <?php if($role=='admin' || $role=='purchasing'): ?>
      <button class="btn btn-success btn-small" onclick="document.getElementById('createOrderModal').classList.add('active')">Create New Order</button>
      <?php endif; ?>
      <table>
        <thead><tr><th>Order ID</th><th>Supplier</th><th>Product</th><th>Quantity</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php
            $q = mysqli_query($conn, "SELECT po.*, s.name as supplier_name FROM purchase_orders po LEFT JOIN suppliers s ON s.id=po.supplier_id ORDER BY po.id DESC");
            while($row = mysqli_fetch_assoc($q)){
              echo "<tr>";
              echo "<td>PO-". $row['id'] ."</td>";
              echo "<td>".htmlspecialchars($row['supplier_name'])."</td>";
              echo "<td>".htmlspecialchars($row['product'])."</td>";
              echo "<td>".htmlspecialchars($row['quantity'])."</td>";
              echo "<td>".htmlspecialchars($row['date'])."</td>";
              echo "<td><span class='badge'>{$row['status']}</span></td>";
              echo "<td>";
              if($role=='admin' || $role=='purchasing'){
                echo "<a href='actions/update_status.php?order_id={$row['id']}'><button class='btn btn-small btn-success'>Approve</button></a> ";
                echo "<a href='actions/delete.php?order_id={$row['id']}' onclick=\"return confirm('Delete order?')\"><button class='btn btn-small btn-danger'>Delete</button></a>";
              } else {
                echo "<button class='btn btn-small' onclick=\"alert('View only')\">View</button>";
              }
              echo "</td>";
              echo "</tr>";
            }
          ?>
        </tbody>
      </table>
    </div>

    <div id="suppliers" class="content-section">
      <h2>Supplier Management</h2>
      <?php if($role=='admin' || $role=='purchasing'): ?>
      <button class="btn btn-success btn-small" onclick="document.getElementById('addSupplierModal').classList.add('active')">Add Supplier</button>
      <?php endif; ?>
      <table>
        <thead><tr><th>Supplier ID</th><th>Name</th><th>Contact</th><th>Email</th><th>Products</th><th>Actions</th></tr></thead>
        <tbody>
          <?php
            $q = mysqli_query($conn, 'SELECT * FROM suppliers ORDER BY id DESC');
            while($s = mysqli_fetch_assoc($q)){
              echo "<tr>";
              echo "<td>SUP-".$s['id']."</td>";
              echo "<td>".htmlspecialchars($s['name'])."</td>";
              echo "<td>".htmlspecialchars($s['contact'])."</td>";
              echo "<td>".htmlspecialchars($s['email'])."</td>";
              echo "<td>".htmlspecialchars($s['products'])."</td>";
              echo "<td>";
              if($role=='admin' || $role=='purchasing'){
                echo "<a href='actions/delete.php?supplier_id={$s['id']}' onclick=\"return confirm('Delete supplier?')\"><button class='btn btn-small btn-danger'>Delete</button></a>";
              } else {
                echo "<button class='btn btn-small' onclick=\"alert('View only')\">View</button>";
              }
              echo "</td>";
              echo "</tr>";
            }
          ?>
        </tbody>
      </table>
    </div>

    <div id="deliveries" class="content-section">
      <h2>Delivery Management</h2>
      <table>
        <thead><tr><th>Delivery ID</th><th>Order ID</th><th>Product</th><th>Scheduled Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php
            $q = mysqli_query($conn, 'SELECT * FROM deliveries ORDER BY id DESC');
            while($d = mysqli_fetch_assoc($q)){
              echo "<tr>";
              echo "<td>DEL-".$d['id']."</td>";
              echo "<td>PO-".$d['order_id']."</td>";
              echo "<td>".htmlspecialchars($d['product'])."</td>";
              echo "<td>".htmlspecialchars($d['scheduled_date'])."</td>";
              echo "<td><span class='badge'>{$d['status']}</span></td>";
              echo "<td>";
              if($role=='admin' || $role=='delivery'){
                echo "<a href='actions/update_status.php?delivery_id={$d['id']}'><button class='btn btn-small btn-success'>Update Status</button></a>";
              } else {
                echo "<button class='btn btn-small'>View</button>";
              }
              echo "</td>";
              echo "</tr>";
            }
          ?>
        </tbody>
      </table>
    </div>

    <div id="users" class="content-section">
      <h2>User Management</h2>
      <?php if($role=='admin'): ?>
      <button class="btn btn-success btn-small" onclick="document.getElementById('addUserModal').classList.add('active')">Add User</button>
      <?php endif; ?>
      <table>
        <thead><tr><th>User ID</th><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php
            $q = mysqli_query($conn, 'SELECT * FROM users ORDER BY id DESC');
            while($u = mysqli_fetch_assoc($q)){
              echo "<tr>";
              echo "<td>USR-".$u['id']."</td>";
              echo "<td>".htmlspecialchars($u['username'])."</td>";
              echo "<td>".htmlspecialchars($u['role'])."</td>";
              echo "<td>".htmlspecialchars($u['status'])."</td>";
              echo "<td>";
              if($role=='admin'){
                echo "<a href='actions/delete.php?user_id={$u['id']}' onclick=\"return confirm('Delete user?')\"><button class='btn btn-small btn-danger'>Delete</button></a>";
              }
              echo "</td>";
              echo "</tr>";
            }
          ?>
        </tbody>
      </table>
    </div>

    <div id="reports" class="content-section">
      <h2>Reports</h2>
      <form method="GET">
        <select name="reportType">
          <option value="monthly">Monthly Summary</option>
          <option value="supplier">Supplier Performance</option>
          <option value="delivery">Delivery Status</option>
          <option value="purchase">Purchase Order Summary</option>
        </select>
        <button class="btn btn-success btn-small" type="submit">Generate</button>
      </form>

      <div id="reportOutput" style="margin-top:20px;">
        <?php
          if(isset($_GET['reportType'])){
            $rt = $_GET['reportType'];
            if($rt=='monthly' || $rt=='purchase'){
              echo "<h3>Purchase Orders</h3>";
              $q = mysqli_query($conn, 'SELECT * FROM purchase_orders ORDER BY id DESC LIMIT 50');
              echo "<table><tr><th>ID</th><th>Supplier</th><th>Product</th><th>Status</th></tr>";
              while($r = mysqli_fetch_assoc($q)){
                $sname = mysqli_fetch_row(mysqli_query($conn, "SELECT name FROM suppliers WHERE id={$r['supplier_id']}"))[0];
                echo "<tr><td>PO-{$r['id']}</td><td>".htmlspecialchars($sname)."</td><td>".htmlspecialchars($r['product'])."</td><td>".htmlspecialchars($r['status'])."</td></tr>";
              }
              echo "</table>";
            } else if($rt=='supplier'){
              echo "<h3>Supplier Performance (simple)</h3>";
              $q = mysqli_query($conn,'SELECT * FROM suppliers');
              echo "<table><tr><th>Supplier</th><th>Orders</th></tr>";
              while($s = mysqli_fetch_assoc($q)){
                $count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM purchase_orders WHERE supplier_id={$s['id']}"))[0];
                echo "<tr><td>".htmlspecialchars($s['name'])."</td><td>$count</td></tr>";
              }
              echo "</table>";
            } else if($rt=='delivery'){
              echo "<h3>Delivery Status Report</h3>";
              $q = mysqli_query($conn,'SELECT * FROM deliveries');
              echo "<table><tr><th>Delivery ID</th><th>Order ID</th><th>Product</th><th>Status</th></tr>";
              while($d = mysqli_fetch_assoc($q)){
                echo "<tr><td>DEL-{$d['id']}</td><td>PO-{$d['order_id']}</td><td>".htmlspecialchars($d['product'])."</td><td>".htmlspecialchars($d['status'])."</td></tr>";
              }
              echo "</table>";
            }
          }
        ?>
      </div>
    </div>

    <div id="settings" class="content-section">
      <h2>Settings</h2>
      <p>Static settings area. Edit config.php to change DB connection.</p>
    </div>
  </div>

  <!-- Modals -->
  <div class="modal" id="createOrderModal">
    <div class="modal-content">
      <span class="close-modal" onclick="document.getElementById('createOrderModal').classList.remove('active')">&times;</span>
      <h3>Create Purchase Order</h3>
      <form action="actions/add_order.php" method="POST">
        <div class="form-group">
          <label>Supplier</label>
          <select name="supplier_id" required>
            <option value="">Select Supplier</option>
            <?php
              $sq = mysqli_query($conn,'SELECT id,name FROM suppliers');
              while($ss = mysqli_fetch_assoc($sq)){
                echo "<option value='{$ss['id']}'>".htmlspecialchars($ss['name'])."</option>";
              }
            ?>
          </select>
        </div>
        <div class="form-group"><label>Product</label><input name="product" required></div>
        <div class="form-group"><label>Quantity</label><input type="number" name="quantity" required></div>
        <div class="form-group"><label>Date</label><input type="date" name="date" required></div>
        <div class="form-group"><label>Notes</label><textarea name="notes"></textarea></div>
        <button class="btn btn-success">Create Order</button>
      </form>
    </div>
  </div>

  <div class="modal" id="addSupplierModal">
    <div class="modal-content">
      <span class="close-modal" onclick="document.getElementById('addSupplierModal').classList.remove('active')">&times;</span>
      <h3>Add Supplier</h3>
      <form action="actions/add_supplier.php" method="POST">
        <div class="form-group"><label>Name</label><input name="name" required></div>
        <div class="form-group"><label>Contact</label><input name="contact"></div>
        <div class="form-group"><label>Email</label><input name="email" type="email"></div>
        <div class="form-group"><label>Products</label><input name="products"></div>
        <div class="form-group"><label>Address</label><textarea name="address"></textarea></div>
        <button class="btn btn-success">Add Supplier</button>
      </form>
    </div>
  </div>

  <div class="modal" id="addUserModal">
    <div class="modal-content">
      <span class="close-modal" onclick="document.getElementById('addUserModal').classList.remove('active')">&times;</span>
      <h3>Add User</h3>
      <form action="actions/add_user.php" method="POST">
        <div class="form-group"><label>Username</label><input name="username" required></div>
        <div class="form-group"><label>Password</label><input name="password" required></div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" required>
            <option value="admin">Administrator</option>
            <option value="purchasing">Purchasing Staff</option>
            <option value="delivery">Delivery Staff</option>
            <option value="manager">Manager</option>
          </select>
        </div>
        <button class="btn btn-success">Add User</button>
      </form>
    </div>
  </div>

</body>
</html>
