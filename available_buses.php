<?php
session_start();
include('db.php');
include('header.php');

// Get search parameters from URL
$departure = isset($_GET['departure']) ? $_GET['departure'] : '';
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$travel_date = isset($_GET['date']) ? $_GET['date'] : '';
$bus_type = isset($_GET['bus_type']) ? $_GET['bus_type'] : '';

// Initialize an empty array for available buses
$available_buses = [];

// Validate input: Ensure that all parameters are provided
if (!empty($departure) && !empty($destination) && !empty($travel_date) && !empty($bus_type)) {
    
    // Prepare the SQL query
    $stmt = $conn->prepare("
        SELECT s.id AS schedule_id, b.bus_name, s.route_from, s.route_to, 
               s.travel_date, s.departure_time, s.arrival_time, 
               b.bus_type, s.price, s.available_seats
        FROM schedules s
        JOIN buses b ON s.bus_id = b.id
        WHERE s.route_from = ? 
        AND s.route_to = ? 
        AND s.travel_date = ? 
        AND b.bus_type = ?
        AND s.available_seats > 0
    ");
    
    // Bind the parameters to the query
    $stmt->bind_param("ssss", $departure, $destination, $travel_date, $bus_type);
    $stmt->execute();
    $result = $stmt->get_result();

    // If buses are found, fetch the results
    if ($result->num_rows > 0) {
        $available_buses = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Close the statement
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Buses</title>
    <style>
        /* Internal CSS */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin-top: 20px;
        }

        .bus-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        .bus-card {
            width: 31%; /* 3 cards in a row, with space between them */
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: all 0.3s ease;
        }

        .bus-card:hover {
            transform: scale(1.05);
        }

        .bus-card h5 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .bus-card p {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .bus-card i {
            margin-right: 8px;
            color: #007bff;
        }

        .bus-card .price {
            font-weight: bold;
            color: #28a745;
        }

        .bus-card .available-seats {
            font-weight: bold;
            color: #dc3545;
        }

        .book-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .book-btn:hover {
            background-color: #0056b3;
        }

        @media (max-width: 767px) {
            .bus-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h3 class="text-center section-title">Available Buses</h3>

    <?php if (!empty($available_buses)): ?>
        <div class="bus-list">
            <?php foreach ($available_buses as $bus): ?>
                <div class="bus-card">
                    <h5><?php echo htmlspecialchars($bus['bus_name']); ?></h5>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($bus['route_from']); ?> → <?php echo htmlspecialchars($bus['route_to']); ?></p>
                    <p><i class="fas fa-calendar-alt"></i> Date: <?php echo htmlspecialchars($bus['travel_date']); ?></p>
                    <p><i class="fas fa-clock"></i> Departure: <?php echo htmlspecialchars($bus['departure_time']); ?></p>
                    <p><i class="fas fa-clock"></i> Arrival: <?php echo htmlspecialchars($bus['arrival_time']); ?></p>
                    <p><i class="fas fa-rupee-sign"></i> Price: ₹<?php echo htmlspecialchars($bus['price']); ?></p>
                    <p><i class="fas fa-chair"></i> <span class="available-seats">Available Seats: <?php echo htmlspecialchars($bus['available_seats']); ?></span></p>
                    <a href="my_seat.php?schedule_id=<?php echo $bus['schedule_id']; ?>" class="book-btn">Book Seat</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center mt-5">
            <h4 class="text-danger"><i class="fas fa-exclamation-circle"></i> No Buses Available</h4>
            <p>Please try a different search.</p>
        </div>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>

</body>
</html>
