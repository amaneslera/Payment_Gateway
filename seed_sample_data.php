<?php
// Sample data seeder for testing the sales dashboard
// This script adds some sample data to test the sales functionality

require_once __DIR__ . '/src/config/db.php';

try {
    // First, let's add some more categories
    echo "Adding sample categories...\n";
    
    $categories = [
        ['Food', 'Food items and snacks'],
        ['Electronics', 'Electronic devices and accessories'],
        ['Clothing', 'Apparel and fashion items'],
        ['Books', 'Books and educational materials']
    ];
    
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (category_name, description) VALUES (?, ?)");
        $stmt->execute($cat);
    }
    
    // Add some more products
    echo "Adding sample products...\n";
    
    $products = [
        ['Sandwich', 2, 45.00, 'Ham and cheese sandwich', 50, 25.00, '102', '102', 1, '2025-05-25'],
        ['Chips', 2, 15.00, 'Potato chips', 100, 8.00, '103', '103', 1, '2025-12-31'],
        ['Smartphone', 3, 15000.00, 'Android smartphone', 20, 12000.00, '201', '201', 0, null],
        ['T-shirt', 4, 350.00, 'Cotton t-shirt', 30, 200.00, '301', '301', 0, null],
        ['Notebook', 5, 120.00, 'Spiral notebook', 40, 80.00, '401', '401', 0, null]
    ];
    
    foreach ($products as $product) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO products (name, category_id, price, description, stock_quantity, cost_price, barcode, sku, is_food, expiry_date, min_stock_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 5)");
        $stmt->execute($product);
    }
    
    // Add some sample orders (only if there are no existing orders today)
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = ?");
    $stmt->execute([$today]);
    $todayOrders = $stmt->fetch()['count'];
    
    if ($todayOrders == 0) {
        echo "Adding sample orders for today...\n";
        
        // Get all available products
        $stmt = $pdo->query("SELECT product_id, price FROM products WHERE stock_quantity > 0");
        $availableProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($availableProducts) > 0) {
            // Create some sample orders for today
            for ($i = 0; $i < 10; $i++) {
                // Create order
                $orderTotal = 0;
                $orderItems = [];
                
                // Add 1-3 random products to each order
                $itemCount = rand(1, 3);
                $selectedProducts = array_rand($availableProducts, min($itemCount, count($availableProducts)));
                if (!is_array($selectedProducts)) {
                    $selectedProducts = [$selectedProducts];
                }
                
                foreach ($selectedProducts as $productIndex) {
                    $product = $availableProducts[$productIndex];
                    $quantity = rand(1, 3);
                    $subtotal = $product['price'] * $quantity;
                    $orderTotal += $subtotal;
                    
                    $orderItems[] = [
                        'product_id' => $product['product_id'],
                        'quantity' => $quantity,
                        'subtotal' => $subtotal
                    ];
                }
                
                // Insert order
                $orderTime = date('Y-m-d H:i:s', strtotime($today . ' ' . rand(8, 20) . ':' . rand(0, 59) . ':' . rand(0, 59)));
                $stmt = $pdo->prepare("INSERT INTO orders (order_date, total_amount, payment_status, user_id) VALUES (?, ?, 'Paid', 114)");
                $stmt->execute([$orderTime, $orderTotal]);
                $orderId = $pdo->lastInsertId();
                
                // Insert order items
                foreach ($orderItems as $item) {
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['subtotal']]);
                }
                
                // Insert payment
                $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, cash_received, change_amount, payment_time, cashier_id) VALUES (?, 'Cash', ?, 0.00, ?, 114)");
                $stmt->execute([$orderId, $orderTotal, $orderTime]);
                
                echo "Created order $orderId with total $orderTotal\n";
            }
        }
    }
    
    // Add some orders for the past week
    for ($day = 1; $day <= 7; $day++) {
        $orderDate = date('Y-m-d', strtotime("-$day days"));
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = ?");
        $stmt->execute([$orderDate]);
        $dayOrders = $stmt->fetch()['count'];
        
        if ($dayOrders == 0) {
            echo "Adding sample orders for $orderDate...\n";
            
            // Create 3-8 orders for each past day
            $dailyOrderCount = rand(3, 8);
            
            for ($i = 0; $i < $dailyOrderCount; $i++) {
                $orderTotal = 0;
                $orderItems = [];
                
                // Add 1-2 random products to each order
                $itemCount = rand(1, 2);
                $selectedProducts = array_rand($availableProducts, min($itemCount, count($availableProducts)));
                if (!is_array($selectedProducts)) {
                    $selectedProducts = [$selectedProducts];
                }
                
                foreach ($selectedProducts as $productIndex) {
                    $product = $availableProducts[$productIndex];
                    $quantity = rand(1, 2);
                    $subtotal = $product['price'] * $quantity;
                    $orderTotal += $subtotal;
                    
                    $orderItems[] = [
                        'product_id' => $product['product_id'],
                        'quantity' => $quantity,
                        'subtotal' => $subtotal
                    ];
                }
                
                // Insert order
                $orderTime = date('Y-m-d H:i:s', strtotime($orderDate . ' ' . rand(9, 19) . ':' . rand(0, 59) . ':' . rand(0, 59)));
                $stmt = $pdo->prepare("INSERT INTO orders (order_date, total_amount, payment_status, user_id) VALUES (?, ?, 'Paid', 114)");
                $stmt->execute([$orderTime, $orderTotal]);
                $orderId = $pdo->lastInsertId();
                
                // Insert order items
                foreach ($orderItems as $item) {
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['subtotal']]);
                }
                
                // Insert payment
                $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, cash_received, change_amount, payment_time, cashier_id) VALUES (?, 'Cash', ?, 0.00, ?, 114)");
                $stmt->execute([$orderId, $orderTotal, $orderTime]);
            }
        }
    }
    
    echo "Sample data seeding completed successfully!\n";
    
    // Show summary
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $productCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $orderCount = $stmt->fetch()['count'];
    
    echo "\nDatabase summary:\n";
    echo "Categories: $categoryCount\n";
    echo "Products: $productCount\n";
    echo "Orders: $orderCount\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
