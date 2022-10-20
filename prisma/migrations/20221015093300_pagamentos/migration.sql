ALTER TABLE `ident_pacientes_tratamentos_pagamentos_baixas` CHANGE `taxa` `taxa` DOUBLE NOT NULL;

ALTER TABLE `ident_pacientes_tratamentos_pagamentos_baixas` ADD `dias` INT NOT NULL AFTER `taxa`;
