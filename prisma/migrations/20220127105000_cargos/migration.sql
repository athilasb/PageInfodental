
CREATE TABLE `ident_colaboradores_cargos` (
  `id` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



INSERT INTO `ident_colaboradores_cargos` (`id`, `titulo`) VALUES
(1, 'ASB'),
(2, 'TSB'),
(3, 'TPD'),
(4, 'APD'),
(5, 'Cirurgião Dentista'),
(6, 'Administrador Financeiro'),
(7, 'Recepcionista'),
(8, 'Gerente Geral');

ALTER TABLE `ident_colaboradores_cargos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_colaboradores_cargos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
