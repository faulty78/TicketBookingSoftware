<?php
session_start();

// Database configuration
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "user_db";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        if (strlen($username) < 1 || strlen($username) > 17) {
            $message = "Username must be between 1 and 17 characters long!";
        } else {
            $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

            if ($conn->query($sql) === TRUE) {
                $message = "Registration Successful!";
            } else {
                $message = "Error: ". $sql. "<br>". $conn->error;
            }
        }
    } elseif (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $sql = "SELECT id, username, password FROM users WHERE username = '$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $row['username'];
                header("Location: home.php");
                exit();
            } else {
                $message = "Invalid password!";
            }
        } else {
            $message = "Invalid username or password! Please check your credentials.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login and Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
        <?php if ($message) : ?>
            <p class="error"><?php echo $message;?></p>
        <?php endif; ?>

        <?php if (isset($_GET['action']) && $_GET['action'] == 'register') : ?>
            <h2>Register</h2>
            <form action="index.php" method="post" autocomplete="off" onsubmit="showloading()">
                <label for="username">*Username:</label>
                <input type="text" name="username" required autocomplete="off">
                <label for="email">*Email:</label>
                <input type="email" name="email" required autocomplete="off">
                <label for="password">*Password:</label>
                <input type="password" name="password" required autocomplete="off">
                <input type="submit" name="register" value="Register">
            </form>
            <a class="toggle-link" href="index.php">Already have an account? Login</a>
        <?php else : ?>
            <h2>Login</h2>
            <form action="index.php" method="post" autocomplete="off" onsubmit="showloading()">
                <label for="username">*Username:</label>
                <input type="text" name="username" required autocomplete="off">
                <label for="password">*Password:</label>
                <input type="password" name="password" required autocomplete="off">
                <input type="submit" name="login" value="Login">
            </form>
            <a class="toggle-link" href="index.php?action=register">Don't have an account? Register</a>
        <?php endif; ?>
    </div>
    <script>
        function showTicket() {
            var ticketId = prompt("Please enter your ticket ID: ")
            if (ticketId) {
                window.open("ticket.php?ticket_id=" + ticketId, "_blank");
            }
        }
    </script>
</body>
</html>
