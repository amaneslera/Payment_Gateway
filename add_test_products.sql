-- SQL Script to Add Test Products for PayPal Integration Test
-- This script adds the two test products that the PayPal test page expects

USE pos_system;

-- Insert test products for PayPal integration test
INSERT INTO `products` (`product_id`, `name`, `category_id`, `price`, `description`, `stock_quantity`, `cost_price`, `barcode`, `sku`, `product_image`, `is_food`, `expiry_date`, `min_stock_level`, `supplier_id`) VALUES
(1, 'Sample Coffee', 1, 150.00, 'Premium coffee blend for testing PayPal integration', 100, 90.00, 'TEST001', 'COFFEE001', NULL, 1, '2025-12-31', 10, NULL),
(2, 'Sample Pastry', 2, 85.00, 'Delicious pastry for testing PayPal integration', 50, 50.00, 'TEST002', 'PASTRY001', NULL, 1, '2025-06-30', 5, NULL);

-- Note: These products are specifically created for the PayPal integration test
-- Test cart expects:
-- - Product ID 1: Sample Coffee - ₱150.00
-- - Product ID 2: Sample Pastry - ₱85.00
-- Total test amount: ₱385.00 (₱300.00 + ₱85.00)
