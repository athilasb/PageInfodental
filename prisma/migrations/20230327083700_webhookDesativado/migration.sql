ALTER TABLE `ident_whatsapp_mensagens` ADD `webhook_desativado` BOOLEAN NOT NULL AFTER `arquivo_titulo`, ADD `webhook_obs` VARCHAR(250) NOT NULL AFTER `webhook_desativado`;
ALTER TABLE `ident_whatsapp_mensagens` ADD `webhook_expiracao` DATETIME NOT NULL AFTER `webhook_obs`;
