<?php
// Start output buffering at the very beginning
ob_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ticket_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once('TCPDF-main/tcpdf.php');
require_once('phpqrcode/qrlib.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate a random 8-digit ticket ID
    $ticket_id = mt_rand(10000000, 99999999);

    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $date = $_POST['date'];

    // Generate the QR code and store it in a variable
    ob_start();
    QRcode::png($ticket_id, null, QR_ECLEVEL_L, 4);
    $qrCodeImage = ob_get_contents();
    ob_end_clean();

    // Convert QR code image to binary data
    $qrCodeBinary = mysqli_real_escape_string($conn, $qrCodeImage);

    // Create a new PDF document
    $pdf = new TCPDF();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', '', 12);

    // Load the ticket template image
    $pdf->Image('ticket_template.png', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);

    // Add ticket details in the correct positions
    $pdf->SetXY(5, 83); // Coordinates for Ticket ID
    $pdf->Cell(40, 10, $ticket_id, 0, 0, 'C');

    $pdf->SetXY(50, 83); // Coordinates for Name
    $pdf->Cell(40, 10, $name, 0, 0, 'C');

    $pdf->SetXY(100, 83); // Coordinates for Email
    $pdf->Cell(40, 10, $email, 0, 0, 'C');

    $pdf->SetXY(145, 83); // Coordinates for Date
    $pdf->Cell(40, 10, $date, 0, 0, 'C');

    $pdf->SetXY(190, 83); // Coordinates for Age
    $pdf->Cell(40, 10, $age, 0, 0, 'C');

    // Add the QR code to the PDF from the variable
    $pdf->Image('@' . $qrCodeImage, 5, 93, 30, 30, 'PNG');

    // Output the PDF as a string
    $pdfContent = $pdf->Output('', 'S');
    $pdfContentBinary = mysqli_real_escape_string($conn, $pdfContent);

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO tickets (ticket_id, name, email, age, date_of_visit, pdf_content, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississs", $ticket_id, $name, $email, $age, $date, $pdfContentBinary, $qrCodeBinary);

    if ($stmt->execute()) {
        echo "<script>
        navigator.clipboard.writeText('$ticket_id').then(function() {
            alert('Ticket Booked Successfully. Your ticket ID is: ' + '$ticket_id' + '. It has been copied to the clipboard.');
        }, function(err) {
            alert('Ticket booked successfully, there was an error copying the ticket ID to the clipboard. Your ticket ID is: ' + '$ticket_id');
        });
    </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Close the database connection
$conn->close();

// Get the logged-in username
session_start();
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJXlnivjOO3FKT3lNu0WJv5AZXyG/6vB+L0" crossorigin="anonymous">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <title>Site-Seeing Ticket Booking</title>
    <link rel="stylesheet" href="style/home.css">
    <style>
        .profile {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: white;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0;
            border-radius: 5px;
            overflow: hidden;
        }

        .dropdown-menu a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }

        .dropdown-menu a:hover {
            background-color: #f1f1f1;
        }

        .dropdown-menu .logout {
            color: red;
        }

        /* Popup styles */
        #pdfPopup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 800px;
            height: 80%;
            background-color: white;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        #pdfPopup.visible {
            display: block;
            opacity: 1;
        }

        #pdfPopup .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
            line-height: 30px;
        }

        #pdfPopup iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <header>
        <h1>Intelline - Ticket Booking</h1>
        <div class="profile">
            <button id="viewTicketBtn">View Ticket</button>
            <div id="ticketInputDiv" style="display: none;">
                <input type="text" id="ticketIdInput" placeholder="Enter Ticket ID">
                <button id="fetchTicketBtn">Fetch Ticket</button>
            </div>
            <img src="images/default-profile.png" alt="Default Profile Picture" class="profile-picture">
            <span class="username <?php if (strlen($username) > 15) echo 'long';?>" id="username"><?php echo $username;?></span>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="edit_profile.php">Edit Profile</a>
                <a href="logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <h2>Book Your Ticket</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                <label for="name">*Full Name:</label>
                <input type="text" id="name" name="name" autocomplete="off" required>
                <label for="email">*Email:</label>
                <input type="email" id="email" name="email" autocomplete="off" required>
                <label for="age">*Age:</label>
                <input type="number" id="age" name="age" autocomplete="off" required>
                <label for="date">*Date of Visit:</label>
                <input type="date" id="date" name="date" autocomplete="off" required>
                <input type="submit" value="Book Ticket">
            </form>
        </div>
    </main>
    <div id="pdfPopup">
        <button class="close-btn">&times;</button>
        <iframe id="pdfFrame"></iframe>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profilePicture = document.querySelector('.profile-picture');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const viewTicketBtn = document.getElementById('viewTicketBtn');
            const ticketInputDiv = document.getElementById('ticketInputDiv');
            const fetchTicketBtn = document.getElementById('fetchTicketBtn');
            const ticketIdInput = document.getElementById('ticketIdInput');
            const pdfPopup = document.getElementById('pdfPopup');
            const closeBtn = document.querySelector('#pdfPopup .close-btn');
            const pdfFrame = document.getElementById('pdfFrame');

            profilePicture.addEventListener('click', function() {
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            });

            viewTicketBtn.addEventListener('click', function() {
                ticketInputDiv.style.display = ticketInputDiv.style.display === 'block' ? 'none' : 'block';
            });

            fetchTicketBtn.addEventListener('click', function() {
                const ticketId = ticketIdInput.value.trim();
                if (ticketId) {
                    fetch(`fetch_ticket.php?ticket_id=${ticketId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const pdfContent = data.pdfContent;
                                const pdfBlob = new Blob([Unit8Array.from(atob(pdfContent), c => c.charCodeAt(0))], { type: 'application/pdf' });
                                const pdfUrl = URL.createObjectURL(pdfBlob);
                                pdfFrame.src = pdfUrl;
                                pdfPopup.classList.add('visible');
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching ticket:', error);
                            alert('An error occurred while fetching the ticket.');
                        });
                } else {
                    alert('Please enter a valid ticket ID.');
                }
            });

            closeBtn.addEventListener('click', function() {
                pdfPopup.classList.remove('visible');
                pdfFrame.src = '';
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!profilePicture.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.style.display = 'none';
                }
            });

            // Hide ticket input when clicking outside
            document.addEventListener('click', function(event) {
                if (!viewTicketBtn.contains(event.target) && !ticketInputDiv.contains(event.target)) {
                    ticketInputDiv.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
