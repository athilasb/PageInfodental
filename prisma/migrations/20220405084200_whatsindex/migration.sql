
CREATE TABLE `ident_whatsapp_mensagens` (
  `id` int NOT NULL,
  `data` datetime NOT NULL,
  `enviado` tinyint(1) NOT NULL,
  `id_conexao` int NOT NULL,
  `erro` tinyint(1) NOT NULL,
  `data_erro` datetime NOT NULL,
  `erro_retorno` varchar(250) NOT NULL,
  `data_enviado` datetime NOT NULL,
  `id_paciente` int NOT NULL,
  `id_profissional` int NOT NULL,
  `id_agenda` int NOT NULL,
  `id_tipo` int NOT NULL,
  `numero` bigint NOT NULL,
  `mensagem` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `lng` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `retorno_json` text NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `resposta_sim` tinyint(1) NOT NULL,
  `resposta_nao` tinyint(1) NOT NULL,
  `resposta_naocompreendida` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ident_whatsapp_mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enviado` (`enviado`),
  ADD KEY `erro` (`erro`),
  ADD KEY `data` (`data`),
  ADD KEY `lixo` (`lixo`),
  ADD KEY `id_agenda` (`id_agenda`),
  ADD KEY `id_agenda_2` (`id_agenda`),
  ADD KEY `id_agenda_3` (`id_agenda`);

ALTER TABLE `ident_whatsapp_mensagens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1243;