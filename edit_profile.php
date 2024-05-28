<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current user's data
$current_username = $_SESSION['username'];
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare statement failed: " . $conn->error);
}

$stmt->bind_param("s", $current_username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if any changes were made
    $changes_made = false;

    // Handle username change
    if (!empty($_POST['new_username']) && $_POST['new_username'] != $current_username) {
        $new_username = $_POST['new_username'];
        $changes_made = true;

        // Update username in the database
        $update_username_query = "UPDATE users SET username = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_username_query);
        
        if (!$update_stmt) {
            die("Prepare statement failed: " . $conn->error);
        }

        $update_stmt->bind_param("ss", $new_username, $current_username);
        if ($update_stmt->execute()) {
            // Update session username
            $_SESSION['username'] = $new_username;
            $current_username = $new_username;
        } else {
            echo "Error updating username: " . $update_stmt->error;
        }
    }

    // Handle profile picture change
    if (!empty($_FILES['profile_picture']['name'])) {
        $profile_picture = $_FILES['profile_picture'];
        $changes_made = true;

        // Process profile picture change (code not shown for brevity)
    }

    // If no changes were made, redirect to home.php without displaying any message
    if (!$changes_made) {
        header("Location: home.php");
        exit();
    }

    // If changes were made, redirect to home.php with a success message
    header("Location: home.php?update=success");
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJXlnivjOO3FKT3lNu0WJv5AZXyG/6vB+L0" crossorigin="anonymous">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style/home.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            overflow-x: hidden;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 100%;
            text-align: center;
            margin: 20px auto 0;
        }

        .container h2 {
            margin-bottom: 25px;
            color: #333;
            font-weight: 500;
        }

        .container label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            text-align: left;
        }

        .container input[type="text"],
        .container input[type="file"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .container input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .container input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <form method="POST" action="edit_profile.php" enctype="multipart/form-data">
            <label for="new_username">New Username:</label>
            <input type="text" id="new_username" name="new_username" value="<?php echo htmlspecialchars($current_username); ?>">

            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture">

            <input type="submit" value="Update Profile">
        </form>
    </div>
</body>
</html>
