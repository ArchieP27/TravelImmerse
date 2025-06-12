<?php
session_start();
include '../db.php'; 


if (isset($_GET['id'])) {
    $destination_id = mysqli_real_escape_string($conn, $_GET['id']);
} else {
    die("Destination ID is missing.");
}

$query = "SELECT * FROM destinations WHERE id='$destination_id'";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $destination = mysqli_fetch_assoc($result);
} else {
    die("Destination not found.");
}

$queryDetails = "SELECT * FROM destination_details WHERE destination_id='$destination_id'";
$detailsResult = mysqli_query($conn, $queryDetails);
$details = mysqli_fetch_assoc($detailsResult);


$queryTravelOptions = "SELECT * FROM destination_travel_options WHERE destination_id = '$destination_id'";
$travelOptionsResult = mysqli_query($conn, $queryTravelOptions);

$travelOptions = [];
while ($row = mysqli_fetch_assoc($travelOptionsResult)) {
    $travelOptions[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);

        $review_image = '';
        if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] === UPLOAD_ERR_OK) {
            $image_name = basename($_FILES['review_image']['name']);
            $image_path = "../images/review/" . $image_name;

            if (move_uploaded_file($_FILES['review_image']['tmp_name'], $image_path)) {
                $review_image = "images/review/" . $image_name; // Store relative path
            } else {
                echo "Error uploading image!";
            }
        }

        $query = "INSERT INTO reviews (user_id, destination_id, review_text, review_image) 
                  VALUES ('$user_id', '$destination_id', '$review_text', '$review_image')";
        if (mysqli_query($conn, $query)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$destination_id");
            exit();
        } else {
            if($_SESSION['user_id']==='guest'){
                echo "<script>alert('You must be signed in to leave a review');</script>";
            }
            else
                echo "Error submitting review!";
        }
    } else {
        echo "You must be logged in to submit a review.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_guide'])) {
    $guide_id = intval($_POST['guide_id']);
    $guide_name = htmlspecialchars($_POST['guide_name']);
    $start_date = htmlspecialchars($_POST['start_date']);
    $days = intval($_POST['days']);
    $price_per_day = floatval($_POST['price_per_day']);
    $total_price = $days * $price_per_day;

    if (!isset($_SESSION['guide_bookings'])) {
        $_SESSION['guide_bookings'] = [];
    }

    $_SESSION['guide_bookings'][] = [
        'guide_id' => $guide_id,
        'guide_name' => $guide_name,
        'start_date' => $start_date,
        'days' => $days,
        'price_per_day' => $price_per_day,
        'total_price' => $total_price
    ];

    echo "<script>alert('Booking confirmed for guide: {$guide_name}\\nDuration: {$days} days\\nTotal price: \Rs.{$total_price}');</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_resort'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $days = intval($_POST['days']);
    $check_in_date = mysqli_real_escape_string($conn, $_POST['check_in_date']);
    $resort_name = mysqli_real_escape_string($conn, $_POST['resort_name']);
    $per_night_price = floatval($_POST['per_night_price']);
    $total_price = $days * $per_night_price;

    if (!isset($_SESSION['resort_bookings'])) {
        $_SESSION['resort_bookings'] = [];
    }

    $booking = [
        'customer_name' => $customer_name,
        'days' => $days,
        'check_in_date' => $check_in_date,
        'resort_name' => $resort_name,
        'per_night_price' => $per_night_price,
        'total_price' => $total_price,
        'booking_date' => date('Y-m-d H:i:s')
    ];

    $_SESSION['resort_bookings'][] = $booking;

    $_SESSION['booking_success'] = true;
    header("Location: ".$_SERVER['PHP_SELF']."?id=".$destination_id);
    exit();
}

$show_booking_confirmation = false;
$last_booking = [];
if (isset($_SESSION['booking_success']) && $_SESSION['booking_success']) {
    $show_booking_confirmation = true;
    if (!empty($_SESSION['resort_bookings'])) {
        $last_booking = end($_SESSION['resort_bookings']);
    }
    unset($_SESSION['booking_success']); 
}

$queryReviews = "SELECT reviews.*, users.username FROM reviews 
                 JOIN users ON reviews.user_id = users.id 
                 WHERE destination_id = '$destination_id' 
                 ORDER BY reviews.created_at DESC";
$reviewsResult = mysqli_query($conn, $queryReviews);

