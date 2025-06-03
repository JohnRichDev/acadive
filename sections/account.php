<?php
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION["username"];
$uid = $_SESSION["user_id"];

include('database/connection.php');
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