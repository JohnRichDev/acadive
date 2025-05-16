<?php
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit;
}
$username = $_SESSION["username"];

include(__DIR__ . '/../database/connection.php');

$uid = null;
$query = "SELECT id FROM users WHERE username='$username'";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $uid = $row['id'];
}
?>
<div class="section-content">
    <h2>Account Settings</h2>
    <div class="card">
        <h3>Account Information</h3>
        <p><strong>UID:</strong> <?php echo $uid !== null ? $uid : 'N/A'; ?></p>
        <p><strong>Username:</strong> <?php echo $username; ?></p>
        <p><strong>Password:</strong> ********</p>

    </div>
</div>