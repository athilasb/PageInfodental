UPDATE `ident_whatsapp_mensagens_tipos` SET `texto` = 'Olá *[nome]*, aqui é o assistente virtual da *[clinica_nome]*! \r\n\r\nData: *[agenda_data]*, às *[agenda_hora]*, com duração de *[duracao]*.\r\n\r\nProfissionais: *[agenda_profissionais]*.\r\n\r\nPodemos confirmar seu agendamento? 😁 📅\r\n\r\nDigite 1 ou aperte em Sim - Para *Confirmar* \r\nDigite 2 ou aperte em Não - Para *Desmarcar*' WHERE `id` = 1;
ALTER TABLE `ident_financeiro_fluxo` ADD `descricao` VARCHAR(500) NULL DEFAULT NULL AFTER `desconto`;
INSERT INTO `ident_financeiro_fluxo_origens` (`id`, `tabela`, `titulo`) VALUES (2, 'ident_financeiro_fluxo', 'ident_financeiro_fluxo')