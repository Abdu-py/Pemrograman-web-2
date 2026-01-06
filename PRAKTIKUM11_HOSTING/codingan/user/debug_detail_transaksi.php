<?php
require_once '../config/database.php';

try {
    $conn = getConnection();
    
    // Lihat struktur tabel detail_transaksi
    echo "<h2>Struktur Tabel detail_transaksi:</h2>";
    $result = $conn->query("DESCRIBE detail_transaksi");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>