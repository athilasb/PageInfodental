
CREATE TABLE `ident_whatsapp_mensagens_complemento` (
  `id` int NOT NULL,
  `data` datetime NOT NULL,
  `numero` varchar(20) NOT NULL,
  `enviado` tinyint(1) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lng` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(250) NOT NULL,
  `data_enviado` datetime NOT NULL,
  `id_whatsapp` int NOT NULL,
  `json_request` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `erro` tinyint(1) NOT NULL,
  `data_erro` datetime NOT NULL,
  `erro_retorno` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_whatsapp_mensagens_complemento`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_whatsapp_mensagens_complemento`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
