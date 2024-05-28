<?php
header('Content-Type: application/json');

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

if (isset($_GET['ticket_id'])) {
    $ticket_id = $_GET['ticket_id'];

    $stmt = $conn->prepare("SELECT pdf_content FROM tickets WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);

    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($pdf_content);
            $stmt->fetch();
            $response = [
                'success' => true,
                'pdfContent' => base64_encode($pdf_content),
            ];
        } else {
            $response = ['success' => false, 'message' => 'Ticket not found.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Error fetching ticket: ' . $stmt->error];
    }

    $stmt->close();
} else {
    $response = ['success' => false, 'message' => 'Ticket ID not provided.'];
}

$conn->close();

echo json_encode($response);
?>
