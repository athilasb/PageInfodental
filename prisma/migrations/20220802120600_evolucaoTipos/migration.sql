

INSERT INTO `ident_pacientes_evolucoes_tipos` (`id`, `tipo`, `titulo`, `tituloSingular`, `icone`, `pagina`) VALUES
(1, 'anamnese', 'Anamnese', 'Anamnese', 'mdi-clipboard-check-multiple-outline', 'pg_contatos_pacientes_evolucao_anamnese.php'),
(2, 'procedimentos-aprovados', 'Procedimentos Aprovados', 'Procedimento Aprovado', 'mdi-check-circle-outline', 'pg_contatos_pacientes_evolucao_procedimentos.php'),
(3, 'procedimentos-avulsos', 'Procedimentos Avulsos', 'Procedimento Avulso', 'mdi-progress-check', 'pg_contatos_pacientes_evolucao_procedimentosavulsos.php'),
(4, 'atestado', 'Atestados', 'Atestado', 'mdi-file-document-outline', 'pg_contatos_pacientes_evolucao_atestado.php'),
(5, 'servicos-de-laboratorio', 'Serviços de Laboratório', 'Serviço de Laboratório', 'entypo-lab-flask', 'pg_contatos_pacientes_evolucao_laboratorio.php'),
(6, 'pedidos-de-exames', 'Pedidos de Exames', 'Pedido de Exame', 'carbon-user-x-ray', 'pg_contatos_pacientes_evolucao_pedidosdeexame.php'),
(7, 'receituario', 'Receituário', 'Receituário', 'mdi-pill', 'pg_contatos_pacientes_evolucao_receituario.php'),
(8, 'proxima-consulta', 'Próxima Consulta', 'Próxima Consulta', 'mdi-calendar-cursor', 'pg_contatos_pacientes_evolucao_proximaconsulta.php');
COMMIT;
