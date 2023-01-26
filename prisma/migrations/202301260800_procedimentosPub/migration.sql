ALTER TABLE `ident_parametros_procedimentos` ADD `pub` BOOLEAN NOT NULL AFTER `data`;
update  `ident_parametros_procedimentos` set pub=1;