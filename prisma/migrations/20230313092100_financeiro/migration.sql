ALTER TABLE `ident_pacientes_tratamentos_procedimentos` CHANGE `hof` `hof` DOUBLE NOT NULL;
ALTER TABLE `ident_pacientes_arquivos` ADD `lixo` BOOLEAN NOT NULL AFTER `obs`;
