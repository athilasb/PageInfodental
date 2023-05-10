ALTER TABLE `ident_whatsapp_mensagens_tipos` ADD `texto_original` TEXT NOT NULL AFTER `texto`;

UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[nome]*, aqui é o assistente virtual da *[clinica_nome]*!\n\nData: *[agenda_data]*, às *[agenda_hora]*, com duração de *[duracao]*.\n\nProfissionais: *[agenda_profissionais]*.\n\nPodemos confirmar seu agendamento? 😁 📅\n\nDigite 1 ou aperte em Sim - Para *Confirmar*\nDigite 2 ou aperte em Não - Para *Desmarcar*' WHERE `id` = 1;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[nome]*. Apenas lembrando que seu horário é daqui há pouco às *[agenda_hora]* ✅\n\n*[clinica_nome]* - [clinica_endereco] 📍' WHERE `id` = 2;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[nome]*, aqui é o assistente virtual da *[clinica_nome]*!\n\nSeu agendamento na *[agenda_data]*,  às *[agenda_hora]*, foi *DESMARCADO* ❌\n\nEm breve entraremos em contato para novo agendamento 😉' WHERE `id` = 3;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[nome]*, aqui é o Gestor de Pacientes da *[clinica_nome]*!\n\nEm nosso sistema, consta que seu último atendimento já tem [tempo_sem_atendimento] 😱\n\nÉ importante lembrar que a prevenção é essencial para identificar qualquer alteração na sua saúde bucal! ⚠️\n\nDeseja agendar?', `texto` = 'Olá *[nome]*, aqui é o Gestor de Pacientes da *[clinica_nome]*!\n\nEm nosso sistema, consta que seu último atendimento já tem [tempo_sem_atendimento] 😱\n\nÉ importante lembrar que a prevenção é essencial para identificar qualquer alteração na sua saúde bucal! ⚠️\n\nDeseja agendar?' WHERE `id` = 4;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[nome]*, aqui é o assistente virtual da *[clinica_nome]*!\n\nHove uma alteração no seu agendamento 🔄\n\nHorário Antigo:  *[agenda_antiga_data]*,  às *[agenda_antiga_hora]*  ❌\nHorário Novo: *[agenda_data]*,  às *[agenda_hora]*, com duração de *[duracao]*. ✅\n\nQualquer dúvida entre em contato, com nossa secretária. ' WHERE `id` = 5;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[profissionais]*, aqui é o assistente virtual da *[clinica_nome]*!\n\nO agendamento do *[nome]* está *CONFIRMADO* ✅\n\n*[agenda_data]*,  às *[agenda_hora]*, com duração de *[duracao]*.' WHERE `id` = 6;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[profissionais]*, aqui é o assistente virtual da *[clinica_nome]*!\n\nO agendamento do *[paciente] foi alterado de *[agenda_antiga_data]*,  às *[agenda_antiga_hora]*  para *[agenda_data]*,  às *[agenda_hora]*, com duração de *[duracao]*.' WHERE `id` = 7;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[profissionais]*, aqui é o assistente virtual da *[clinica_nome]*!\n\nO agendamento do *[paciente]* está *DESMARCADO* ❌\n\n*[agenda_data]*, às *[agenda_hora]*, com duração de *[duracao]*.' WHERE `id` = 8;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[nome]*, segue o PDF de seu prontuário' WHERE `id` = 9;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Olá *[nome]*! Segue link para preenchimento de sua Anamnese:\n\n[linkAnamnese]' WHERE `id` = 11;
UPDATE `ident_whatsapp_mensagens_tipos` SET `texto_original` = 'Ligue para a clínica e veja se seu agendamento no dia: *[agenda_data]*, às *[agenda_hora]*, com duração de *[duracao]*, ainda está disponível.' WHERE `id` = 12;


ALTER TABLE `ident_whatsapp_respostasdeconfirmacao` ADD `msgSim_original` TEXT NOT NULL AFTER `msgInteligenciaNaoIdentificado`, ADD `msgNao_original` TEXT NOT NULL AFTER `msgSim_original`;
UPDATE `ident_whatsapp_respostasdeconfirmacao` SET `msgSim_original` = 'Obrigado pela ajuda 🙏\n\nSeu horário está *CONFIRMADO* ✅\n\nAté breve' WHERE `id` = 1;
UPDATE `ident_whatsapp_respostasdeconfirmacao` SET `msgNao_original` = 'Obrigado pela ajuda 🙏\n\nSeu horário está *DESMARCADO* ❌\n\nEntraremos em contato em breve' WHERE `id` = 1;

DELETE FROM `ident_whatsapp_mensagens_tipos` WHERE `id`=13;

INSERT INTO `ident_whatsapp_mensagens_tipos` (`id`, `lixo`, `titulo`, `pub`, `getProfile`, `texto`, `texto_original`, `geolocalizacao`) VALUES
(13, 0, 'Aniversariantes do dia', 1, 0, 'Olá *[nome]*, parabéns pelo seu aniversário 🎂🎉🎉🎉', 'Olá *[nome]*, parabéns pelo seu aniversário 🎂🎉🎉🎉', 0);
COMMIT;
UPDATE `ident_whatsapp_mensagens_tipos` SET `lixo` = '0', `pub`=1 WHERE `ident_whatsapp_mensagens_tipos`.`id` = 4;
UPDATE `ident_whatsapp_mensagens_tipos` SET `titulo` = 'Relacionamento Gestão de Paciente' WHERE `ident_whatsapp_mensagens_tipos`.`id` = 4;
