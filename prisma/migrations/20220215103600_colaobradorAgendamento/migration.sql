ALTER TABLE `ident_colaboradores` ADD `comissionamento_tipo` VARCHAR(10) NOT NULL AFTER `carga_horaria`;
ALTER TABLE `ident_colaboradores` ADD `check_agendamento` BOOLEAN NOT NULL AFTER `comissionamento_tipo`;
