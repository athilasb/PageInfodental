ALTER TABLE `ident_whatsapp_mensagens` ADD `fila_data` DATETIME NOT NULL AFTER `webhook_expiracao`, ADD `fila_numero` VARCHAR(30) NOT NULL AFTER `fila_data`, ADD INDEX (`fila_numero`);
