-- Add PayPal transaction details table to support detailed PayPal transaction tracking
-- Execute this script to add PayPal integration support to your existing database

USE pos_system;

-- Create PayPal transaction details table
CREATE TABLE IF NOT EXISTS `paypal_transaction_details` (
  `detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `paypal_order_id` varchar(255) NOT NULL,
  `payer_id` varchar(255) DEFAULT NULL,
  `payer_email` varchar(255) DEFAULT NULL,
  `transaction_details` text DEFAULT NULL COMMENT 'JSON encoded PayPal order details',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`detail_id`),
  KEY `idx_payment_id` (`payment_id`),
  KEY `idx_paypal_order_id` (`paypal_order_id`),
  CONSTRAINT `fk_paypal_details_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores detailed PayPal transaction information';

-- Update payments table transaction_status to ensure 'Success' is a valid value
ALTER TABLE `payments` MODIFY `transaction_status` enum('Pending','Success','Failed') DEFAULT 'Pending';

-- Add index for better PayPal transaction queries
ALTER TABLE `payments` ADD INDEX `idx_paypal_transaction_id` (`paypal_transaction_id`);
ALTER TABLE `payments` ADD INDEX `idx_payment_method` (`payment_method`);

-- Show current table structure for verification
DESCRIBE `paypal_transaction_details`;
DESCRIBE `payments`;
