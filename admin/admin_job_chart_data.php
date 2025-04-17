<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");

$sql = "SELECT MONTHNAME(posted_on) AS month, COUNT(*) AS total 
        FROM jobs 
        GROUP BY MONTH(posted_on), MONTHNAME(posted_on) 
        ORDER BY MONTH(posted_on)";
$result = $conn->query($sql);

$labels = [];
$counts = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['month'];
    $counts[] = $row['total'];
}

echo json_encode(['labels' => $labels, 'counts' => $counts]);
?>
