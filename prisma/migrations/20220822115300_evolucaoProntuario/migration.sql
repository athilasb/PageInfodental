
CREATE TABLE `ident_pacientes_evolucoes_geral` (
  `id` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_evolucao` int NOT NULL,
  `data` date NOT NULL,
  `id_profissional` int NOT NULL,
  `id_usuario` int NOT NULL,
  `texto` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_pacientes_evolucoes_geral`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_pacientes_evolucoes_geral`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
