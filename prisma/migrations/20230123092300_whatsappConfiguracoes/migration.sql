ALTER TABLE `ident_parametros_formasdepagamento` ADD `politica_de_pagamento` BOOLEAN NOT NULL AFTER `tipo`;

CREATE TABLE `ident_parametros_tags` (
  `id` int NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `cor` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `lixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `ident_parametros_tags`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_parametros_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `ident_parametros_politicapagamento` ADD `status` INT NOT NULL DEFAULT '0' AFTER `lixo`;

ALTER TABLE `ident_pacientes_tratamentos` ADD `tipo_financeiro` ENUM('manual','politica') NOT NULL AFTER `procedimentos`;

ALTER TABLE `ident_pacientes_tratamentos` ADD `id_politica` INT NULL DEFAULT '0' AFTER `id_profissional`;
