CREATE TABLE `ident_financeiro_fluxo_splits_centrodecusto` (
  `id` int NOT NULL,
  `data` datetime DEFAULT NULL,
  `lixo` int NOT NULL DEFAULT '0',
  `titulo` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `ident_financeiro_fluxo_splits_centrodecusto`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_financeiro_fluxo_splits_centrodecusto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;
