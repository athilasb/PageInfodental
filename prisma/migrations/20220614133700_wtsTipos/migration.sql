ALTER TABLE `ident_whatsapp_mensagens_tipos` ADD `lixo` BOOLEAN NOT NULL AFTER `id`;
UPDATE `ident_whatsapp_mensagens_tipos` SET lixo=1 WHERE id=4;