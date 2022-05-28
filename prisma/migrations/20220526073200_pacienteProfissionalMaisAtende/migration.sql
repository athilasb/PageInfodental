ALTER TABLE `ident_pacientes` ADD `profissional_maisAtende` INT NOT NULL AFTER `periodicidade`;
ALTER TABLE `ident_whatsapp_mensagens` ADD `semConexao` BOOLEAN NOT NULL AFTER `reenvio_data`;
ALTER TABLE `ident_whatsapp_mensagens` CHANGE `semConexao` `semConexao` DATETIME(1) NOT NULL COMMENT 'reenvia sem conexao';
