-- Migration script to add missing columns for PayPal integration
-- This adds only the essential columns needed for payment processing

-- Add the missing columns to orders table
ALTER TABLE `orders` 
ADD COLUMN `total` DECIMAL(10,2) NULL COMMENT 'Total amount paid (for PayPal compatibility)',
ADD COLUMN `order_status` VARCHAR(50) DEFAULT 'Pending' COMMENT 'Order status (Pending, Completed, Failed, etc.)',
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Order creation timestamp';

-- Update existing records to populate the new columns with current data
UPDATE `orders` 
SET 
    `total` = `total_amount`,
    `order_status` = CASE 
        WHEN `payment_status` = 'Paid' THEN 'Completed'
        WHEN `payment_status` = 'Cancelled' THEN 'Failed'
        ELSE 'Pending'
    END,
    `created_at` = `order_date`
WHERE `total` IS NULL;

-- Optional: Add payment response tracking table for PayPal responses
CREATE TABLE IF NOT EXISTS `payment_responses` (
    `response_id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `payment_provider` VARCHAR(50) DEFAULT 'PayPal',
    `transaction_id` VARCHAR(100) NULL,
    `response_data` JSON NULL COMMENT 'Full PayPal response data',
    `status` VARCHAR(50) NULL,
    `amount` DECIMAL(10,2) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`response_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add comments to clarify the purpose
ALTER TABLE `orders` 
MODIFY COLUMN `total_amount` DECIMAL(10,2) NOT NULL COMMENT 'Original total amount (legacy)',
MODIFY COLUMN `payment_status` ENUM('Pending','Paid','Cancelled') DEFAULT 'Pending' COMMENT 'Legacy payment status';
