<?php


echo "NIM : 312310576";
echo "<br>";
echo "NAMA : TAUFIK HIDAYAT";
?>
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Toko Online</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .struk {
            border: 1px solid #ddd;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Sistem Toko Online</h1>
    
    <?php
    require_once 'config.php';
    
    // Display products
    $stmt = $pdo->query("SELECT * FROM product");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <h2>Daftar Produk</h2>
    <table>
        <tr>
            <th>ID Produk</th>
            <th>Nama Produk</th>
            <th>Harga (Rp)</th>
            <th>Stok</th>
        </tr>
        <?php foreach($products as $product): ?>
        <tr>
            <td><?= htmlspecialchars($product['id']) ?></td>
            <td><?= htmlspecialchars($product['nama']) ?></td>
            <td><?= number_format($product['harga'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($product['stok']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Form Transaksi</h2>
    <form method="POST">
        <div class="form-group">
            <label>ID Produk:</label>
            <input type="text" name="product_id" required>
        </div>
        <div class="form-group">
            <label>Jumlah:</label>
            <input type="number" name="quantity" required>
        </div>
        <button type="submit">Proses Transaksi</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_id = strtoupper($_POST['product_id']);
        $quantity = (int)$_POST['quantity'];

        // Get product details
        $stmt = $pdo->prepare("SELECT * FROM product WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo "<p style='color: red;'>Produk tidak ditemukan!</p>";
            exit;
        }

        // Validate stock
        if ($quantity > $product['stok']) {
            echo "<p style='color: red;'>Maaf, stok untuk {$product['nama']} tidak mencukupi.</p>";
            exit;
        }

        // Calculate total
        $total = $product['harga'] * $quantity;

        // Calculate discount
        $discount = 0;
        if ($total > 500000) {
            $discount = $total * 0.10;
        } elseif ($total > 250000) {
            $discount = $total * 0.05;
        }

        // Calculate tax
        $afterDiscount = $total - $discount;
        $tax = $afterDiscount * 0.10;

        // Calculate final total
        $finalTotal = $afterDiscount + $tax;

        // Update stock
        $newStock = $product['stok'] - $quantity;
        $stmt = $pdo->prepare("UPDATE product SET stok = ? WHERE id = ?");
        $stmt->execute([$newStock, $product_id]);

        // Display receipt
        echo "<div class='struk'>";
        echo "=============================================<br>";
        echo "STRUK TRANSAKSI<br>";
        echo "=============================================<br>";
        echo "NAMA PRODUK: " . $product['nama'] . "<br>";
        echo "JUMLAH: " . $quantity . "<br>";
        echo "TOTAL HARGA: Rp" . number_format($total, 0, ',', '.') . "<br>";
        echo "DISKON: Rp" . number_format($discount, 0, ',', '.') . "<br>";
        echo "PAJAK: " . number_format($tax, 0, ',', '.') . "<br>";
        echo "---------------------------------------------<br>";
        echo "TOTAL YANG HARUS DIBAYAR: Rp" . number_format($finalTotal, 0, ',', '.') . "<br>";
        echo "=============================================";
        echo "</div>";
    }
    ?>
</body>
</html>