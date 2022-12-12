INSERT INTO `ident_agenda_status` (`id`, `lixo`, `titulo`, `cor`, `kanban_ordem`) VALUES (8, '', 'RESERVA DE HORÁRIO', '#545559', '1');


CREATE TABLE `ident_parametros_politicapagamento` (
  `id` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `entrada` double NOT NULL COMMENT 'pagamento minimo (%)',
  `parcelas` int NOT NULL COMMENT 'numero maximo de parcelas',
  `de` double NOT NULL,
  `ate` double NOT NULL,
  `parcelasParametros` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_parametros_politicapagamento`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_parametros_politicapagamento`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

CREATE TABLE `ident_pacientes_evolucoes_alta` (
  `id` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_evolucao` int NOT NULL,
  `id_profissional` int NOT NULL,
  `texto` text NOT NULL,
  `id_usuario` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_pacientes_evolucoes_alta`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `ident_pacientes_evolucoes_alta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;
