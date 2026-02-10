<?php
$servername='localhost';$username='root';$password='';$dbname='gestio_explotacio';$socket='/opt/lampp/var/mysql/mysql.sock';
$conn=new mysqli($servername,$username,$password,$dbname, 3306, $socket);
if($conn->connect_error){die('Error de connexió: '.$conn->connect_error);}

// Add columns to productes
$sqls = [
    "ALTER TABLE productes ADD COLUMN stock_actual DECIMAL(10,2) DEFAULT 0.00",
    "ALTER TABLE productes ADD COLUMN unitat_stock VARCHAR(20) DEFAULT 'kg'",
    "ALTER TABLE productes ADD COLUMN preu_unitari DECIMAL(10,2) DEFAULT 0.00"
];

foreach ($sqls as $sql) {
    try {
        if ($conn->query($sql) === TRUE) {
            echo "Successfully executed: $sql<br>";
        } else {
            // Check if error is "Duplicate column name"
            if ($conn->errno == 1060) {
                echo "Column already exists (skipped): $sql<br>";
            } else {
                echo "Error executing $sql: " . $conn->error . "<br>";
            }
        }
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "<br>";
    }
}

echo "Migration completed.";
?>