$queryGuides = "SELECT * FROM tourist_guides WHERE destination_id = '$destination_id'";
$guidesResult = mysqli_query($conn, $queryGuides);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore - <?php echo htmlspecialchars($destination['name']); ?></title>
    <link rel="icon" href="../images/Logo.png" type="image/png">
    <link rel="stylesheet" href="styleplace.css">
    <link rel="stylesheet" href="stylehome.css">
    <link rel="stylesheet" href="footerdes.css">
    <link href="https://fonts.googleapis.com/css2?family=Girassol&family=Sigmar&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'header.php'; ?>

<div class="main">
    <section class="place-overview">
        <h1>Explore <span id="place-name"><?php echo htmlspecialchars($destination['name']); ?></span></h1>
        <p><?php echo htmlspecialchars($destination['description']); ?></p>
    </section>

    <section class="culture-box">
        <h2>Cultural Practices</h2>
        <p><?php echo htmlspecialchars($details['culture_practices']); ?></p>
    </section>

    <section class="gallery-section">
        <h2>Image Gallery</h2>
        <div class="gallery-scroll">
            <?php foreach (explode(',', $details['image_gallery']) as $image): ?>
                <img src="../<?php echo htmlspecialchars($image); ?>" alt="Place Image">
            <?php endforeach; ?>
        </div>
    </section>

    <section class="activities-section">
        <h2>Things to Do</h2>
        <table class="things-to-do-table">
            <tr>
                <?php 
                $activities = explode(',', $details['things_to_do']);
                $count = 0;
                foreach ($activities as $activity): 
                    if ($count % 3 == 0 && $count != 0) echo '</tr><tr>';
                    ?>
                    <td><?php echo htmlspecialchars($activity); ?></td>
                    <?php
                    $count++;
                endforeach; 
                ?>
            </tr>
        </table>
    </section>


    <section class="tourist-spots">
        <h2>Famous Tourist Spots</h2>
        <div class="tourist-spot-scroll">
            <?php 
            $touristSpots = explode(',', $details['tourist_spots']);
            foreach ($touristSpots as $spot): 
                $spot = trim($spot);

                $querySpot = "SELECT * FROM tourist_spots WHERE spot_name = '$spot'";
                $spotResult = mysqli_query($conn, $querySpot);
                if ($spotResult && mysqli_num_rows($spotResult) > 0) {
                    $spotDetails = mysqli_fetch_assoc($spotResult);
            ?>
                <div class="tourist-item">
                    <img src="../<?php echo htmlspecialchars($spotDetails['image_url']); ?>" alt="<?php echo htmlspecialchars($spotDetails['spot_name']); ?>">
                    <h3><?php echo htmlspecialchars($spotDetails['spot_name']); ?></h3>
                    <p><?php echo htmlspecialchars($spotDetails['description']); ?></p>
                </div>
            <?php 
                }
            endforeach; 
            ?>
        </div>
    </section>


