UPDATE `ident_parametros_procedimentos_regioes` SET `quantitativo` = '0' WHERE `ident_parametros_procedimentos_regioes`.`id` = 5;
ALTER TABLE `ident_pacientes_tratamentos_procedimentos` ADD `face` BOOLEAN NOT NULL AFTER `obs`, ADD `faces` VARCHAR(100) NOT NULL AFTER `face`, ADD `id_regiao` INT NOT NULL AFTER `faces`, ADD `hof` INT NOT NULL AFTER `id_regiao`;
