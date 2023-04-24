
CREATE TABLE `ident_pacientes_evolucoes_pedidosdeexames_anexos` (
  `id` int NOT NULL,
  `data` datetime NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `lixo_id_colaborador` int NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `arq` varchar(10) NOT NULL,
  `id_evolucao_pedidodeexame` int NOT NULL,
  `id_evolucao` int NOT NULL,
  `id_paciente` int NOT NULL,
  `id_colaborador` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_pacientes_evolucoes_pedidosdeexames_anexos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_pacientes_evolucoes_pedidosdeexames_anexos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
