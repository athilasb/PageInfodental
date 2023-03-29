ALTER TABLE `ident_whatsapp_mensagens` ADD `fila_agenda_data` DATETIME NOT NULL AFTER `fila_numero`, ADD `fila_enviada` DATETIME NOT NULL AFTER `fila_agenda_data`;
