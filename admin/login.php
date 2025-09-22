<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded admin credentials (for now)
    $correct_username = "admin";
    $correct_password = "password";  // Replace this before real-world use!

    if ($username === $correct_username && $password === $correct_password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid login credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9fb;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        .login-box h1 {
            margin-bottom: 20px;
            color: #e91e63;
            font-size: 22px;
            font-weight: 600;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group input:focus {
            border-color: #e91e63;
            outline: none;
            box-shadow: 0 0 4px rgba(233,30,99,0.3);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #e91e63;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #c2185b;
        }
        .back-link {
            display: block;
            margin-top: 15px;
            font-size: 14px;
            color: #555;
            text-decoration: none;
        }
        .back-link:hover {
            color: #e91e63;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h1>Admin Login</h1>

    <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>

    <form method="post">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>

    <a href="../index.php" class="back-link">← Back to Home</a>
</div>

</body>
</html>
