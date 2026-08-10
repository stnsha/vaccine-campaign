-- Add recycle (soft-delete) column to vaccine_campaign
-- Run on the odb database before deploying vaccine_campaign_dedupe.php soft-delete logic.

ALTER TABLE vaccine_campaign
    ADD COLUMN recycle tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=active, 1=recycled/soft-deleted';
