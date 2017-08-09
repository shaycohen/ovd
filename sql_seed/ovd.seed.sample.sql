INSERT INTO `warehouse` (`id`, `description`) VALUES
(1, 'warehouse1');
INSERT INTO `warehouse` (`id`, `description`) VALUES
(2, 'warehouse2');

INSERT INTO `container` (`id`, `description`, `warehouse_id`) VALUES
(1, 'cont1', 1);
INSERT INTO `container` (`id`, `description`, `warehouse_id`) VALUES
(2, 'cont2', 2);
INSERT INTO `container` (`id`, `description`, `warehouse_id`) VALUES
(3, 'cont3', 1);
INSERT INTO `container` (`id`, `description`, `warehouse_id`) VALUES
(4, 'cont4', 2);

INSERT INTO `serial` (`id`, `container_id`, `number`, description`) VALUES
(1, 1, 'serial1_cont1', 's 1 c 1');
INSERT INTO `serial` (`id`, `container_id`, `number`, description`) VALUES
(2, 1, 'serial2_cont1', 's 2 c 1');
INSERT INTO `serial` (`id`, `container_id`, `number`, description`) VALUES
(3, 2, 'serial3_cont2', 's 3 c 2');
INSERT INTO `serial` (`id`, `container_id`, `number`, description`) VALUES
(4, 3, 'serial4_cont3', 's 4 c 3');
INSERT INTO `serial` (`id`, `container_id`, `number`, description`) VALUES
(5, 4, 'serial5_cont4', 's 5 c 4');

INSERT INTO `damage` (`id`, `serial_id`, `type`,  `enabled`, `description`) VALUES
(1, 1, 1, 1, 'damage1');
INSERT INTO `damage` (`id`, `serial_id`, `type`,  `enabled`, `description`) VALUES
(2, 1, 2, 1, 'damage2');
INSERT INTO `damage` (`id`, `serial_id`, `type`,  `enabled`, `description`) VALUES
(3, 1, 3, 1, 'damage3');
INSERT INTO `damage` (`id`, `serial_id`, `type`,  `enabled`, `description`) VALUES
(4, 1, 3, 1, 'damage4');
INSERT INTO `damage` (`id`, `serial_id`, `type`,  `enabled`, `description`) VALUES
(5, 2, 1, 1, 'damage5');
INSERT INTO `damage` (`id`, `serial_id`, `type`,  `enabled`, `description`) VALUES
(6, 2, 2, 1, 'damage6');
INSERT INTO `damage` (`id`, `container_id`, `type`,  `enabled`, `description`) VALUES
(7, 1, 1, 1, 'damage_container_1');
INSERT INTO `damage` (`id`, `container_id`, `type`,  `enabled`, `description`) VALUES
(8, 2, 1, 1, 'damage_container_2');
INSERT INTO `damage` (`id`, `container_id`, `type`,  `enabled`, `description`) VALUES
(9, 3, 1, 1, 'damage_container_3');

INSERT INTO `user` (`id`, `fname`, `lname`, `username`, `warehouse_id`, `pw`) VALUES
(1, 'user', 'one', 'user1', 1, 'pass1');
INSERT INTO `user` (`id`, `fname`, `lname`, `username`, `warehouse_id`, `pw`) VALUES
(2, 'user', 'two', 'user2', 2, 'pass2');

