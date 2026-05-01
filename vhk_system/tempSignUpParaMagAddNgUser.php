<?php
// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "vhk_system");

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = trim($_POST['username']);
    $pass_input = $_POST['password'];

    // 2. Encrypt the password automatically
    $hashed_password = password_hash($pass_input, PASSWORD_DEFAULT);

    // 3. Insert into database
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $user_input, $hashed_password);

    if ($stmt->execute()) {
        $message = "<p style='color: green;'>Success! User created. <a href='login.php'>Go to Login</a></p>";
    } else {
        $message = "<p style='color: red;'>Error: Username might already exist.</p>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account - VHK System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #e0e0e0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 350px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; }
        button { width: 100%; padding: 12px; background: #403F8E; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        h2 { margin-bottom: 20px; color: #333; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Create Account</h2>
        <?php echo $message; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Choose Username" required>
            <input type="password" name="password" placeholder="Choose Password" required>
            <button type="submit">Register User</button>
        </form>
        <p style="margin-top: 15px; font-size: 13px;"><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>