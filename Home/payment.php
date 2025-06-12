<?php
include 'process_payment.php';

// Redirect if there is no payment
if (!isset($_SESSION['total_price']) || $_SESSION['total_price'] <= 0) {
    header("Location: bucket_list.php");
    exit();
}

// Check for successful payment
$payment_success = isset($_GET['success']) && $_GET['success'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✨ Secure Payment - Travel Immerse ✨</title>
    <link rel="stylesheet" href="stylehome.css">
    <link rel="stylesheet" href="footerdes.css">
    <link rel="stylesheet" href="payment.css"> <!-- External CSS file -->
    <link href="https://fonts.googleapis.com/css2?family=Girassol&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'header.php'; ?>

<div class="payment-container">
    <div class="payment-header">
        <h2>✨ Secure Payment Gateway ✨</h2>
        <p>Complete your booking with our secure payment system</p>
    </div>

    <?php if ($payment_success): ?>
        <div class="payment-success">
            <h3>Payment Successful!</h3>
            <p>Your payment of ₹<?= number_format($_SESSION['total_price'], 2); ?> has been processed successfully.</p>
            <p>Your booking confirmation and tickets will be sent to your registered email address.</p>
            <p>Thank you for choosing Travel Immerse!</p>
            <a href="booking.php" class="submit-btn">Return to Home</a>
        </div>
        <?php
// Retain user ID before clearing session
$user_id = $_SESSION['user_id'];

// Clear all session variables
session_unset();

// Restore user ID
$_SESSION['user_id'] = $user_id;

// Empty bucket list variables
$_SESSION['guide_bookings'] = [];
$_SESSION['resort_bookings'] = [];
$_SESSION['restaurant_bookings'] = [];
$_SESSION['transport_bookings'] = [];
?>
    <?php else: ?>
        <div class="payment-amount">
            <h3>Total Amount to Pay</h3>
            <p class="price">₹<?= number_format($_SESSION['total_price'], 2); ?></p>
        </div>

        <h3 class="method-title">Choose Payment Method</h3>
        <div class="payment-methods">
            <div class="payment-method"><img src="../images/visa.png" alt="Visa"></div>
            <div class="payment-method"><img src="../images/mastercard.png" alt="Mastercard"></div>
            <div class="payment-method"><img src="../images/amex.png" alt="American Express"></div>
            <div class="payment-method"><img src="../images/paypal.png" alt="PayPal"></div>
            <div class="payment-method"><img src="../images/upi.png" alt="UPI"></div>
            <div class="payment-method"><img src="../images/netbanking.png" alt="Net Banking"></div>
        </div>

        <form class="payment-form" method="POST">
            <div class="form-group">
                <label for="card_number">Card Number</label>
                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
            </div>
            
            <div class="form-group">
                <label for="card_name">Name on Card</label>
                <input type="text" id="card_name" name="card_name" placeholder="ENTER YOUR NAME" required>
            </div>
            
            <div class="flex-container">
                <div class="form-group">
                    <label for="expiry">Expiry Date</label>
                    <input type="text" id="expiry" name="expiry" placeholder="MM/YY" required>
                </div>
                
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="password" id="cvv" name="cvv" placeholder="123" required maxlength="3">
                </div>
            </div>
            
            <div class="secure-badge">
                <i>🔒</i> <span>Secure SSL Encryption</span>
            </div>
            
            <button type="submit" name="pay_now" class="submit-btn">Pay ₹<?= number_format($_SESSION['total_price'], 2); ?></button>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
