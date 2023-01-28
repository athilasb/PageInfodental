
ALTER TABLE `ident_pacientes_tratamentos_pagamentos_baixas` CHANGE `tipoBaixa` `tipoBaixa` ENUM('pagamento','desconto','despesa','multas','juros') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL;

ALTER TABLE `ident_pacientes_tratamentos_pagamentos_baixas` ADD `valorJuros` DOUBLE NOT NULL DEFAULT '0' AFTER `cobrarJuros`, ADD `valorMulta` DOUBLE NOT NULL DEFAULT '0' AFTER `valorJuros`;