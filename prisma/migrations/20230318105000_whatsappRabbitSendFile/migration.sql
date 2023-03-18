ALTER TABLE `ident_whatsapp_mensagens` ADD `arquivo` VARCHAR(250) NOT NULL AFTER `semConexao`;
INSERT INTO `ident_whatsapp_mensagens_tipos` (`id`, `lixo`, `titulo`, `pub`, `getProfile`, `texto`, `geolocalizacao`) VALUES ('10', '0', 'Envio de Arquivos', '1', '0', '', '');
ALTER TABLE `ident_whatsapp_mensagens` ADD `arquivo_titulo` VARCHAR(250) NOT NULL AFTER `arquivo`;

