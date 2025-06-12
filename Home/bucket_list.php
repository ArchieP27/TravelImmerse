<?php
session_start();

// Function to remove an item from session arrays
if (isset($_GET['remove']) && isset($_GET['type'])) {
    $type = $_GET['type'];
    $index = $_GET['remove'];

    if ($type === "guide" && isset($_SESSION['guide_bookings'][$index])) {
        unset($_SESSION['guide_bookings'][$index]);
    } elseif ($type === "resort" && isset($_SESSION['resort_bookings'][$index])) {
        unset($_SESSION['resort_bookings'][$index]);
    } elseif ($type === "restaurant" && isset($_SESSION['restaurant_bookings'][$index])) {
        unset($_SESSION['restaurant_bookings'][$index]);
    } elseif ($type === "transport" && isset($_SESSION['transport_bookings'][$index])) {
        unset($_SESSION['transport_bookings'][$index]);
    }

    // Re-index arrays to prevent gaps
    $_SESSION['guide_bookings'] = array_values($_SESSION['guide_bookings'] ?? []);
    $_SESSION['resort_bookings'] = array_values($_SESSION['resort_bookings'] ?? []);
    $_SESSION['restaurant_bookings'] = array_values($_SESSION['restaurant_bookings'] ?? []);
    $_SESSION['transport_bookings'] = array_values($_SESSION['transport_bookings'] ?? []);

    header("Location: bucket_list.php"); // Refresh the page after removing item
    exit();
}

$totalPrice = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✨ Your Bucket List - Travel Immerse ✨</title>
    <link rel="stylesheet" href="bucket_list.css">
    <link rel="stylesheet" href="stylehome.css">
    <link rel="stylesheet" href="footerdes.css">
    <link href="https://fonts.googleapis.com/css2?family=Girassol&display=swap" rel="stylesheet">
    <style>
        /* Custom styles to fix the issues */
        .scrollable-table {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
            /* Hide scrollbar but keep functionality */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        .scrollable-table::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        
        .price-summary {
            background: #fff9f5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #ffd9c2;
        }
        
        .price-highlight {
            color: #ff6600;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .empty-bucket {
            padding: 30px;
            font-size: 1.2em;
            color: #666;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>✈ Your Bucket List - Adventure Awaits! ✨</h2>
    <p>🌟 Don't miss out! Confirm your bookings before they're gone! 🚀</p>

    <div class="scrollable-table">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Image</th>
                    <th>Details</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($_SESSION['guide_bookings'])): ?>
                    <?php foreach ($_SESSION['guide_bookings'] as $index => $booking): ?>
                        <tr>
                            <td>Guide Booking</td>
                            <td><img src="../images/guide.png" alt="Guide" class="item-img"></td>
                            <td><?= $booking['guide_name']; ?> (<?= $booking['days']; ?> days)</td>
                            <td>₹<?= number_format($booking['total_price'], 2); ?></td>
                            <td><a href="?remove=<?= $index; ?>&type=guide" class="remove-btn">❌</a></td>
                        </tr>
                        <?php $totalPrice += $booking['total_price']; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['resort_bookings'])): ?>
                    <?php foreach ($_SESSION['resort_bookings'] as $index => $booking): ?>
                        <tr>
                            <td>Resort Stay</td>
                            <td><img src="../images/resort.png" alt="Resort" class="item-img"></td>
                            <td><?= $booking['resort_name']; ?> (<?= $booking['days']; ?> nights)</td>
                            <td>₹<?= number_format($booking['total_price'], 2); ?></td>
                            <td><a href="?remove=<?= $index; ?>&type=resort" class="remove-btn">❌</a></td>
                        </tr>
                        <?php $totalPrice += $booking['total_price']; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['restaurant_bookings'])): ?>
                    <?php foreach ($_SESSION['restaurant_bookings'] as $index => $booking): ?>
                        <tr>
                            <td>Restaurant Booking</td>
                            <td><img src="../images/restaurant.jpg" alt="Restaurant" class="item-img"></td>
                            <td>
                                <strong><?= htmlspecialchars($booking['restaurant_name']); ?></strong><br>
                                For <?= intval($booking['num_people']); ?> people<br>
                                Date: <?= htmlspecialchars($booking['booking_date']); ?>
                            </td>
                            <td>₹<?= number_format($booking['total_price'], 2); ?></td>
                            <td><a href="?remove=<?= $index; ?>&type=restaurant" class="remove-btn">❌</a></td>
                        </tr>
                        <?php $totalPrice += $booking['total_price']; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['transport_bookings'])): ?>
                    <?php foreach ($_SESSION['transport_bookings'] as $index => $booking): ?>
                        <?php
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
                        ?>
                        <tr>
                            <td>Transport</td>
                            <td><img src="../images/transport.png" alt="Transport" class="item-img"></td>
                            <td><?= htmlspecialchars($booking['mode_of_transport'] . ' - ' . $transport_info); ?></td>
                            <td>₹<?= number_format((float)$price, 2); ?></td>
                            <td><a href="?remove=<?= $index; ?>&type=transport" class="remove-btn">❌</a></td>
                        </tr>
                        <?php $totalPrice += (float)$price; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPrice > 0): ?>
        <?php 
            $platformFee = $totalPrice * 0.02; 
            $finalPrice = $totalPrice + $platformFee;
            $_SESSION['total_price'] = $finalPrice;
        ?>
        <div class="price-summary">
            <p>Subtotal: <span class="price-highlight">₹<?= number_format($totalPrice, 2); ?></span></p>
            <p>Platform Fee (2%): ₹<?= number_format($platformFee, 2); ?></p>
            <h3>Total: <span class="price-highlight">₹<?= number_format($finalPrice, 2); ?></span></h3>
            <button class="confirm-btn" onclick="window.location.href='payment.php'">✨ Confirm & Pay ✨</button>
        </div>
    <?php else: ?>
        <div class="empty-bucket">
            <p>🏠 Your bucket list is empty! Start planning your dream trip now! 🌟</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>