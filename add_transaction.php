<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user'])) { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['user'];
    $user_query = $conn->query("SELECT email FROM users WHERE username='$username'");
    $email = $user_query->fetch_assoc()['email'];

    $amount = $_POST['amount'];
    // Handle Custom Category
    $category = ($_POST['category'] === 'Custom') ? $_POST['custom_cat'] : $_POST['category'];
    $notes = $conn->real_escape_string($_POST['notes']);

    $sql = "INSERT INTO expenses (user_email, amount, category, notes) VALUES ('$email', '$amount', '$category', '$notes')";
    $conn->query($sql);

    header("Location: dashboard.php");
    exit();
}
?>