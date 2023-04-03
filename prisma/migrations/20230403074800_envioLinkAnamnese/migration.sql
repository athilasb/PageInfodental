INSERT INTO `ident_whatsapp_mensagens_tipos` (`id`, `lixo`, `titulo`, `pub`, `getProfile`, `texto`, `geolocalizacao`) VALUES ('11', '0', 'Envio do Formulário de Preenchimento de Anamnese', '1', '0', 'Olá *[nome]*! Segue link para preenchimento de sua Anamnese:\r\n\r\n[linkAnamnese]', '');
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto` = 'Olá *[nome]*, tudo bem? ' WHERE `ident_whatsapp_mensagens_tipos`.`id` = 10;
ALTER TABLE `ident_whatsapp_mensagens` ADD `id_evolucao` INT NOT NULL AFTER `id_agenda`;
ALTER TABLE `ident_pacientes_evolucoes` ADD `enviarLink` INT NOT NULL COMMENT 'enviarLink = 1 -> link para preenchimento do paciente' AFTER `s3`, ADD `id_assinatura` INT NOT NULL COMMENT 'assinatura do paciente (ident_pacientes_assinaturas)' AFTER `enviarLink`;
