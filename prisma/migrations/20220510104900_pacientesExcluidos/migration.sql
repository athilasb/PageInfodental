CREATE TABLE `ident_pacientes_excluidos` ( 
	`id` INT(11) NOT NULL AUTO_INCREMENT , 
	`lixo` BOOLEAN NOT NULL , 
	`data` DATETIME NOT NULL , 
	`id_paciente` INT NOT NULL , 
	`motivo` TINYTEXT NOT NULL , 
	`id_usuario` INT NOT NULL , `lixo_data` DATETIME NOT NULL , `lixo_id_usuario` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
