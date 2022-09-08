CREATE TABLE `ident_pacientes_evolucoes_erratas` (`id` INT NOT NULL AUTO_INCREMENT , `data` DATETIME NOT NULL , `id_usuario` INT NOT NULL , `id_evolucao` INT NOT NULL , `lixo` BOOLEAN NOT NULL , `texto` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `ident_pacientes_evolucoes_erratas` ADD `id_paciente` INT NOT NULL AFTER `id_evolucao`;
