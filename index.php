<?php
session_start();

// Disable caching to prevent back button access after logout
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login if session not set
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

$user_id = $_SESSION['user_id']; 
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Safe query: bind user_id, inject limit/start directly
$sql = "SELECT * FROM transactions WHERE user_id=? ORDER BY created_at DESC LIMIT $start, $limit";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();


// Get total records for pagination count
$stmt_total = $conn->prepare("SELECT COUNT(*) AS total FROM transactions WHERE user_id=?");
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Totals for the logged-in user
$stmt_income = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE type='income' AND user_id=?");
$stmt_income->bind_param("i", $user_id);
$stmt_income->execute();
$total_income = $stmt_income->get_result()->fetch_assoc()['total'] ?? 0;

$stmt_expense = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE type='expense' AND user_id=?");
$stmt_expense->bind_param("i", $user_id);
$stmt_expense->execute();
$total_expense = $stmt_expense->get_result()->fetch_assoc()['total'] ?? 0;


$total_balance = $total_income - $total_expense;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Personal Tracker</title>
  <link rel="stylesheet" href="style.css">
  
</head>
<body>
<header>
  <nav class="navbar">
    <div class="logo">
      <h1>Personal Tracker</h1>
    </div>
    <div class="nav-links">
      <span>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
      <a href="logout.php" class="logout-btn" onclick="return confirmLogout()">Logout</a>

    </div>
  </nav>
</header>


<section class="summary">
  <div class="card income" data-tooltip="Total Income Earned">
    <h3>Income</h3>
    <p>Rs. <?= number_format($total_income, 2) ?></p>
  </div>
  <div class="card expense" data-tooltip="Total Expenses Made">
    <h3>Expense</h3>
    <p>Rs. <?= number_format($total_expense, 2) ?></p>
  </div>
  <div class="card total" data-tooltip="Available Balance">
    <h3>Total</h3>
    <p>Rs. <?= number_format($total_balance, 2) ?></p>
  </div>
  <button class="add-btn" onclick="openForm()">+ Add Transaction</button>
</section>


<!-- Modal Form (Add/Edit) -->
<div id="formModal" class="modal">
  <div class="modal-content">
    <h2 id="modal-title">Add Transaction</h2>
    <form id="transactionForm" action="add_transaction.php" method="POST">
      <input type="hidden" name="id" id="transaction-id">
      <label>Type:</label>
      <select name="type" id="transaction-type" required>
        <option value="income">Income</option>
        <option value="expense">Expense</option>
      </select>
      <label>Description:</label>
      <input type="text" name="description" id="transaction-desc" placeholder="For what used money" required>
      <label>Amount (Rs):</label>
      <input type="number" name="amount" id="transaction-amount" step="0.01" required>
      <div class="buttons">
        <button type="submit">Save</button>
        <button type="button" onclick="closeForm()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<section class="table-section">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Type</th>
        <th>Description</th>
        <th>Amount (Rs)</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0) { ?>
        <?php while($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td class="<?= $row['type'] ?>"><?= ucfirst($row['type']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td>Rs. <?= number_format($row['amount'], 2) ?></td>
            <td>
              <a href="delete_transaction.php?id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Delete this record?')">Delete</a>
              <button class="edit-btn" onclick="editTransaction(<?= $row['id'] ?>,'<?= $row['type'] ?>','<?= addslashes($row['description']) ?>',<?= $row['amount'] ?>)">Edit</button>
            </td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr><td colspan="5">No transactions found.</td></tr>
      <?php } ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
      <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
    <?php endif; ?>
  </div>
</section>

<footer>
  <p>© <?= date("Y") ?> Personal Tracker | Built with  PHP & MySQL</p>
</footer>


<script>
    window.addEventListener("pageshow", function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        // Reload page to trigger PHP session check
        window.location.reload();
    }
});
  function openForm() {
    document.getElementById('modal-title').innerText = "Add Transaction";
    document.getElementById('transactionForm').action = "add_transaction.php";
    document.getElementById('transaction-id').value = "";
    document.getElementById('transaction-type').value = "income";
    document.getElementById('transaction-desc').value = "";
    document.getElementById('transaction-amount').value = "";
    document.getElementById('formModal').style.display = 'flex';
  }

  function closeForm() {
    document.getElementById('formModal').style.display = 'none';
  }

  function editTransaction(id, type, desc, amount) {
    document.getElementById('modal-title').innerText = "Edit Transaction";
    document.getElementById('transactionForm').action = "edit_transaction.php";
    document.getElementById('transaction-id').value = id;
    document.getElementById('transaction-type').value = type;
    document.getElementById('transaction-desc').value = desc;
    document.getElementById('transaction-amount').value = amount;
    document.getElementById('formModal').style.display = 'flex';
  }
</script>
</body>
</html>
