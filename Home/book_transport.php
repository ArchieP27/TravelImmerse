<?php
// Start session and include the database connection
session_start();
include '../db.php';

// Get the mode of transport and destination ID from the URL parameters
if (isset($_GET['mode_of_transport']) && isset($_GET['destination_id'])) {
    $mode_of_transport = mysqli_real_escape_string($conn, $_GET['mode_of_transport']);
    $destination_id = mysqli_real_escape_string($conn, $_GET['destination_id']);
} else {
    die("Transport mode or destination ID is missing.");
}

// Define some random transport options based on the mode of transport
$transportOptions = [];
if ($mode_of_transport === 'Air') {
    $transportOptions = [
        ['Flight Number' => 'AI 123', 'Departure' => '10:00 AM', 'Arrival' => '12:00 PM', 'Price' => 6120.50],
        ['Flight Number' => 'BA 456', 'Departure' => '02:00 PM', 'Arrival' => '04:00 PM', 'Price' => 5140.00],
        ['Flight Number' => 'QR 789', 'Departure' => '06:00 PM', 'Arrival' => '08:00 PM', 'Price' => 12160.75],
    ];
} elseif ($mode_of_transport === 'Train') {
    $transportOptions = [
        ['Train Number' => '12345', 'Departure' => '07:00 AM', 'Arrival' => '10:00 AM', 'Price' => 350.00],
        ['Train Number' => '67890', 'Departure' => '11:00 AM', 'Arrival' => '02:00 PM', 'Price' => 1055.00],
        ['Train Number' => '11223', 'Departure' => '03:00 PM', 'Arrival' => '06:00 PM', 'Price' => 660.00],
    ];
} elseif ($mode_of_transport === 'Bus/Car') {
    $transportOptions = [
        ['Bus Number' => 'BUS 101', 'Departure' => '09:00 AM', 'Arrival' => '11:30 AM', 'Price' => 1125.00],
        ['Bus Number' => 'BUS 202', 'Departure' => '01:00 PM', 'Arrival' => '03:30 PM', 'Price' => 2330.00],
        ['Bus Number' => 'BUS 303', 'Departure' => '05:00 PM', 'Arrival' => '07:30 PM', 'Price' => 3435.00],
        ['Car Model' => 'Toyota Camry', 'Availability' => 'Available', 'Price per Day' => 1180.00],
        ['Car Model' => 'Honda Civic', 'Availability' => 'Available', 'Price per Day' => 2375.00],
        ['Car Model' => 'Ford Focus', 'Availability' => 'Available', 'Price per Day' => 4570.00],
    ];
} else {
    die("Invalid mode of transport.");
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transport_option'])) {
    $transport_option = htmlspecialchars($_POST['transport_option']);
    $customer_name = htmlspecialchars($_POST['customer_name']);

    // Initialize transport bookings array if not set
    if (!isset($_SESSION['transport_bookings'])) {
        $_SESSION['transport_bookings'] = [];
    }

    // Store booking details in the session
    $_SESSION['transport_bookings'][] = [
        'customer_name' => $customer_name,
        'mode_of_transport' => $mode_of_transport,
        'transport_option' => $transport_option
    ];

    // Output the modal HTML
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('bookingModal').style.display = 'block';
        });
    </script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Transport - <?php echo ucfirst($mode_of_transport); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Girassol&family=Sigmar&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="stylehome.css">
    <link rel="stylesheet" href="footerdes.css">
    <link rel="icon" href="../images/Logo.png" type="image/png">
    <style>
    /* Define button styles and hover effect */
    .confirm-booking-btn {
        color: white;
        background-color: #D35400;
        padding: 10px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
    }

    /* Hover effect */
    .confirm-booking-btn:hover {
        background-color: rgb(201, 106, 43); /* Darker orange on hover */
    }

     /* Modal background */
     #bookingModal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Black background with opacity */
        }

        /* Modal content */
        #modalContent {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        /* Button styling */
        .modal-button {
            padding: 10px 20px;
            background-color: #D35400;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-button:hover {
            background-color: rgb(201, 106, 43);
        }

</style>

</head>
<body style="background-color:  rgb(249, 218, 197;">
<?php include "header.php"; ?>

<div class="container mx-auto py-12">
    <div class="bg-white shadow-md rounded-lg p-8">
        <h2 class="text-3xl font-bold text-center mb-8 text-orange-600">Book Your <?php echo ucfirst($mode_of_transport); ?></h2>
        
        <form method="post" action="" class="space-y-6">
            <div>
                <label for="customer_name" class="block text-lg font-medium text-gray-700">Customer Name</label>
                <input type="text" id="customer_name" name="customer_name" required class="mt-1 p-3 w-full border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div>
                <label for="transport_option" class="block text-lg font-medium text-gray-700">Choose a <?php echo ucfirst($mode_of_transport); ?></label>
                <select id="transport_option" name="transport_option" required class="mt-1 p-3 w-full border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <?php
                    // Loop through transport options and display them as dropdown options
                    foreach ($transportOptions as $option) {
                        $optionDetails = '';
                        foreach ($option as $key => $value) {
                            $optionDetails .= "$key: $value, ";
                        }
                        // Trim the trailing comma and space
                        $optionDetails = rtrim($optionDetails, ', ');
                        echo '<option value="' . htmlspecialchars($optionDetails) . '">' . htmlspecialchars($optionDetails) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="text-center">
                <!-- Booking Button Color Fix -->
                <button type="submit" class="confirm-booking-btn">Confirm Booking</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal HTML -->
<div id="bookingModal">
    <div id="modalContent">
        <h2 class="text-xl font-bold mb-4">Booking Confirmed!</h2>
        <p class="mb-4">Your booking for <?php echo $transport_option; ?> has been confirmed.</p>
        <button onclick="goBack()" class="modal-button">Go Back</button>
    </div>
</div>

<script>
function goBack() {
    // Get the destination ID from PHP variable
    const destinationId = "<?php echo $destination_id; ?>";
    
    // Redirect back to the place.php page with the correct destination_id
    window.location.href = "place.php?id=" + destinationId;
}
</script>

<?php include "footer.php"; ?>
</body>
</html>