-- Activity Center needs to record actions taken by CLIENTS (logged into
-- the portal), not just staff. `user_id` was NOT NULL, which made that
-- impossible without a fake staff row. Make it nullable and add a
-- parallel `client_user_id` so every activity row is attributable to
-- exactly one real actor (staff OR client), never both, never neither
-- silently faked.
ALTER TABLE `activity_logs` MODIFY COLUMN `user_id` INT NULL;
ALTER TABLE `activity_logs` ADD COLUMN `client_user_id` INT NULL AFTER `user_id`;
ALTER TABLE `activity_logs` ADD CONSTRAINT `activity_logs_client_user_id_foreign`
    FOREIGN KEY (`client_user_id`) REFERENCES `client_users`(`id`) ON DELETE SET NULL;
