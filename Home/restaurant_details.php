<?php
// Start session and include the database connection file
session_start();
include '../db.php';

// Get the restaurant ID from the URL
if (isset($_GET['id'])) {
    $restaurant_id = mysqli_real_escape_string($conn, $_GET['id']);
} else {
    die("Restaurant ID is missing from the URL.");
}

// Fetch restaurant information
$query = "SELECT restaurants.*, destinations.name AS place_name FROM restaurants 
          JOIN destinations ON restaurants.place_id = destinations.id 
          WHERE restaurants.id='$restaurant_id'";

// Run the query
$result = mysqli_query($conn, $query);

// Check if the query was successful and returned a result
if ($result && mysqli_num_rows($result) > 0) {
    // Fetch restaurant details
    $restaurant = mysqli_fetch_assoc($result);
} else {
    // If no restaurant found, display an error message
    die("Restaurant not found or query failed.");
}

// Check if booking form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_name'], $_POST['num_people'], $_POST['booking_date'], $_POST['total_price'])) {
    
    // Create the booking details array
    $restaurantBooking = [
        'customer_name' => $_POST['customer_name'],
        'num_people' => $_POST['num_people'],
        'booking_date' => $_POST['booking_date'],
        'total_price' => $_POST['total_price'],
        'restaurant_name' => $restaurant['name']
    ];

    // Initialize the restaurant bookings array if it doesn't exist yet
    if (!isset($_SESSION['restaurant_bookings'])) {
        $_SESSION['restaurant_bookings'] = [];
    }

    // Add the new booking to the session's restaurant bookings array
    $_SESSION['restaurant_bookings'][] = $restaurantBooking;

    // Redirect back to place.php with the destination ID
    if (isset($_GET['from_place'])) {
        header("Location: place.php?id=" . $_GET['from_place']);
        exit();
    } else {
        // Fallback if from_place isn't set
        header("Location: place.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurant['name']); ?> - Details</title>
    <link rel="icon" href="../images/Logo.png" type="image/png">
    <link rel="stylesheet" href="styledetails.css">
    <link href="https://fonts.googleapis.com/css2?family=Girassol&family=Sigmar&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Girassol', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f7f7f7;
        }

        .restaurant-details {
            display: flex;
            width: 90%;
            max-width: 1200px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .restaurant-image {
            width: 50%;
            overflow: hidden;
        }

        .restaurant-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .restaurant-info {
            padding: 20px;
            width: 50%;
        }

        .restaurant-info h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .restaurant-info .place-name {
            font-size: 1rem;
            color: #666;
            margin-bottom: 10px;
        }

        .restaurant-info .cost {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 20px;
        }

        .restaurant-info .book-btn {
            padding: 10px 20px;
            background-color: #ff6347;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
        }

        .modal-content input,
        .modal-content button {
            width: 95%;
            padding: 10px;
            margin-top: 10px;
        }

        .close-btn,
        .confirm-btn {
            background-color: #ff6347;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            margin-top: 10px;
        }

        .total-price {
            font-size: 1.5rem;
            margin-top: 20px;
            color: #ff6347;
        }

        .map-link-container {
            margin-top: 20px;
        }

        .map-link-preview {
            margin-top: 10px;
            padding: 10px;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }

        .map-link-preview iframe {
            width: 100%;
            height: 200px;
        }

        .map-link-preview a {
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="restaurant-details">
    <div class="restaurant-image">
        <img src="../<?php echo htmlspecialchars($restaurant['image_url']); ?>" alt="Restaurant Image">
    </div>

    <div class="restaurant-info">
        <h1><?php echo htmlspecialchars($restaurant['name']); ?></h1>
        <p class="place-name">Located in <?php echo htmlspecialchars($restaurant['place_name']); ?></p>
        <p class="cost">Cost Per Person: ₹<span id="cost-per-person"><?php echo htmlspecialchars($restaurant['cost_per_person']); ?></span></p>

        <!-- Book Button to Open the Modal -->
        <button class="book-btn" onclick="openModal()">Book Now</button>

        <div class="map-link-container">
            <p><a href="<?php echo htmlspecialchars($restaurant['maps_link']); ?>" target="_blank">Locate on Map</a></p>
            <div class="map-link-preview">
                <?php if (!empty($restaurant['embed_map_link'])): ?>
                    <iframe src="<?php echo htmlspecialchars($restaurant['embed_map_link']); ?>"
                            style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                <?php else: ?>
                    <p>No map preview available for this restaurant.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Booking Form -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <h2>Book a Table</h2>
        <form id="bookingForm" method="POST" onsubmit="showConfirmation(event)">
            <input type="text" id="customer_name" name="customer_name" placeholder="Your Name" required>
            <input type="number" id="num_people" name="num_people" placeholder="Number of People" required>
            <input type="date" id="booking_date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
            <input type="hidden" id="total_price" name="total_price">
            <button type="submit">Confirm Booking</button>
        </form>
        <button class="close-btn" onclick="closeModal()">Close</button>
    </div>
</div>

<!-- Modal for Booking Confirmation -->
<div id="confirmationModal" class="modal">
    <div class="modal-content">
        <h2>Confirm Your Booking</h2>
        <p id="confirm-name"></p>
        <p id="confirm-people"></p>
        <p id="confirm-date"></p>
        <p class="total-price" id="confirm-price"></p>
        <button class="confirm-btn" onclick="submitBooking()">Confirm and Book</button>
        <button class="close-btn" onclick="closeAllModals()">Close</button>
    </div>
</div>

<script>
    // JavaScript to open and close the modal
    function openModal() {
        document.getElementById('bookingModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('bookingModal').style.display = 'none';
    }

    function closeConfirmation() {
        document.getElementById('confirmationModal').style.display = 'none';
    }

    function closeAllModals() {
        closeModal();
        closeConfirmation();
    }

   // Function to submit the booking form
function submitBooking() {
    document.getElementById('bookingForm').submit();
}

// Function to display the confirmation screen
function showConfirmation(event) {
    event.preventDefault();
    
    const name = document.getElementById('customer_name').value;
    const numPeople = document.getElementById('num_people').value;
    const bookingDate = document.getElementById('booking_date').value;
    const costPerPerson = document.getElementById('cost-per-person').textContent;

    const totalCost = numPeople * costPerPerson;

    document.getElementById('confirm-name').textContent = "Name: " + name;
    document.getElementById('confirm-people').textContent = "Number of People: " + numPeople;
    document.getElementById('confirm-date').textContent = "Booking Date: " + bookingDate;
    document.getElementById('confirm-price').textContent = "Total Price: ₹" + totalCost;
    document.getElementById('total_price').value = totalCost;

    closeModal();
    document.getElementById('confirmationModal').style.display = 'flex';
}

    // Close modals when clicking outside the content
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            closeAllModals();
        }
    }
</script>

</body>
</html>