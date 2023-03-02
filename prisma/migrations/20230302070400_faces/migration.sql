
DROP TABLE `ident_parametros_procedimentos_regioes_faces`;

CREATE TABLE `ident_parametros_procedimentos_regioes_faces` (
  `id` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `abreviacao` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `ident_parametros_procedimentos_regioes_faces` (`id`, `lixo`, `titulo`, `abreviacao`) VALUES
(1, 0, 'MESIAL', 'M'),
(2, 0, 'DISTAL', 'D'),
(3, 0, 'OCLUSAL/INCISAL', 'O/I'),
(4, 0, 'VESTIBULAR', 'V'),
(5, 0, 'LINGUAL/PALATINA', 'L/P');

ALTER TABLE `ident_parametros_procedimentos_regioes_faces`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_parametros_procedimentos_regioes_faces`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;
