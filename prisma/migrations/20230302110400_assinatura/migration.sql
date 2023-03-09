
CREATE TABLE `ident_pacientes_assinaturas` (
  `id` int NOT NULL,
  `id_evolucao` int NOT NULL,
  `id_tipo_evolucao` int NOT NULL,
  `id_paciente` int NOT NULL,
  `data` datetime NOT NULL,
  `png_url` text CHARACTER SET utf8 COLLATE utf8_unicode_520_ci,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `aprox` float DEFAULT NULL,
  `user_agent` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_pacientes_assinaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tipo_evolucao` (`id_tipo_evolucao`),
  ADD KEY `id_evolucao` (`id_evolucao`),
  ADD KEY `id_paciente` (`id_paciente`);

ALTER TABLE `ident_pacientes_assinaturas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

COMMIT;
