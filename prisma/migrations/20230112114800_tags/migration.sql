ALTER TABLE `ident_agenda` ADD `tags` VARCHAR(250) NOT NULL AFTER `agenda_alteracao_id_whatsapp`;

CREATE TABLE `ident_parametros_tags` (
  `id` int NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `lixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_parametros_tags`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_parametros_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;


CREATE TABLE `ident_landingpage_antesedepois` (
  `id` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_tema` int NOT NULL,
  `data` datetime NOT NULL,
  `foto_antes1` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `foto_depois1` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `foto_antes2` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `foto_depois2` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nome_paciente1` varchar(150) NOT NULL,
  `nome_paciente2` varchar(150) NOT NULL,
  `id_usuario` int NOT NULL,
  `id_alteracao` int NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_landingpage_antesedepois`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_landingpage_antesedepois`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;



DROP TABLE `ident_landingpages_conversao`;

CREATE TABLE `ident_landingpage_conversao` (
  `id` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int NOT NULL,
  `id_alteracao` int NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `id_tema` int NOT NULL,
  `teleconsulta_nome` varchar(150) NOT NULL,
  `teleconsulta_valor` double NOT NULL,
  `teleconsulta_desconto` double NOT NULL,
  `teleconsulta_beneficios` text NOT NULL,
  `teleconsulta_mensagem` text NOT NULL,
  `consultapresencial_nome` varchar(150) NOT NULL,
  `consultapresencial_valor` double NOT NULL,
  `consultapresencial_desconto` double NOT NULL,
  `consultapresencial_beneficios` text NOT NULL,
  `consultapresencial_mensagem` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_landingpage_conversao`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_landingpage_conversao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;


ALTER TABLE `ident_landingpages_depoimentos` RENAME `ident_landingpage_depoimentos`;

CREATE TABLE `ident_avaliacoes_habilitadas` (`id` INT NOT NULL AUTO_INCREMENT , `id_tipo` INT NOT NULL , `pub` TINYINT(1) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;