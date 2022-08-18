update `ident_whatsapp_mensagens_tipos` set texto='Olá *[nome]*, aqui é o assistente virtual da *[clinica_nome]*! 

Preciso da sua ajuda para confirmar seu horário. 🙏

Seu agendamento está no dia: *[agenda_data]*,  às *[agenda_hora]*, com duração de *[duracao]*, podemos confirmar? 😁 📅

É importante que na resposta digite *APENAS 1 OU 2*:

Digite 1 - Para *Confirmar* 
Digite 2 - Para *Desmarcar*' where id=1;

update `ident_whatsapp_mensagens_tipos` set texto='Olá *[nome]*. Apenas lembrando que seu horário é daqui há pouco às *[agenda_hora]* ✅

*[clinica_nome]* - [clinica_endereco] 📍
[clinica_geolocalizacao]' where id=2;


update `ident_whatsapp_mensagens_tipos` set texto='Olá *[nome]*, aqui é o assistente virtual da *[clinica_nome]*! 

Seu agendamento na *[agenda_data]*,  às *[agenda_hora]*, foi *DESMARCADO* ❌

Em breve entraremos em contato para novo agendamento 😉' where id=3;

update `ident_whatsapp_mensagens_tipos` set texto='Olá *[nome]*, aqui é o assistente virtual da *[clinica_nome]*! 

Hove uma alteração no seu agendamento  🔄
Horário Antigo:  *[agenda_antiga_data]*,  às *[agenda_antiga_hora]*  ❌
Horário Novo: *[agenda_data]*,  às *[agenda_hora]*, com duração de *[duracao]*. ✅

Qualquer dúvida entre em contato, com nossa secretária. ' where id=5;


update `ident_whatsapp_mensagens_tipos` set texto='Olá *[profissionais]*, aqui é o assistente virtual da *[clinica_nome]*! 

O agendamento do *[nome]* está *CONFIRMADO* ✅

*[agenda_data]*,  às *[agenda_hora]*, com duração de *[duracao]*.' where id=6;


update `ident_whatsapp_mensagens_tipos` set texto='Olá *[profissionais]*, aqui é o assistente virtual da *[clinica_nome]*! 

O agendamento do *[paciente] foi alterado de *[agenda_antiga_data]*,  às *[agenda_antiga_hora]*  para *[agenda_data]*,  às *[agenda_hora]*, com duração de *[duracao]*.' where id=7;


update `ident_whatsapp_mensagens_tipos` set texto='Olá *[profissionais]*, aqui é o assistente virtual da *[clinica_nome]! 

O agendamento do *[paciente]* está *DESMARCADO* ❌

*[agenda_data]*,  às *[agenda_hora]*, com duração de *[duracao]*.' where id=8;

