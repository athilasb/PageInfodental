ALTER TABLE `ident_whatsapp_respostasdeconfirmacao` ADD `msgWebhookDesativado` TEXT NOT NULL AFTER `msgNaoIdentificado`;
UPDATE `ident_whatsapp_respostasdeconfirmacao` SET `msgWebhookDesativado` = 'Infelizmente não conseguimos entender a mensagem.\r\n\r\nLigue para a clínica e veja se seu agendamento no dia: *[agenda_data]*, às *[agenda_hora]*, com duração de *[duracao]*, ainda está disponível.' WHERE `id` = 1;
INSERT INTO `ident_whatsapp_mensagens_tipos` (`id`, `lixo`, `titulo`, `pub`, `getProfile`, `texto`, `geolocalizacao`) VALUES ('12', '', 'Desativação da espera de Confirmação de Agendamento', '1', '0', 'Ligue para a clínica e veja se seu agendamento no dia: *[agenda_data]*, às *[agenda_hora]*, com duração de *[duracao]*, ainda está disponível.', '0');
ALTER TABLE `ident_whatsapp_mensagens` ADD `webhook_expirado_inoperacao` DATETIME NOT NULL COMMENT 'data de quando webhook foi desativado por falta de resposta' AFTER `webhook_expiracao`;

