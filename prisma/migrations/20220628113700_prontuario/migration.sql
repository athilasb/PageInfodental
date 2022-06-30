CREATE TABLE `ident_pacientes_prontuarios` (`id` INT NOT NULL AUTO_INCREMENT , `data` DATETIME NOT NULL , `id_usuario` INT NOT NULL , `id_paciente` INT NOT NULL , `texto` LONGTEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `ident_pacientes_prontuarios` ADD `lixo` BOOLEAN NOT NULL AFTER `texto`;