<section class="resorts-section">
        <h2>Resorts</h2>
        
        <?php if ($show_booking_confirmation && !empty($last_booking)): ?>
        <div class="booking-confirmation" style="background: #dff0d8; padding: 15px; margin: 20px 0; border: 1px solid #d6e9c6; border-radius: 4px;">
            <h3 style="color: #3c763d; margin-top: 0;">Booking Confirmed!</h3>
            <p><strong>Resort Name:</strong> <?php echo htmlspecialchars($last_booking['resort_name']); ?></p>
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($last_booking['customer_name']); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($last_booking['check_in_date']); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($last_booking['days']); ?> days</p>
            <p><strong>Total Price:</strong> Rs <?php echo number_format($last_booking['total_price'], 2); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="resort-list-scroll">
            <?php
            $queryResorts = "SELECT * FROM resorts WHERE place_id = (SELECT id FROM destination_details WHERE destination_id = '$destination_id')";
            $resortsResult = mysqli_query($conn, $queryResorts);

            if ($resortsResult && mysqli_num_rows($resortsResult) > 0) {
                while ($resort = mysqli_fetch_assoc($resortsResult)) {
            ?>
            <div class="resort-item-box">
                <div class="resort-item">
                    <img src="../<?php echo htmlspecialchars($resort['image_url']); ?>" alt="<?php echo htmlspecialchars($resort['name']); ?>">
                    <div class="resort-info">
                        <h3><?php echo htmlspecialchars($resort['name']); ?></h3>
                        <a href="<?php echo htmlspecialchars($resort['google_map_link']); ?>" target="_blank" style="color: #D35400;">
                            <p style="color: #D35400;">Location: <?php echo htmlspecialchars($resort['place']); ?></p>
                        </a>
                        <p>Contact: +91 <?php echo htmlspecialchars($resort['contact']); ?></p>
                        <p>Charges per night: Rs <?php echo number_format($resort['per_night_charges'], 2); ?></p>
                    </div>
                    <button class="book-btn" onclick="openBookingForm('<?php echo htmlspecialchars($resort['name']); ?>', <?php echo $resort['per_night_charges']; ?>)">
                        Book Now
                    </button>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<p>No resorts available for this destination.</p>";
            }
            ?>
        </div>
    </section>

    <div id="booking-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeBookingForm()">&times;</span>
            <h2>Book Resort</h2>
            <form id="booking-form" method="post" action="">
                <input type="hidden" name="resort_name" id="resort_name">
                <input type="hidden" name="per_night_price" id="per_night_price">
                <input type="hidden" name="book_resort" value="1">
                
                <div class="form-group">
                    <label for="customer_name">Your Name:</label>
                    <input type="text" id="customer_name" name="customer_name" required>
                </div>
                <div class="form-group">
                    <label for="days">Number of Days:</label>
                    <input type="number" id="days" name="days" required min="1" onchange="calculatePrice()">
                </div>
                <div class="form-group">
                    <label for="date">Check-in Date:</label>
                    <input type="date" id="date" name="check_in_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Total Price:</label>
                    <p id="total_price">Rs 0.00</p>
                </div>
                <button type="submit" class="book-btn">Confirm Booking</button>
            </form>
        </div>
    </div>


<?php include "footer.php"; ?>

<script>
    function openBookingForm(resortName, perNightPrice) {
        document.getElementById('resort_name').value = resortName;
        document.getElementById('per_night_price').value = perNightPrice;
        document.getElementById('total_price').innerText = "Rs " + perNightPrice.toFixed(2);
        document.getElementById('days').value = 1;
        
        // Set minimum date to today
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('date').setAttribute('min', today);
        
        document.getElementById('booking-modal').style.display = 'flex'; 
    }

    function closeBookingForm() {
        document.getElementById('booking-modal').style.display = 'none';
    }

    function calculatePrice() {
        var days = document.getElementById('days').value;
        var perNightPrice = document.getElementById('per_night_price').value;
        var totalPrice = days * perNightPrice;
        document.getElementById('total_price').innerText = "Rs " + totalPrice.toFixed(2);
    }
</script>


<?php if (isset($booking_confirmed) && $booking_confirmed): ?>
    <div class="booking-confirmation">
        <h3>Booking Confirmed!</h3>
        <p>Resort Name: <?php echo htmlspecialchars($_SESSION['booking']['resort_name']); ?></p>
        <p>Customer Name: <?php echo htmlspecialchars($_SESSION['booking']['customer_name']); ?></p>
        <p>Number of Days: <?php echo htmlspecialchars($_SESSION['booking']['days']); ?></p>
        <p>Check-in Date: <?php echo htmlspecialchars($_SESSION['booking']['check_in_date']); ?></p>
        <p>Total Price: $<?php echo number_format($_SESSION['booking']['total_price'], 2); ?></p>
    </div>
<?php endif; ?>

    <section class="restaurants-section">
        <h2>Famous Restaurants</h2>
        <div class="restaurant-list-scroll">
            <?php

            $place_id = $destination['id'];

            $queryRestaurants = "SELECT * FROM restaurants WHERE place_id = '$place_id'";
            $restaurantsResult = mysqli_query($conn, $queryRestaurants);

            if ($restaurantsResult && mysqli_num_rows($restaurantsResult) > 0) {
                while ($restaurant = mysqli_fetch_assoc($restaurantsResult)):
            ?>
            <a href="restaurant_details.php?id=<?php echo $restaurant['id']; ?>&from_place=<?php echo $destination_id; ?>" style="text-decoration: none;">
                <div class="restaurant-item">
                    <img src="../<?php echo htmlspecialchars($restaurant['image_url']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>">
                    <div class="restaurant-info">
                        <h3><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                        <p>Famous Dish: <?php echo htmlspecialchars($restaurant['famous_dish']); ?></p>
                        <p>Seasonal Offer: <?php echo htmlspecialchars($restaurant['seasonal_offer']); ?></p>
                    </div>
                </div>
            </a>
            <?php
                endwhile;
            } else {
                echo "<p>No restaurants available for this destination.</p>";
            }
            ?>
        </div>
    </section>

  
<section class="how-to-reach-section">
    <h2>How to Reach</h2>
    <div class="how-to-reach-options">
        <?php
        if (count($travelOptions) > 0) {
            foreach ($travelOptions as $option) {
                echo '<div class="option">';
                echo '<h3>' . htmlspecialchars($option['title']) . '</h3>';
                echo '<p>' . htmlspecialchars($option['description']) . '</p>';
        
                echo '<a href="book_transport.php?mode_of_transport=' . urlencode($option['mode_of_transport']) . '&destination_id=' . urlencode($destination_id) . '">';
                echo '<button class="book-btn">Book ' . htmlspecialchars($option['mode_of_transport']) . '</button>';
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo "<p>No travel options available for this destination.</p>";
        }
        ?>
    </div>
</section>

    <?php
    $queryGuides = "SELECT * FROM tourist_guides WHERE destination_id = '$destination_id'";
    $guidesResult = mysqli_query($conn, $queryGuides);
    ?>

<section class="tourist-guides-section">
    <h2>Tourist Guides</h2>
    <div class="guide-list-scroll">
        <?php 
        if (mysqli_num_rows($guidesResult) > 0) {
            while ($guide = mysqli_fetch_assoc($guidesResult)) {
                ?>
                <div class="guide-item">
                    <div class="guide-image">
                        <img src="../images/<?php echo htmlspecialchars($guide['guide_image']); ?>" alt="<?php echo htmlspecialchars($guide['guide_name']); ?>">
                    </div>
                    <div class="guide-info">
                        <h3><?php echo htmlspecialchars($guide['guide_name']); ?></h3>
                        <p>Experience: <?php echo htmlspecialchars($guide['experience']); ?> years</p>
                        <p>Rating: <img src="../images/star.png" alt="Star" class="star-icon"> <?php echo htmlspecialchars($guide['rating']); ?></p>
                        <p>People guided: <?php echo htmlspecialchars($guide['people_guided']); ?></p>
                        <p class="price-per-day">Price per day: Rs. <?php echo htmlspecialchars($guide['price_per_day']); ?></p>

                        <form method="POST" action="" class="guide-booking-form">
                            <div class="form-group">
                                <label for="start_date">Start Date:</label>
                                <input type="date" name="start_date" required class="form-input" min="<?php echo date('Y-m-d'); ?>">

                            </div>
                            <div class="form-group">
                                <label for="days">Number of days:</label>
                                <input type="number" name="days" min="1" value="1" required class="form-input">
                            </div>
                            <input type="hidden" name="guide_id" value="<?php echo $guide['id']; ?>">
                            <input type="hidden" name="guide_name" value="<?php echo htmlspecialchars($guide['guide_name']); ?>">
                            <input type="hidden" name="price_per_day" value="<?php echo htmlspecialchars($guide['price_per_day']); ?>">

                            <button type="submit" name="book_guide" class="book-guide-btn">Book Guide</button>
                        </form>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No tourist guides available for this destination.</p>";
        }
        ?>
    </div>
</section>

<section class="review-section bordered-review">
        <h2>Reviews & Experiences</h2>
        <div class="review-post">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="file" name="review_image" accept="image/*" required>
                <textarea name="review_text" placeholder="Share your experience..." required></textarea>
                <button type="submit" class="post-review-btn">Post Review</button>
            </form>
        </div>

        <div class="user-reviews">
            <?php if (mysqli_num_rows($reviewsResult) > 0): ?>
                <?php while ($review = mysqli_fetch_assoc($reviewsResult)): ?>
                    <div class="user-review-item">
                        <?php if (!empty($review['review_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($review['review_image']); ?>" alt="User Review Image" class="review-image">
                        <?php endif; ?>
                        <p>"<?php echo nl2br(htmlspecialchars($review['review_text'])); ?>" - <?php echo htmlspecialchars($review['username']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews yet. Be the first to leave a review!</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include "footer.php"; ?>

</body>
</html>

<?php
mysqli_close($conn);
?>