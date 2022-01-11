
CREATE TABLE `ident_pacientes_historico_status` (
  `id` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `cor` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ident_pacientes_historico_status` (`id`, `lixo`, `titulo`, `cor`) VALUES
(1, 0, 'Não conseguiu contato', 'orange'),
(2, 0, 'Paciente entrará em contato', 'gray'),
(3, 0, 'Paciente pediu para retornar posteriormente', 'blue');


ALTER TABLE `ident_pacientes_historico_status`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_pacientes_historico_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
