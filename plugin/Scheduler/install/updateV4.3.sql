ALTER TABLE `scheduler_commands`
ADD COLUMN IF NOT EXISTS `type` VARCHAR(45) DEFAULT NULL;
