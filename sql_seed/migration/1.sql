ALTER TABLE `serial` ADD `serial_id` INT NULL DEFAULT NULL AFTER `container_id`;
ALTER TABLE `serial` ADD INDEX `serial_serial_id` (`serial_id`);
ALTER TABLE `serial`
  ADD CONSTRAINT `serial_serial_id` FOREIGN KEY (`serial_id`) REFERENCES `serial` (`id`);

