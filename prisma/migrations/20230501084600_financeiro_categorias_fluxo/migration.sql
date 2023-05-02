CREATE TABLE `ident_financeiro_fluxo_categorias` (
  `id` int NOT NULL,
  `data` datetime NOT NULL,
  `tipo` enum('categoria','subcategoria') NOT NULL,
  `titulo` varchar(250) NOT NULL,
  `lixo` int NOT NULL DEFAULT '0',
  `id_categoria` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `ident_financeiro_fluxo_categorias`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_financeiro_fluxo_categorias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;


ALTER TABLE `ident_financeiro_fluxo` ADD INDEX(`id_origem`);

ALTER TABLE `ident_financeiro_fluxo` ADD INDEX(`id_registro`);

ALTER TABLE `ident_financeiro_fluxo` ADD INDEX(`pagamento`);

ALTER TABLE `ident_financeiro_fluxo` ADD INDEX(`tipo`);

ALTER TABLE `ident_financeiro_fluxo` ADD INDEX(`id_banco`);

ALTER TABLE `ident_financeiro_fluxo` ADD INDEX(`dividido`);

ALTER TABLE `ident_financeiro_fluxo` ADD INDEX(`id_dividido`);