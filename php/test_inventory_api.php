<?php
// Test 1: List Products
echo "--- LIST PRODUCTS ---\n";
$json = file_get_contents('http://localhost/gestio-explotacio/gestio-explotacio/php/get_inventari_productes.php');
$products = json_decode($json, true);
print_r($products);

if (count($products) > 0) {
    $id = $products[0]['id_producte'];
    echo "\n--- UPDATE STOCK for ID $id ---\n";
    
    // Test 2: Update Stock
    $data = ['id_producte' => $id, 'stock' => 123.45, 'price' => 9.99];
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents('http://localhost/gestio-explotacio/gestio-explotacio/php/save_stock_producte.php', false, $context);
    echo "Update Result: " . $result . "\n";
    
    // Test 3: Verify Update
    echo "\n--- VERIFY UPDATE ---\n";
    $json2 = file_get_contents('http://localhost/gestio-explotacio/gestio-explotacio/php/get_inventari_productes.php');
    $products2 = json_decode($json2, true);
    foreach ($products2 as $p) {
        if ($p['id_producte'] == $id) {
            echo "Stock: " . $p['stock_actual'] . " (Expected: 123.45)\n";
            echo "Price: " . $p['preu_unitari'] . " (Expected: 9.99)\n";
        }
    }
}

// Test 4: List Harvests
echo "\n--- LIST FRUIT STOCK ---\n";
$json3 = file_get_contents('http://localhost/gestio-explotacio/gestio-explotacio/php/get_inventari_collites.php');
$fruits = json_decode($json3, true);
print_r($fruits);
?>
