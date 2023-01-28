

INSERT INTO `ident_parametros_anamnese` (`id`, `lixo`, `titulo`, `perguntas`) VALUES
(1000, 0, 'Anamnese Direcionada Pelo Dentista ', ''),
(1001, 0, 'Anamnese Respondida Pelo Paciente ', ''),
(1002, 0, 'Anamnese Harmonização Oro Facial ', '');


INSERT INTO `ident_parametros_anamnese_formulario` (`id`, `lixo`, `pub`, `id_anamnese`, `pergunta`, `tipo`, `alerta`, `obrigatorio`, `ordem`) VALUES
(NULL, 0, 0, 1000, 'Qual nota você da ao seu Sorriso?', 'nota', 'nenhum', 0, 7),
(NULL, 0, 0, 1000, 'Prioriza mais um tratamento conservador ou busca mais agilidade?', 'texto', 'nenhum', 0, 3),
(NULL, 100, 0, 1000, 'Se tivesse que escolher entre um tratamento mais longo porem mais conservador e um tratamento mais rápido porem mais agressivo, qual você escolheria? ', 'texto', 'nenhum', 0, 3),
(NULL, 0, 0, 1000, 'Sobre a ATM: possui dores de cabeça; estalid; zumbido no ouvido; ranger de dente; dificuldade de abertura bucal? Descreva a frequencia e intensidade.', 'simnaotexto', 'nenhum', 0, 6),
(NULL, 0, 0, 1000, 'Passou por tratamento odontológico previo?', 'simnaotexto', 'nenhum', 0, 2),
(NULL, 0, 0, 1000, 'Como é a sua dieta? Ex: refrigerante, alimentos ácidos em geral e doces.', 'texto', 'nenhum', 0, 4),
(NULL, 0, 0, 1000, 'Tem hábito de morder objetos ou roer unha?', 'simnaotexto', 'nenhum', 0, 5),
(NULL, 0, 0, 1000, 'Alteração de Face: Dinâmica Muscular?', 'simnaotexto', 'nenhum', 0, 8),
(NULL, 0, 0, 1000, 'Alteração de Deglutição: Lingua; Lábio; Palato.', 'simnaotexto', 'nenhum', 0, 9),
(NULL, 0, 0, 1000, 'Alteração Ganglionar?', 'simnaotexto', 'nenhum', 0, 10),
(NULL, 0, 0, 1000, 'Qual a sua queixa Principal?', 'texto', 'nenhum', 0, 1),
(NULL, 0, 0, 1001, 'Sente alguma dor nos dentes ou na boca?', 'simnaotexto', 'nenhum', 0, 0),
(NULL, 0, 0, 1001, 'Sua gengiva sangra com a escova e/ou fio dental?', 'simnao', '', 0, 0),
(NULL, 0, 0, 1001, 'Relate a sua escovação diária: quantidade de vezes; uso de creme dental;  uso de fio dental. ', 'texto', 'nenhum', 0, 0),
(NULL, 0, 0, 1001, 'Possui alergia a algum medicamento, produto e/ou  alimento? Se sim, quais?', 'simnaotexto', 'sim', 1, 0),
(NULL, 0, 0, 1001, 'Está sob tratamento médico eu/ou possui alguma alteração sistêmica?  Pulmonar; Cardíaca; Vascular; Digestória; Renal; Endocrina; Infectocontagiosa. Descreva com detalhes.', 'simnaotexto', 'sim', 1, 0),
(NULL, 0, 0, 1001, 'Faz uso de algum medicamento?  Se sim, quais? Com qual frequência? Incluindo Anticoncepcional', 'simnaotexto', 'sim', 1, 0),
(NULL, 0, 0, 1001, 'Fez alguma intervenção cirúrgica nos últimos 5 anos? Se sim qual?', 'simnaotexto', 'sim', 1, 0),
(NULL, 0, 0, 1001, 'É fumante ou alcoolista? Se sim qual a quantidade diária consumida?', 'simnaotexto', 'sim', 1, 0),
(NULL, 0, 0, 1001, 'Está Grávida e/ou Amamentando?', 'simnaotexto', 'sim', 0, 0),
(NULL, 0, 0, 1002, 'O que você gostaria de melhorar com a HOF?', 'texto', 'nenhum', 0, 1),
(NULL, 0, 0, 1002, 'Tem muita exposição solar?', 'simnaotexto', 'nenhum', 0, 2),
(NULL, 0, 0, 1002, 'Apresenta mancha na pele?', 'simnaotexto', 'nenhum', 0, 3),
(NULL, 0, 0, 1002, 'Usa filtro solar regularmente? Se sim, qual?', 'simnaotexto', 'nenhum', 0, 4),
(NULL, 0, 0, 1002, 'Faz uso de produtos de cuidado com a pele diariamente? Se sim, qual?', 'simnaotexto', 'nenhum', 0, 5),
(NULL, 0, 0, 1002, 'Apresenta flacidez na pele?', 'simnaotexto', 'nenhum', 0, 6),
(NULL, 0, 0, 1002, 'Apresenta lesões de Acne ativa?', 'simnaotexto', 'nenhum', 0, 7),
(NULL, 0, 0, 1002, 'Já realizou algum tratamento estético facial ou de pele?', 'simnaotexto', 'nenhum', 0, 8),
(NULL, 0, 0, 1002, 'Já se submeteu a tratamento com preenchedores não reabsorvíeis, PMMA?', 'simnaotexto', 'nenhum', 0, 9),
(NULL, 0, 0, 1002, 'Fez uso de ácido ou algum peeling químico?', 'simnaotexto', '', 0, 10);



