<?php session_start(); if(isset($_SESSION['user'])) header("Location: dashboard.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to FinTrackPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Montserrat:wght@700;800&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; height: 100vh; display: flex; overflow: hidden; background: #0f172a; }
        .brand-section { flex: 1; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); display: flex; flex-direction: column; justify-content: center; padding: 60px; position: relative; overflow: hidden; }
        .brand-section::before { content: ''; position: absolute; top: -100px; left: -100px; width: 400px; height: 400px; background: rgba(6, 182, 212, 0.2); filter: blur(100px); border-radius: 50%; }
        .logo { font-family: 'Montserrat', sans-serif; font-size: 32px; font-weight: 800; color: #fff; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .logo span { color: #06b6d4; }
        .hero-text { font-size: 48px; font-weight: 700; color: #fff; line-height: 1.2; margin-bottom: 20px; z-index: 2; }
        .hero-sub { color: #94a3b8; font-size: 18px; max-width: 80%; line-height: 1.6; z-index: 2; }
        .form-section { flex: 0.8; background: #fff; display: flex; flex-direction: column; justify-content: center; padding: 60px; position: relative; }
        .form-container { width: 100%; max-width: 400px; margin: 0 auto; }
        .form-title { font-size: 28px; font-weight: 700; color: #0f172a; margin-bottom: 10px; }
        .form-desc { color: #64748b; margin-bottom: 30px; font-size: 14px; }
        .input-group { margin-bottom: 20px; position: relative; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        input { width: 100%; padding: 15px 15px 15px 45px; border: 1px solid #cbd5e1; border-radius: 12px; outline: none; font-size: 15px; transition: 0.3s; background: #f8fafc; color: #334155; }
        input:focus { border-color: #06b6d4; background: #fff; box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1); }
        .btn-main { width: 100%; background: #06b6d4; color: #fff; padding: 15px; border: none; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-main:hover { background: #0891b2; transform: translateY(-2px); }
        .toggle-text { margin-top: 20px; text-align: center; color: #64748b; font-size: 14px; }
        .toggle-text a { color: #06b6d4; text-decoration: none; font-weight: 700; cursor: pointer; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="brand-section">
        <div class="logo"><i class="fa-solid fa-wallet"></i> FinTrack<span>Pro</span></div>
        <div class="hero-text">Master Your Money with <span style="color:#06b6d4">AI Precision.</span></div>
        <div class="hero-sub">Track expenses, scan receipts, and predict your financial future with our intelligent budget analyzer.</div>
    </div>
    <div class="form-section">
        <div class="form-container" id="login-box">
            <div class="form-title">Welcome Back</div>
            <div class="form-desc">Please enter your details to sign in.</div>
            <form action="auth.php" method="POST">
                <div class="input-group"><i class="fa-solid fa-envelope"></i><input type="email" name="email" placeholder="Email Address" required></div>
                <div class="input-group"><i class="fa-solid fa-lock"></i><input type="password" name="password" placeholder="Password" required></div>
                <button type="submit" name="signin" class="btn-main">Sign In</button>
            </form>
            <div class="toggle-text">Don't have an account? <a onclick="toggleForms()">Create Account</a></div>
        </div>
        <div class="form-container hidden" id="signup-box">
            <div class="form-title">Create Account</div>
            <div class="form-desc">Start your financial journey today.</div>
            <form action="auth.php" method="POST">
                <div class="input-group"><i class="fa-solid fa-user"></i><input type="text" name="username" placeholder="Full Name" required></div>
                <div class="input-group"><i class="fa-solid fa-envelope"></i><input type="email" name="email" placeholder="Email Address" required></div>
                <div class="input-group"><i class="fa-solid fa-lock"></i><input type="password" name="password" placeholder="Password" required></div>
                <button type="submit" name="signup" class="btn-main">Get Started</button>
            </form>
            <div class="toggle-text">Already have an account? <a onclick="toggleForms()">Sign In</a></div>
        </div>
    </div>
    <script>
        function toggleForms() { document.getElementById('login-box').classList.toggle('hidden'); document.getElementById('signup-box').classList.toggle('hidden'); }
    </script>
</body>
</html>