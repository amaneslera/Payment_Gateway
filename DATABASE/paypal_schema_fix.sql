-- PayPal Integration Schema Fix
-- This script adds missing columns to support PayPal payment processing

-- Fix orders table - add missing columns
ALTER TABLE orders 
ADD COLUMN subtotal DECIMAL(10,2) NULL AFTER total_amount,
ADD COLUMN tax DECIMAL(10,2) NULL AFTER subtotal;

-- Update orders table to make total compatible with PayPal API naming
-- Note: We'll keep total_amount for compatibility but add computed values

-- For existing orders, calculate subtotal and tax based on total_amount
UPDATE orders 
SET 
    subtotal = ROUND(total_amount / 1.12, 2),  -- Assuming 12% tax
    tax = ROUND(total_amount - (total_amount / 1.12), 2)
WHERE subtotal IS NULL;

-- Fix order_items table - add price column to match PayPal API expectations
ALTER TABLE order_items 
ADD COLUMN price DECIMAL(10,2) NULL AFTER quantity;

-- Update existing order_items to calculate price from subtotal and quantity
UPDATE order_items 
SET price = ROUND(subtotal / quantity, 2)
WHERE price IS NULL AND quantity > 0;

-- Make the new columns NOT NULL after setting values
ALTER TABLE orders 
MODIFY COLUMN subtotal DECIMAL(10,2) NOT NULL,
MODIFY COLUMN tax DECIMAL(10,2) NOT NULL;

ALTER TABLE order_items 
MODIFY COLUMN price DECIMAL(10,2) NOT NULL;

-- Verify the changes
SELECT 'Orders table structure:' as info;
DESCRIBE orders;

SELECT 'Order items table structure:' as info;
DESCRIBE order_items;

-- Show sample data to verify calculations
SELECT 'Sample orders data:' as info;
SELECT order_id, total_amount, subtotal, tax, 
       ROUND(subtotal + tax, 2) as calculated_total 
FROM orders 
ORDER BY order_id DESC 
LIMIT 5;

SELECT 'Sample order items data:' as info;
SELECT order_item_id, order_id, product_id, quantity, price, subtotal,
       ROUND(price * quantity, 2) as calculated_subtotal
FROM order_items 
ORDER BY order_item_id DESC 
LIMIT 5;
