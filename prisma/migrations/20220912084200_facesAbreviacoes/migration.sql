ALTER TABLE `ident_parametros_procedimentos_regioes_faces` ADD `abreviacao` VARCHAR(10) NOT NULL AFTER `titulo`;
UPDATE `ident_parametros_procedimentos_regioes_faces` SET `abreviacao` = 'M' WHERE `id` = 1;
UPDATE `ident_parametros_procedimentos_regioes_faces` SET `abreviacao` = 'D' WHERE `id` = 2;
UPDATE `ident_parametros_procedimentos_regioes_faces` SET `abreviacao` = 'O/I' WHERE `id` = 3;
UPDATE `ident_parametros_procedimentos_regioes_faces` SET `abreviacao` = 'V' WHERE `id` = 4;
UPDATE `ident_parametros_procedimentos_regioes_faces` SET `abreviacao` = 'L/P' WHERE `id` = 5;