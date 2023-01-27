ALTER TABLE `ident_parametros_formasdepagamento` ADD `politica_de_pagamento` BOOLEAN NOT NULL AFTER `tipo`;

ALTER TABLE `ident_parametros_politicapagamento` ADD `status` INT NOT NULL DEFAULT '0' AFTER `lixo`;

ALTER TABLE `ident_pacientes_tratamentos` ADD `tipo_financeiro` ENUM('manual','politica') NOT NULL AFTER `procedimentos`;

ALTER TABLE `ident_pacientes_tratamentos` ADD `id_politica` INT NULL DEFAULT '0' AFTER `id_profissional`;
