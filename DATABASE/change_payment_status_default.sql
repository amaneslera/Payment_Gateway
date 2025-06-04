-- Migration script to change transaction_status default value from 'Pending' to 'Success'
-- Created: June 4, 2025

USE pos_system;

-- Modify the payments table to change the default value of transaction_status to 'Success'
ALTER TABLE `payments` 
MODIFY COLUMN `transaction_status` enum('Pending','Success','Failed') NOT NULL DEFAULT 'Success';

-- Verify the change by showing the table structure
DESCRIBE `payments`;

-- Optional: Update existing 'Pending' records to 'Success' (uncomment if needed)
-- UPDATE `payments` SET `transaction_status` = 'Success' WHERE `transaction_status` = 'Pending';

SELECT 'Migration completed: transaction_status default value changed to Success' as result;
