<?php
session_start();
include 'db_connect.php';

// SIGN UP
if (isset($_POST['signup'])) {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hashing

    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.location.href='index.php';</script>";
    } else {
        $conn->query("INSERT INTO users (username, email, password) VALUES ('$user', '$email', '$pass')");
        $_SESSION['user'] = $user;
        echo "<script>window.location.href='onboarding.php';</script>"; // Send to setup
    }
}

// SIGN IN
if (isset($_POST['signin'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user'] = $row['username'];
            $conn->query("UPDATE users SET last_login = NOW() WHERE email = '$email'");

            // Smart Onboarding Check
            if ($row['salary'] == 0.00 || $row['salary'] == NULL) {
                echo "<script>window.location.href='onboarding.php';</script>";
            } else {
                echo "<script>window.location.href='dashboard.php';</script>";
            }
        } else {
            echo "<script>alert('Incorrect Password'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location.href='index.php';</script>";
    }
}
?>