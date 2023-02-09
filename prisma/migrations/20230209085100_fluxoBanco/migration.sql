ALTER TABLE `ident_financeiro_fluxo` ADD `id_banco` INT NOT NULL AFTER `obs`;

CREATE TABLE `ident_colaboradores_comissionamentos` (
  `id` int NOT NULL,
  `data` datetime NOT NULL,
  `id_colaborador` int NOT NULL,
  `tipo` enum('venda','procedimento') NOT NULL,
  `gatilho` int NOT NULL,
  `percentual` tinyint(1) NOT NULL COMMENT 'se comissao e feito por percentual',
  `valor_percentual` double NOT NULL,
  `valor_percentual_total` double NOT NULL,
  `valor_comissao` double NOT NULL,
  `id_procedimento` int NOT NULL,
  `id_tratamento` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_colaboradores_comissionamentos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_colaboradores_comissionamentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;


CREATE TABLE `ident_financeiro_fluxo_recebimentos` (
  `id` int NOT NULL,
  `data_emissao` date NOT NULL,
  `data_vecimento` date NOT NULL,
  `tipo` enum('paciente','fornecedor','colaborador') NOT NULL,
  `id_pagante` int NOT NULL,
  `id_tratamento` int NOT NULL,
  `valor` double NOT NULL,
  `fusao` tinyint(1) NOT NULL,
  `id_fusao` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `lixo_id_colaborador` int NOT NULL,
  `id_formapagamento` int NOT NULL,
  `qtdParcelas` int NOT NULL,
  `pago` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `ident_financeiro_fluxo_recebimentos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_financeiro_fluxo_recebimentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

ALTER TABLE `ident_financeiro_fluxo` ADD `tipo` ENUM('paciente','colaborador','fornecedor') NOT NULL AFTER `id_formapagamento`;

ALTER TABLE `ident_financeiro_fluxo` CHANGE `id_paciente` `id_pagante_beneficiario` INT NOT NULL;
