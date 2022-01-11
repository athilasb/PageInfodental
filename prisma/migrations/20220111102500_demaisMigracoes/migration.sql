
CREATE TABLE `ident_financeiro_conciliacoes` (
  `id` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `id_transferencia` int(11) NOT NULL,
  `id_extrato` int(11) NOT NULL,
  `id_fluxo` int(11) NOT NULL,
  `valor` double NOT NULL,
  `multiplo` tinyint(1) NOT NULL,
  `movimentacaoConciliacao` tinyint(1) NOT NULL COMMENT 'conciliacao realizada a partir de uma movimentacao'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ident_financeiro_extrato` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data_extrato` date NOT NULL,
  `ajuste` tinyint(1) NOT NULL,
  `uniqueid` bigint(20) NOT NULL,
  `checknumber` varchar(50) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `descricao` varchar(250) NOT NULL,
  `valor` double NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `id_conta` int(11) NOT NULL,
  `id_despesa` int(11) NOT NULL,
  `juros` double NOT NULL,
  `multa` double NOT NULL,
  `desconto` double NOT NULL,
  `obs` text NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_fluxo_criacao` int(11) NOT NULL COMMENT 'Fluxo que criou este extrato/movimentacao',
  `transferencia` int(11) NOT NULL COMMENT 'se foi uma transferencia',
  `id_transferencia` int(11) NOT NULL COMMENT 'Transferencias que este extrato/movimentacao criou',
  `id_ofx` int(11) NOT NULL COMMENT 'OFX que originalizou esta movimentacao/extrato'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ident_financeiro_extrato_ofx` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_conta` int(11) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `saldo` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ident_pacientes_evolucoes_laboratorio_arquivos` (
  `id` int(11) NOT NULL,
  `lixo` varchar(10) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `arquivo` varchar(250) NOT NULL,
  `id_conteudo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `ident_pacientes_evolucoes_laboratorio_arquivos_conteudos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `id_conteudo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `ident_pacientes_evolucoes_laboratorio_checklist` (
  `id` int(11) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `id_modelodetrabalho` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ident_pacientes_evolucoes_laboratorio_checklist_modelos` (
  `id` int(11) NOT NULL,
  `id_modelo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `ident_pacientes_evolucoes_laboratorio_historico` (
  `id` int(11) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensagem` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `ident_pacientes_evolucoes_laboratorio_servicos` (
  `id` int(11) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `id_procedimento` int(11) NOT NULL,
  `id_opcao` int(11) NOT NULL,
  `opcao` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `ident_pacientes_historico` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `evento` enum('agendaStatus','agendaHorario','agendaNovo','observacao','relacionamento') NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_agenda` int(11) NOT NULL,
  `id_status_novo` int(11) NOT NULL,
  `id_status_antigo` int(11) NOT NULL,
  `agenda_data_novo` datetime NOT NULL,
  `agenda_data_antigo` datetime NOT NULL,
  `descricao` varchar(150) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_obs` int(11) NOT NULL,
  `relacionamento_momento` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `ident_pacientes_tratamentos_procedimentos_evolucao` (
  `id` int(11) NOT NULL,
  `id_tratamento_procedimento` int(11) NOT NULL COMMENT 'pacientes_tratamentos_procedimentos',
  `id_paciente` int(11) NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `id_procedimento` int(11) NOT NULL,
  `status_evolucao` enum('iniciar','iniciado','finalizado','cancelado') NOT NULL,
  `numero` int(11) NOT NULL,
  `numeroTotal` int(11) NOT NULL COMMENT 'quantidade de procedimento',
  `lixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `ident_pacientes_tratamentos_procedimentos_evolucao_historico` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(150) NOT NULL,
  `obs` text NOT NULL,
  `id_tratamento_procedimento` int(11) NOT NULL COMMENT 'procedimento aprovado',
  `id_procedimento_aevoluir` int(11) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `ident_financeiro_conciliacoes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_financeiro_extrato`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_empresa` (`id_unidade`),
  ADD KEY `valor` (`valor`),
  ADD KEY `tipo` (`tipo`),
  ADD KEY `uniqueid` (`uniqueid`),
  ADD KEY `id_conta` (`id_conta`),
  ADD KEY `id_despesa` (`id_despesa`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `data_extrato` (`data_extrato`);

ALTER TABLE `ident_financeiro_extrato_ofx`
  ADD PRIMARY KEY (`id`);



ALTER TABLE `ident_pacientes_evolucoes_laboratorio_arquivos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_laboratorio_arquivos_conteudos`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_arquivos_conteudos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_laboratorio_checklist`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_checklist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_laboratorio_checklist_modelos`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_checklist_modelos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_laboratorio_historico`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_historico`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_laboratorio_servicos`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_servicos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_historico`
--
ALTER TABLE `ident_pacientes_historico`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_tratamentos_procedimentos_evolucao`
--
ALTER TABLE `ident_pacientes_tratamentos_procedimentos_evolucao`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_tratamentos_procedimentos_evolucao_historico`
--
ALTER TABLE `ident_pacientes_tratamentos_procedimentos_evolucao_historico`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ident_financeiro_conciliacoes`
--
ALTER TABLE `ident_financeiro_conciliacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_financeiro_extrato`
--
ALTER TABLE `ident_financeiro_extrato`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_financeiro_extrato_ofx`
--
ALTER TABLE `ident_financeiro_extrato_ofx`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_laboratorio_arquivos`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_arquivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_laboratorio_arquivos_conteudos`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_arquivos_conteudos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_laboratorio_checklist`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_checklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_laboratorio_checklist_modelos`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_checklist_modelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_laboratorio_historico`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_laboratorio_servicos`
--
ALTER TABLE `ident_pacientes_evolucoes_laboratorio_servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_historico`
--
ALTER TABLE `ident_pacientes_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_tratamentos_procedimentos_evolucao`
--
ALTER TABLE `ident_pacientes_tratamentos_procedimentos_evolucao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_tratamentos_procedimentos_evolucao_historico`
--
ALTER TABLE `ident_pacientes_tratamentos_procedimentos_evolucao_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
