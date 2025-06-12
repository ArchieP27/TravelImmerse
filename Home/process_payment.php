<?php
session_start();
require('fpdf/fpdf.php'); // Include FPDF
require('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['payment_confirmed'] = true;

    $user_id = $_SESSION['user_id'];

// Fetch user's email from the database
$query = "SELECT email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

$user_email = $user['email']; // User's email address

// Ensure total price is set
if (!isset($_SESSION['total_price']) || $_SESSION['total_price'] <= 0) {
    die("Error: No valid booking found.");
}

$totalPrice = $_SESSION['total_price'];

// Generate ticket PDF
$ticket_dir = "tickets/";
if (!is_dir($ticket_dir)) {
    mkdir($ticket_dir, 0777, true);
}

$ticket_file = $ticket_dir . "ticket_" . $user_id . "_" . time() . ".pdf";
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Travel Immerse - Booking Ticket', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(190, 10, 'Date: ' . date('d-m-Y'), 0, 1, 'R');
$pdf->Ln(10);

// Company Logo
$pdf->Image('../images/Logo.png', 10, 10, 30);
$pdf->Ln(20);

// Booking Summary
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, 'Your Bookings:', 0, 1);
$pdf->SetFont('Arial', '', 12);

if (!empty($_SESSION['guide_bookings'])) {
    foreach ($_SESSION['guide_bookings'] as $booking) {
        $pdf->Cell(190, 10, "Guide: {$booking['guide_name']} ({$booking['days']} days) - Rs." . number_format($booking['total_price'], 2), 0, 1);
    }
}

if (!empty($_SESSION['resort_bookings'])) {
    foreach ($_SESSION['resort_bookings'] as $booking) {
        $pdf->Cell(190, 10, "Resort: {$booking['resort_name']} ({$booking['days']} nights) - Rs. " . number_format($booking['total_price'], 2), 0, 1);
    }
}

if (!empty($_SESSION['restaurant_bookings'])) {
    foreach ($_SESSION['restaurant_bookings'] as $booking) {
        $pdf->Cell(190, 10, "Restaurant: {$booking['restaurant_name']} (For {$booking['num_people']} people) - Rs. " . number_format($booking['total_price'], 2), 0, 1);
    }
}

// 🚀 **Updated Transport Booking Formatting**
if (!empty($_SESSION['transport_bookings'])) {
    foreach ($_SESSION['transport_bookings'] as $booking) {
        $parts = explode(', ', $booking['transport_option']);
        $transport_details = [];
        $price = '0.00';

        foreach ($parts as $part) {
            if (strpos($part, 'Price:') !== false) {
                $price = trim(str_replace('Price:', '', $part));
            } else {
                $transport_details[] = $part;
            }
        }

        $transport_info = implode(', ', $transport_details);
        $pdf->Cell(190, 10, "Transport: {$booking['mode_of_transport']} - {$transport_info} - Rs. " . number_format((float)$price, 2), 0, 1);
    }
}

// Final Price
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, "Total Price: Rs. " . number_format($totalPrice, 2), 0, 1, 'R');

// Save PDF
$pdf->Output('F', $ticket_file);

// Store ticket details in the database
$booking_date = date("Y-m-d H:i:s");
$stmt = $conn->prepare("INSERT INTO tickets (user_id, ticket_path, booking_date) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $ticket_file, $booking_date);
$stmt->execute();
$stmt->close();

// Send Email with Ticket
$subject = "Your Travel Immerse Ticket";
$message = "Dear Customer,\n\nThank you for booking with Travel Immerse! Your ticket is attached below.\n\nBest Regards,\nTravel Immerse Team";
$headers = "From: support@travelimmerse.com";

// Read PDF file
$fileData = file_get_contents($ticket_file);
$attachment = chunk_split(base64_encode($fileData));

// Email Boundary
$boundary = md5(time());
$headers .= "\nMIME-Version: 1.0";
$headers .= "\nContent-Type: multipart/mixed; boundary=\"$boundary\"";

// Email Body
$body = "--$boundary\n";
$body .= "Content-Type: text/plain; charset=ISO-8859-1\n";
$body .= "Content-Transfer-Encoding: 7bit\n\n";
$body .= "$message\n\n";

// Attach PDF
$body .= "--$boundary\n";
$body .= "Content-Type: application/pdf; name=\"ticket.pdf\"\n";
$body .= "Content-Transfer-Encoding: base64\n";
$body .= "Content-Disposition: attachment; filename=\"ticket.pdf\"\n\n";
$body .= "$attachment\n\n";
$body .= "--$boundary--";

// Send Email
if (mail($user_email, $subject, $body, $headers)) {
    // echo "Payment successful! Ticket sent via email.";
} else {
    echo "Payment successful, but failed to send ticket.";
}

    header("Location: payment.php?success=1");
    exit();
}
?>
