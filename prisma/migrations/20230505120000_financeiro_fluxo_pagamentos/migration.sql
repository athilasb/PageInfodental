CREATE TABLE `ident_financeiro_fluxo_pagamentos` (
  `id` int NOT NULL,
  `data_emissao` date NOT NULL,
  `data_vencimento` date NOT NULL,
  `tipo` enum('paciente','fornecedor','colaborador') NOT NULL,
  `id_pagante_beneficiario` int NOT NULL,
  `id_colaborador` int NOT NULL,
  `valor` double NOT NULL,
  `valor_multa` double NOT NULL DEFAULT '0',
  `valor_taxa` double NOT NULL DEFAULT '0',
  `valor_desconto` double NOT NULL DEFAULT '0',
  `taxa_cartao` double NOT NULL DEFAULT '0',
  `fusao` tinyint(1) NOT NULL,
  `id_fusao` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `lixo_id_colaborador` int NOT NULL,
  `pago` tinyint(1) NOT NULL,
  `descricao` VARCHAR(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_financeiro_fluxo_pagamentos`
  ADD PRIMARY KEY (`id`);
  
ALTER TABLE `ident_financeiro_fluxo_pagamentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;