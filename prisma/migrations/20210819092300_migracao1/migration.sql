-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 19, 2021 at 09:45 AM
-- Server version: 5.5.62-0+deb8u1
-- PHP Version: 5.6.40-0+deb8u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `infodental`
--

-- --------------------------------------------------------

--
-- Table structure for table `ident_agenda`
--

CREATE TABLE `ident_agenda` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `data_atualizacao` datetime NOT NULL,
  `agenda_data` datetime NOT NULL,
  `agenda_duracao` int(11) NOT NULL,
  `agenda_data_final` datetime NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `profissionais` varchar(250) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `id_cadeira` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `clienteChegou` tinyint(1) NOT NULL,
  `emAtendimento` tinyint(1) NOT NULL,
  `obs` text NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `procedimentos` text NOT NULL,
  `agendaPessoal` tinyint(1) NOT NULL,
  `dia_inteiro` tinyint(1) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `cancelamento_motivo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_agenda_status`
--

CREATE TABLE `ident_agenda_status` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `cor` varchar(150) NOT NULL,
  `kanban_ordem` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_cidades`
--

CREATE TABLE `ident_cidades` (
  `id` int(11) NOT NULL,
  `codigo` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `uf` char(2) NOT NULL,
  `capital` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ident_colaboradores`
--

CREATE TABLE `ident_colaboradores` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `nome` varchar(150) NOT NULL,
  `sexo` enum('M','F') NOT NULL,
  `cpf` varchar(30) NOT NULL,
  `data_nascimento` date NOT NULL,
  `rg` varchar(50) NOT NULL,
  `rg_orgaoemissor` varchar(50) NOT NULL,
  `rg_uf` varchar(10) NOT NULL,
  `estado_civil` varchar(150) NOT NULL,
  `nome_pai` varchar(150) NOT NULL,
  `nome_mae` varchar(150) NOT NULL,
  `telefone1` varchar(40) NOT NULL,
  `telefone2` varchar(40) NOT NULL,
  `email` varchar(150) NOT NULL,
  `instagram` varchar(150) NOT NULL,
  `linkedin` varchar(150) NOT NULL,
  `facebook` varchar(150) NOT NULL,
  `cep` varchar(20) NOT NULL,
  `endereco` varchar(250) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `complemento` varchar(100) NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `cidade` int(11) NOT NULL,
  `id_cidade` int(11) NOT NULL,
  `escolaridade` varchar(150) NOT NULL,
  `cro` varchar(50) NOT NULL,
  `uf_cro` varchar(10) NOT NULL,
  `tipo_cro` varchar(100) NOT NULL,
  `calendario_cor` varchar(50) NOT NULL,
  `calendario_iniciais` varchar(50) NOT NULL,
  `foto` varchar(10) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `unidades` varchar(250) NOT NULL,
  `senha` text NOT NULL,
  `permitir_acesso` tinyint(1) NOT NULL,
  `tipo` enum('admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_colaboradores_cargahoraria`
--

CREATE TABLE `ident_colaboradores_cargahoraria` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_colaborador` int(11) NOT NULL,
  `horario` int(11) NOT NULL,
  `carga_semanal` int(11) NOT NULL,
  `atendimentopersonalizado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_colaboradores_dadoscontratacao`
--

CREATE TABLE `ident_colaboradores_dadoscontratacao` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_colaborador` int(11) NOT NULL,
  `cargo` varchar(150) NOT NULL,
  `regime_contrato` varchar(150) NOT NULL,
  `salario` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_financeiro_bancosecontas`
--

CREATE TABLE `ident_financeiro_bancosecontas` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `agencia` varchar(50) NOT NULL,
  `conta` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_financeiro_fluxo`
--

CREATE TABLE `ident_financeiro_fluxo` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_origem` int(11) NOT NULL,
  `id_registro` int(11) NOT NULL,
  `data_vencimento` date NOT NULL,
  `pagamento` tinyint(1) NOT NULL,
  `pagamento_id_colaborador` int(11) NOT NULL,
  `data_efetivado` date NOT NULL,
  `id_formapagamento` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `valor` double NOT NULL,
  `obs` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_financeiro_fluxo_origens`
--

CREATE TABLE `ident_financeiro_fluxo_origens` (
  `id` int(11) NOT NULL,
  `tabela` varchar(80) NOT NULL,
  `titulo` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpages_antesedepois`
--

CREATE TABLE `ident_landingpages_antesedepois` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `foto_antes1` varchar(10) NOT NULL,
  `foto_depois1` varchar(10) NOT NULL,
  `foto_antes2` varchar(10) NOT NULL,
  `foto_depois2` varchar(10) NOT NULL,
  `nome_paciente1` varchar(150) NOT NULL,
  `nome_paciente2` varchar(150) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpages_conversao`
--

CREATE TABLE `ident_landingpages_conversao` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `id_tema` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpages_depoimentos`
--

CREATE TABLE `ident_landingpages_depoimentos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `id_tema` int(11) NOT NULL,
  `autor1` varchar(150) NOT NULL,
  `depoimento1` varchar(200) NOT NULL,
  `autor2` varchar(150) NOT NULL,
  `depoimento2` varchar(200) NOT NULL,
  `autor3` varchar(150) NOT NULL,
  `depoimento3` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_aclinica`
--

CREATE TABLE `ident_landingpage_aclinica` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `nome` varchar(150) NOT NULL,
  `foto1` varchar(10) NOT NULL,
  `legenda1` varchar(150) NOT NULL,
  `foto2` varchar(10) NOT NULL,
  `legenda2` varchar(150) NOT NULL,
  `foto3` varchar(10) NOT NULL,
  `legenda3` varchar(150) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_antesedepois`
--

CREATE TABLE `ident_landingpage_antesedepois` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `foto_antes` varchar(10) NOT NULL,
  `foto_depois` varchar(10) NOT NULL,
  `descricao` text NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_banner`
--

CREATE TABLE `ident_landingpage_banner` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_tema` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `palavras` varchar(250) NOT NULL,
  `descricao` varchar(250) NOT NULL,
  `foto` varchar(10) NOT NULL,
  `video` text NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `data_alteracao` datetime NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_captacao`
--

CREATE TABLE `ident_landingpage_captacao` (
  `id` int(11) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `texto` text NOT NULL,
  `foto` varchar(10) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_captacao_abandono`
--

CREATE TABLE `ident_landingpage_captacao_abandono` (
  `id` int(11) NOT NULL,
  `pub` tinyint(1) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `texto` text NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `foto` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_depoimentos`
--

CREATE TABLE `ident_landingpage_depoimentos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `id_tema` int(11) NOT NULL,
  `autor` varchar(150) NOT NULL,
  `depoimento` text NOT NULL,
  `video` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_formulario`
--

CREATE TABLE `ident_landingpage_formulario` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `ip` varchar(30) NOT NULL,
  `data` datetime NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `telefone` varchar(50) NOT NULL,
  `preferencia` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `obs` text NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `tipo` enum('','abandono','captacao') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_informacoes_apresentacao`
--

CREATE TABLE `ident_landingpage_informacoes_apresentacao` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `texto` text NOT NULL,
  `titulo_destaque` varchar(250) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_informacoes_destaques`
--

CREATE TABLE `ident_landingpage_informacoes_destaques` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `descricao` text NOT NULL,
  `foto` varchar(10) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_sobreaclinica`
--

CREATE TABLE `ident_landingpage_sobreaclinica` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `telefone` varchar(50) NOT NULL,
  `whatsapp` varchar(50) NOT NULL,
  `instagram` varchar(50) NOT NULL,
  `facebook` varchar(50) NOT NULL,
  `twitter` varchar(50) NOT NULL,
  `linkedin` varchar(50) NOT NULL,
  `texto` text NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `endereco` varchar(150) NOT NULL,
  `mapa` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_landingpage_temas`
--

CREATE TABLE `ident_landingpage_temas` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `pub` tinyint(1) NOT NULL,
  `code` varchar(250) CHARACTER SET latin1 NOT NULL,
  `titulo` varchar(150) CHARACTER SET latin1 NOT NULL,
  `cor_primaria` varchar(50) CHARACTER SET latin1 NOT NULL,
  `cor_secundaria` varchar(50) CHARACTER SET latin1 NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `descricao` varchar(150) CHARACTER SET latin1 NOT NULL,
  `menu` varchar(150) CHARACTER SET latin1 NOT NULL,
  `whatsapp_banner` varchar(10) CHARACTER SET latin1 NOT NULL,
  `whatsapp_mensagem` varchar(250) COLLATE utf8mb4_bin NOT NULL,
  `codigo_head` text COLLATE utf8mb4_bin NOT NULL,
  `codigo_body` text COLLATE utf8mb4_bin NOT NULL,
  `codigo_conversao` text COLLATE utf8mb4_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `ident_log`
--

CREATE TABLE `ident_log` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('-','select','insert','update','delete') NOT NULL,
  `vsql` text NOT NULL,
  `vwhere` varchar(250) NOT NULL,
  `tabela` varchar(100) NOT NULL,
  `id_reg` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ident_logins`
--

CREATE TABLE `ident_logins` (
  `id` int(11) NOT NULL,
  `erro` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `ip` varchar(30) NOT NULL,
  `cpf` varchar(50) NOT NULL,
  `senha` varchar(50) NOT NULL,
  `ip_lan` varchar(50) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `frentedeloja` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ident_log_sessoes`
--

CREATE TABLE `ident_log_sessoes` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `ip` varchar(30) NOT NULL,
  `ip_lan` varchar(30) NOT NULL,
  `pagina` text NOT NULL,
  `frentedeloja` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ident_medicamentos`
--

CREATE TABLE `ident_medicamentos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes`
--

CREATE TABLE `ident_pacientes` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `paciente` tinyint(1) NOT NULL,
  `situacao` enum('BI','EXCLUIDO') NOT NULL,
  `sexo` enum('M','F') NOT NULL,
  `cpf` varchar(30) NOT NULL,
  `data_nascimento` date NOT NULL,
  `rg` varchar(50) NOT NULL,
  `rg_orgaoemissor` varchar(50) NOT NULL,
  `rg_uf` varchar(10) NOT NULL,
  `profissao` int(11) NOT NULL,
  `estado_civil` varchar(150) NOT NULL,
  `telefone1` varchar(40) NOT NULL,
  `telefone1_whatsapp` tinyint(1) NOT NULL,
  `telefone1_whatsapp_permissao` tinyint(1) NOT NULL,
  `telefone2` varchar(40) NOT NULL,
  `email` varchar(150) NOT NULL,
  `instagram` varchar(150) NOT NULL,
  `instagram_naopossui` tinyint(1) NOT NULL,
  `musica` varchar(150) NOT NULL,
  `indicacao_tipo` enum('PACIENTE','PROFISSIONAL','INDICACAO') NOT NULL,
  `indicacao` varchar(150) NOT NULL,
  `cep` varchar(20) NOT NULL,
  `endereco` varchar(250) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `complemento` varchar(100) NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `cidade` int(11) NOT NULL,
  `id_cidade` int(11) NOT NULL,
  `lat` varchar(150) NOT NULL,
  `lng` varchar(150) NOT NULL,
  `responsavel_possui` tinyint(1) NOT NULL,
  `responsavel_nome` varchar(150) NOT NULL,
  `responsavel_sexo` enum('M','F') NOT NULL,
  `responsavel_datanascimento` date NOT NULL,
  `responsavel_rg` varchar(50) NOT NULL,
  `responsavel_rg_orgaoemissor` varchar(50) NOT NULL,
  `responsavel_rg_uf` varchar(10) NOT NULL,
  `responsavel_rg_estado` varchar(15) NOT NULL,
  `responsavel_cpf` varchar(30) NOT NULL,
  `responsavel_telefone` varchar(40) NOT NULL,
  `responsavel_profissao` int(11) NOT NULL,
  `responsavel_estado_civil` varchar(150) NOT NULL,
  `responsavel_grauparentesco` varchar(150) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `foto` varchar(10) NOT NULL,
  `unidades` varchar(250) NOT NULL,
  `preferencia_contato` varchar(150) NOT NULL,
  `estrangeiro` tinyint(1) NOT NULL,
  `estrangeiro_passaporte` varchar(50) NOT NULL,
  `codigo_bi` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_evolucoes`
--

CREATE TABLE `ident_pacientes_evolucoes` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `data_evolucao` date NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `obs` text NOT NULL,
  `id_anamnese` int(11) NOT NULL COMMENT 'quando tipo=anamnese',
  `id_clinica` int(11) NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `data_pedido` date NOT NULL,
  `tipo_receita` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_evolucoes_anamnese`
--

CREATE TABLE `ident_pacientes_evolucoes_anamnese` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `data_atualizacao` datetime NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `id_anamnese` int(11) NOT NULL,
  `id_pergunta` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `pergunta` varchar(250) NOT NULL,
  `json_pergunta` text NOT NULL,
  `resposta` text NOT NULL,
  `resposta_texto` text NOT NULL,
  `alerta` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_evolucoes_atestados`
--

CREATE TABLE `ident_pacientes_evolucoes_atestados` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `data_atestado` datetime NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `objetivo` varchar(80) NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `id_cid` int(11) NOT NULL,
  `dias` int(11) NOT NULL,
  `atestado` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_evolucoes_pedidosdeexames`
--

CREATE TABLE `ident_pacientes_evolucoes_pedidosdeexames` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `data_atulizacao` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `data_pedido` date NOT NULL,
  `id_clinica` int(11) NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `id_exame` int(11) NOT NULL,
  `opcao` varchar(250) NOT NULL,
  `id_opcao` varchar(150) NOT NULL,
  `status` varchar(50) NOT NULL,
  `obs` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_evolucoes_procedimentos`
--

CREATE TABLE `ident_pacientes_evolucoes_procedimentos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_tratamento` int(11) NOT NULL,
  `id_tratamento_procedimento` int(11) NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `obs` text NOT NULL,
  `plano` varchar(250) NOT NULL,
  `id_plano` int(11) NOT NULL,
  `opcao` varchar(50) NOT NULL,
  `id_opcao` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_evolucoes_receitas`
--

CREATE TABLE `ident_pacientes_evolucoes_receitas` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `data_atualizacao` datetime NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_evolucao` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `medicamento` varchar(150) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `posologia` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_evolucoes_tipos`
--

CREATE TABLE `ident_pacientes_evolucoes_tipos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `tituloSingular` varchar(100) NOT NULL,
  `icone` varchar(150) NOT NULL,
  `pagina` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_fotos`
--

CREATE TABLE `ident_pacientes_fotos` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `foto` varchar(10) NOT NULL,
  `legenda` varchar(150) NOT NULL,
  `id_paciente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_laboratorio`
--

CREATE TABLE `ident_pacientes_laboratorio` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `os` text NOT NULL,
  `pagamentos` text NOT NULL,
  `status` enum('PENDENTE','APROVADO','CANCELADO') NOT NULL,
  `id_aprovado` int(11) NOT NULL,
  `data_aprovado` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_tratamentos`
--

CREATE TABLE `ident_pacientes_tratamentos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `procedimentos` text NOT NULL,
  `pagamento` enum('avista','parcelado') NOT NULL,
  `parcelas` int(11) NOT NULL,
  `pagamentos` text NOT NULL,
  `status` enum('PENDENTE','APROVADO','CANCELADO') NOT NULL,
  `id_aprovado` int(11) NOT NULL,
  `data_aprovado` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_tratamentos_pagamentos`
--

CREATE TABLE `ident_pacientes_tratamentos_pagamentos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_alteracao` datetime NOT NULL,
  `id_usuario_alteracao` int(11) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_tratamento` int(11) NOT NULL,
  `data_vencimento` date NOT NULL,
  `valor` double NOT NULL,
  `fusao` tinyint(1) NOT NULL COMMENT 'se for um pagamento de fusao',
  `id_fusao` int(11) NOT NULL COMMENT 'se faz parte de uma fusao'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_tratamentos_pagamentos_baixas`
--

CREATE TABLE `ident_pacientes_tratamentos_pagamentos_baixas` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `data_vencimento` date NOT NULL,
  `id_pagamento` int(11) NOT NULL,
  `tipoBaixa` enum('pagamento','desconto','despesa') NOT NULL,
  `valor` double NOT NULL,
  `id_formadepagamento` int(11) NOT NULL,
  `desconto` double NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `recibo` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `lixo_id_usuario` int(11) NOT NULL,
  `obs` varchar(250) NOT NULL,
  `id_baixa` tinyint(1) NOT NULL,
  `baixa_data` datetime NOT NULL,
  `cobrarJuros` tinyint(1) NOT NULL,
  `taxa` tinyint(1) NOT NULL,
  `id_bandeira` int(11) NOT NULL,
  `id_operadora` int(11) NOT NULL,
  `parcelas` int(11) NOT NULL,
  `parcela` int(11) NOT NULL,
  `id_primeiraParcela` int(11) NOT NULL,
  `pago` tinyint(1) NOT NULL,
  `pago_data` datetime NOT NULL,
  `pago_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_pacientes_tratamentos_procedimentos`
--

CREATE TABLE `ident_pacientes_tratamentos_procedimentos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `id_tratamento` int(11) NOT NULL,
  `id_procedimento` int(11) NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `procedimento` varchar(150) NOT NULL,
  `plano` varchar(150) NOT NULL,
  `profissional` varchar(150) NOT NULL,
  `situacao` enum('aguardandoAprovacao','aprovado','naoAprovado','observado','cancelado') NOT NULL,
  `status_evolucao` enum('iniciar','iniciado','finalizado','cancelado') NOT NULL,
  `id_plano` int(11) NOT NULL,
  `valor` double NOT NULL,
  `desconto` double NOT NULL,
  `valorSemDesconto` double NOT NULL,
  `id_opcao` int(11) NOT NULL,
  `opcao` varchar(150) NOT NULL,
  `quantitativo` tinyint(1) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_alteracao` datetime NOT NULL,
  `id_usuario_alteracao` int(11) NOT NULL,
  `id_concluido` int(11) NOT NULL,
  `obs` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_anamnese`
--

CREATE TABLE `ident_parametros_anamnese` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `perguntas` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_anamnese_formulario`
--

CREATE TABLE `ident_parametros_anamnese_formulario` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `pub` tinyint(1) NOT NULL,
  `id_anamnese` int(11) NOT NULL,
  `pergunta` varchar(150) NOT NULL,
  `tipo` enum('nota','simnao','simnaotexto','texto') NOT NULL,
  `alerta` enum('nenhum','sim','nao') NOT NULL,
  `obrigatorio` tinyint(1) NOT NULL,
  `ordem` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_anamnese_formulario_opcoes`
--

CREATE TABLE `ident_parametros_anamnese_formulario_opcoes` (
  `id` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `id_formulario` int(11) NOT NULL,
  `opcao` varchar(150) NOT NULL,
  `alerta` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_bancos`
--

CREATE TABLE `ident_parametros_bancos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_cadeiras`
--

CREATE TABLE `ident_parametros_cadeiras` (
  `id` int(11) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `ordem` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_cartoes_bandeiras`
--

CREATE TABLE `ident_parametros_cartoes_bandeiras` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_cartoes_operadoras`
--

CREATE TABLE `ident_parametros_cartoes_operadoras` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `id_banco` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_cartoes_taxas`
--

CREATE TABLE `ident_parametros_cartoes_taxas` (
  `id` int(11) NOT NULL,
  `id_operadora` int(11) NOT NULL,
  `id_bandeira` int(11) NOT NULL,
  `operacao` enum('credito','debito') NOT NULL,
  `vezes` int(11) NOT NULL,
  `parcela` int(11) NOT NULL,
  `taxa` double NOT NULL,
  `prazo` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_cartoes_taxas_semjuros`
--

CREATE TABLE `ident_parametros_cartoes_taxas_semjuros` (
  `id` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `id_operadora` int(11) NOT NULL,
  `id_bandeira` int(11) NOT NULL,
  `semjuros` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_cids`
--

CREATE TABLE `ident_parametros_cids` (
  `id` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_especialidades`
--

CREATE TABLE `ident_parametros_especialidades` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_examedeimagem`
--

CREATE TABLE `ident_parametros_examedeimagem` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `id_regiao` int(11) NOT NULL,
  `clinicas` text NOT NULL,
  `obs` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_fases`
--

CREATE TABLE `ident_parametros_fases` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_formasdepagamento`
--

CREATE TABLE `ident_parametros_formasdepagamento` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `tipo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_fornecedores`
--

CREATE TABLE `ident_parametros_fornecedores` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `tipo` enum('FORNECEDOR','LABORATORIO','CLINICA','') NOT NULL,
  `tipo_pessoa` enum('PF','PJ') NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(20) NOT NULL,
  `razao_social` varchar(150) NOT NULL,
  `nome_fantasia` varchar(150) NOT NULL,
  `cnpj` varchar(30) NOT NULL,
  `responsavel` varchar(150) NOT NULL,
  `telefone1` varchar(15) NOT NULL,
  `telefone2` varchar(15) NOT NULL,
  `email` varchar(150) NOT NULL,
  `cep` varchar(20) NOT NULL,
  `logradouro` varchar(150) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `complemento` varchar(150) NOT NULL,
  `bairro` varchar(80) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `id_cidade` int(11) NOT NULL,
  `dados_bancario` text NOT NULL,
  `lat` varchar(150) NOT NULL,
  `lng` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_grauparentesco`
--

CREATE TABLE `ident_parametros_grauparentesco` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_indicacoes`
--

CREATE TABLE `ident_parametros_indicacoes` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `lixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_indicacoes_listas`
--

CREATE TABLE `ident_parametros_indicacoes_listas` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `id_indicacao` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_medicamentos`
--

CREATE TABLE `ident_parametros_medicamentos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `medida` varchar(50) NOT NULL,
  `posologia` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_planos`
--

CREATE TABLE `ident_parametros_planos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `lixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_procedimentos`
--

CREATE TABLE `ident_parametros_procedimentos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_especialidade` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `id_regiao` int(11) NOT NULL,
  `face` tinyint(1) NOT NULL,
  `garantia` int(11) NOT NULL,
  `garantia_medida` enum('anos','meses') NOT NULL,
  `descricao_adicional` tinyint(1) NOT NULL,
  `camposEvolucao` varchar(250) NOT NULL,
  `quantitativo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_procedimentos_evolucoes`
--

CREATE TABLE `ident_parametros_procedimentos_evolucoes` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `tipo` enum('text','date') NOT NULL,
  `lixo` int(11) NOT NULL,
  `ordem` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_procedimentos_fases`
--

CREATE TABLE `ident_parametros_procedimentos_fases` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `lixo` int(11) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `id_procedimento` int(11) NOT NULL,
  `id_fase` int(11) NOT NULL,
  `evolucao` varchar(250) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_procedimentos_planos`
--

CREATE TABLE `ident_parametros_procedimentos_planos` (
  `id` int(11) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `id_procedimento` int(11) NOT NULL,
  `id_plano` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `obs` varchar(250) NOT NULL,
  `valor` double NOT NULL,
  `custo` double NOT NULL,
  `comissionamento` double NOT NULL,
  `garantia` varchar(150) NOT NULL,
  `garantia_um` varchar(30) NOT NULL,
  `naopossuigarantia` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_procedimentos_regioes`
--

CREATE TABLE `ident_parametros_procedimentos_regioes` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `ordem` int(11) NOT NULL,
  `face` int(11) NOT NULL,
  `quantitativo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_procedimentos_regioes_faces`
--

CREATE TABLE `ident_parametros_procedimentos_regioes_faces` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_procedimentos_regioes_opcoes`
--

CREATE TABLE `ident_parametros_procedimentos_regioes_opcoes` (
  `id` int(11) NOT NULL,
  `id_regiao` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `permanente` tinyint(1) NOT NULL,
  `superior` tinyint(1) NOT NULL,
  `direito` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_profissoes`
--

CREATE TABLE `ident_parametros_profissoes` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_servicosdelaboratorio`
--

CREATE TABLE `ident_parametros_servicosdelaboratorio` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `id_regiao` int(11) NOT NULL,
  `tipo_material` varchar(150) NOT NULL,
  `laboratorios` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_servicosdelaboratorio_laboratorios`
--

CREATE TABLE `ident_parametros_servicosdelaboratorio_laboratorios` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_fornecedor` int(11) NOT NULL,
  `id_servicodelaboratorio` int(11) NOT NULL,
  `valor` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_parametros_servicosdelaboratorio_materiais`
--

CREATE TABLE `ident_parametros_servicosdelaboratorio_materiais` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_profissionais`
--

CREATE TABLE `ident_profissionais` (
  `id` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL,
  `nome` varchar(150) NOT NULL,
  `calendario_iniciais` varchar(50) NOT NULL,
  `calendario_cor` varchar(10) NOT NULL,
  `foto` varchar(10) NOT NULL,
  `cpf` varchar(80) NOT NULL,
  `rg` varchar(80) NOT NULL,
  `data_nascimento` date NOT NULL,
  `conselho_numero` varchar(50) NOT NULL,
  `conselho_uf` varchar(10) NOT NULL,
  `sexo` enum('M','F') NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(30) NOT NULL,
  `celular` varchar(150) NOT NULL,
  `cep` varchar(50) NOT NULL,
  `endereco` varchar(150) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `complemento` varchar(150) NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `id_cidade` int(11) NOT NULL,
  `cidade` varchar(150) NOT NULL,
  `unidades` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_profissionais_comissionamentogeral`
--

CREATE TABLE `ident_profissionais_comissionamentogeral` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `id_plano` int(11) NOT NULL,
  `tipo` enum('','valor','porcentual','horas','') NOT NULL,
  `valor` double NOT NULL,
  `abater_custos` tinyint(1) NOT NULL,
  `abater_impostos` tinyint(1) NOT NULL,
  `abater_taxas` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_profissionais_comissionamentopersonalizado`
--

CREATE TABLE `ident_profissionais_comissionamentopersonalizado` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `id_procedimento` int(11) NOT NULL,
  `id_plano` int(11) NOT NULL,
  `tipo` enum('','valor','porcentual','horas') NOT NULL,
  `valor` double NOT NULL,
  `abater_custos` tinyint(1) NOT NULL,
  `abater_impostos` tinyint(1) NOT NULL,
  `abater_taxas` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_profissionais_horarios`
--

CREATE TABLE `ident_profissionais_horarios` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `id_cadeira` int(11) NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `dia` int(11) NOT NULL,
  `inicio` time NOT NULL,
  `fim` time NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_tratamentos_procedimentos_opcoes`
--

CREATE TABLE `ident_tratamentos_procedimentos_opcoes` (
  `id` int(11) NOT NULL,
  `id_tratamento_procedimento` int(11) NOT NULL,
  `opcao` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ident_unidades`
--

CREATE TABLE `ident_unidades` (
  `id` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `logradouro` varchar(100) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `complemento` varchar(150) NOT NULL,
  `bairro` varchar(50) NOT NULL,
  `cidade` varchar(50) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `telefone` varchar(30) NOT NULL,
  `whatsapp` varchar(30) NOT NULL,
  `site` varchar(100) NOT NULL,
  `instagram` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_usuarios`
--

CREATE TABLE `ident_usuarios` (
  `id` int(1) NOT NULL,
  `lixo` int(11) NOT NULL,
  `pub` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `nome` varchar(100) NOT NULL,
  `senha` text NOT NULL,
  `tipo` enum('moderador','admin') NOT NULL,
  `email` varchar(150) NOT NULL,
  `permissoes` varchar(250) NOT NULL,
  `nunca_lixo` int(11) NOT NULL,
  `nuncalixo` int(11) NOT NULL,
  `cpf` varchar(150) NOT NULL,
  `data_nascimento` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_whatsapp_contatos`
--

CREATE TABLE `ident_whatsapp_contatos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `numero` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ident_whatsapp_instancias`
--

CREATE TABLE `ident_whatsapp_instancias` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `instancia` varchar(150) NOT NULL,
  `token` varchar(100) NOT NULL,
  `endpoint` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ident_agenda`
--
ALTER TABLE `ident_agenda`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_agenda_status`
--
ALTER TABLE `ident_agenda_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_cidades`
--
ALTER TABLE `ident_cidades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_colaboradores`
--
ALTER TABLE `ident_colaboradores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_colaboradores_cargahoraria`
--
ALTER TABLE `ident_colaboradores_cargahoraria`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_colaboradores_dadoscontratacao`
--
ALTER TABLE `ident_colaboradores_dadoscontratacao`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_financeiro_bancosecontas`
--
ALTER TABLE `ident_financeiro_bancosecontas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_financeiro_fluxo`
--
ALTER TABLE `ident_financeiro_fluxo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_financeiro_fluxo_origens`
--
ALTER TABLE `ident_financeiro_fluxo_origens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpages_antesedepois`
--
ALTER TABLE `ident_landingpages_antesedepois`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpages_conversao`
--
ALTER TABLE `ident_landingpages_conversao`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpages_depoimentos`
--
ALTER TABLE `ident_landingpages_depoimentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_aclinica`
--
ALTER TABLE `ident_landingpage_aclinica`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_antesedepois`
--
ALTER TABLE `ident_landingpage_antesedepois`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_banner`
--
ALTER TABLE `ident_landingpage_banner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_captacao`
--
ALTER TABLE `ident_landingpage_captacao`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_captacao_abandono`
--
ALTER TABLE `ident_landingpage_captacao_abandono`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_depoimentos`
--
ALTER TABLE `ident_landingpage_depoimentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_formulario`
--
ALTER TABLE `ident_landingpage_formulario`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_informacoes_apresentacao`
--
ALTER TABLE `ident_landingpage_informacoes_apresentacao`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_informacoes_destaques`
--
ALTER TABLE `ident_landingpage_informacoes_destaques`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_sobreaclinica`
--
ALTER TABLE `ident_landingpage_sobreaclinica`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_landingpage_temas`
--
ALTER TABLE `ident_landingpage_temas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_log`
--
ALTER TABLE `ident_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tipo` (`tipo`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `tabela` (`tabela`),
  ADD KEY `id_reg` (`id_reg`);

--
-- Indexes for table `ident_logins`
--
ALTER TABLE `ident_logins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_log_sessoes`
--
ALTER TABLE `ident_log_sessoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_medicamentos`
--
ALTER TABLE `ident_medicamentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes`
--
ALTER TABLE `ident_pacientes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes`
--
ALTER TABLE `ident_pacientes_evolucoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_anamnese`
--
ALTER TABLE `ident_pacientes_evolucoes_anamnese`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_atestados`
--
ALTER TABLE `ident_pacientes_evolucoes_atestados`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_pedidosdeexames`
--
ALTER TABLE `ident_pacientes_evolucoes_pedidosdeexames`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_procedimentos`
--
ALTER TABLE `ident_pacientes_evolucoes_procedimentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_receitas`
--
ALTER TABLE `ident_pacientes_evolucoes_receitas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_evolucoes_tipos`
--
ALTER TABLE `ident_pacientes_evolucoes_tipos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_fotos`
--
ALTER TABLE `ident_pacientes_fotos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_laboratorio`
--
ALTER TABLE `ident_pacientes_laboratorio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_tratamentos`
--
ALTER TABLE `ident_pacientes_tratamentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_tratamentos_pagamentos`
--
ALTER TABLE `ident_pacientes_tratamentos_pagamentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_tratamentos_pagamentos_baixas`
--
ALTER TABLE `ident_pacientes_tratamentos_pagamentos_baixas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_pacientes_tratamentos_procedimentos`
--
ALTER TABLE `ident_pacientes_tratamentos_procedimentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_anamnese`
--
ALTER TABLE `ident_parametros_anamnese`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_anamnese_formulario`
--
ALTER TABLE `ident_parametros_anamnese_formulario`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_anamnese_formulario_opcoes`
--
ALTER TABLE `ident_parametros_anamnese_formulario_opcoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_bancos`
--
ALTER TABLE `ident_parametros_bancos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_cadeiras`
--
ALTER TABLE `ident_parametros_cadeiras`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_cartoes_bandeiras`
--
ALTER TABLE `ident_parametros_cartoes_bandeiras`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_cartoes_operadoras`
--
ALTER TABLE `ident_parametros_cartoes_operadoras`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_cartoes_taxas`
--
ALTER TABLE `ident_parametros_cartoes_taxas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_cartoes_taxas_semjuros`
--
ALTER TABLE `ident_parametros_cartoes_taxas_semjuros`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_cids`
--
ALTER TABLE `ident_parametros_cids`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_especialidades`
--
ALTER TABLE `ident_parametros_especialidades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_examedeimagem`
--
ALTER TABLE `ident_parametros_examedeimagem`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_fases`
--
ALTER TABLE `ident_parametros_fases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_formasdepagamento`
--
ALTER TABLE `ident_parametros_formasdepagamento`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_fornecedores`
--
ALTER TABLE `ident_parametros_fornecedores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_grauparentesco`
--
ALTER TABLE `ident_parametros_grauparentesco`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_indicacoes`
--
ALTER TABLE `ident_parametros_indicacoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_indicacoes_listas`
--
ALTER TABLE `ident_parametros_indicacoes_listas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_medicamentos`
--
ALTER TABLE `ident_parametros_medicamentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_planos`
--
ALTER TABLE `ident_parametros_planos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_procedimentos`
--
ALTER TABLE `ident_parametros_procedimentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_procedimentos_evolucoes`
--
ALTER TABLE `ident_parametros_procedimentos_evolucoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_procedimentos_fases`
--
ALTER TABLE `ident_parametros_procedimentos_fases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_procedimentos_planos`
--
ALTER TABLE `ident_parametros_procedimentos_planos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_procedimentos_regioes`
--
ALTER TABLE `ident_parametros_procedimentos_regioes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_procedimentos_regioes_faces`
--
ALTER TABLE `ident_parametros_procedimentos_regioes_faces`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_procedimentos_regioes_opcoes`
--
ALTER TABLE `ident_parametros_procedimentos_regioes_opcoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_profissoes`
--
ALTER TABLE `ident_parametros_profissoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_servicosdelaboratorio`
--
ALTER TABLE `ident_parametros_servicosdelaboratorio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_servicosdelaboratorio_laboratorios`
--
ALTER TABLE `ident_parametros_servicosdelaboratorio_laboratorios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_parametros_servicosdelaboratorio_materiais`
--
ALTER TABLE `ident_parametros_servicosdelaboratorio_materiais`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_profissionais`
--
ALTER TABLE `ident_profissionais`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_profissionais_comissionamentogeral`
--
ALTER TABLE `ident_profissionais_comissionamentogeral`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_profissionais_comissionamentopersonalizado`
--
ALTER TABLE `ident_profissionais_comissionamentopersonalizado`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_profissionais_horarios`
--
ALTER TABLE `ident_profissionais_horarios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_tratamentos_procedimentos_opcoes`
--
ALTER TABLE `ident_tratamentos_procedimentos_opcoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_unidades`
--
ALTER TABLE `ident_unidades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_usuarios`
--
ALTER TABLE `ident_usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_whatsapp_contatos`
--
ALTER TABLE `ident_whatsapp_contatos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ident_whatsapp_instancias`
--
ALTER TABLE `ident_whatsapp_instancias`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ident_agenda`
--
ALTER TABLE `ident_agenda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_agenda_status`
--
ALTER TABLE `ident_agenda_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_cidades`
--
ALTER TABLE `ident_cidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_colaboradores`
--
ALTER TABLE `ident_colaboradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_colaboradores_cargahoraria`
--
ALTER TABLE `ident_colaboradores_cargahoraria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_colaboradores_dadoscontratacao`
--
ALTER TABLE `ident_colaboradores_dadoscontratacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_financeiro_bancosecontas`
--
ALTER TABLE `ident_financeiro_bancosecontas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_financeiro_fluxo`
--
ALTER TABLE `ident_financeiro_fluxo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_financeiro_fluxo_origens`
--
ALTER TABLE `ident_financeiro_fluxo_origens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpages_antesedepois`
--
ALTER TABLE `ident_landingpages_antesedepois`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpages_conversao`
--
ALTER TABLE `ident_landingpages_conversao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpages_depoimentos`
--
ALTER TABLE `ident_landingpages_depoimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_aclinica`
--
ALTER TABLE `ident_landingpage_aclinica`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_antesedepois`
--
ALTER TABLE `ident_landingpage_antesedepois`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_banner`
--
ALTER TABLE `ident_landingpage_banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_captacao`
--
ALTER TABLE `ident_landingpage_captacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_captacao_abandono`
--
ALTER TABLE `ident_landingpage_captacao_abandono`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_depoimentos`
--
ALTER TABLE `ident_landingpage_depoimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_formulario`
--
ALTER TABLE `ident_landingpage_formulario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_informacoes_apresentacao`
--
ALTER TABLE `ident_landingpage_informacoes_apresentacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_informacoes_destaques`
--
ALTER TABLE `ident_landingpage_informacoes_destaques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_sobreaclinica`
--
ALTER TABLE `ident_landingpage_sobreaclinica`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_landingpage_temas`
--
ALTER TABLE `ident_landingpage_temas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_log`
--
ALTER TABLE `ident_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_logins`
--
ALTER TABLE `ident_logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_log_sessoes`
--
ALTER TABLE `ident_log_sessoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_medicamentos`
--
ALTER TABLE `ident_medicamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes`
--
ALTER TABLE `ident_pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes`
--
ALTER TABLE `ident_pacientes_evolucoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_anamnese`
--
ALTER TABLE `ident_pacientes_evolucoes_anamnese`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_atestados`
--
ALTER TABLE `ident_pacientes_evolucoes_atestados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_pedidosdeexames`
--
ALTER TABLE `ident_pacientes_evolucoes_pedidosdeexames`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_procedimentos`
--
ALTER TABLE `ident_pacientes_evolucoes_procedimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_receitas`
--
ALTER TABLE `ident_pacientes_evolucoes_receitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_evolucoes_tipos`
--
ALTER TABLE `ident_pacientes_evolucoes_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_fotos`
--
ALTER TABLE `ident_pacientes_fotos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_laboratorio`
--
ALTER TABLE `ident_pacientes_laboratorio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_tratamentos`
--
ALTER TABLE `ident_pacientes_tratamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_tratamentos_pagamentos`
--
ALTER TABLE `ident_pacientes_tratamentos_pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_tratamentos_pagamentos_baixas`
--
ALTER TABLE `ident_pacientes_tratamentos_pagamentos_baixas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_pacientes_tratamentos_procedimentos`
--
ALTER TABLE `ident_pacientes_tratamentos_procedimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_anamnese`
--
ALTER TABLE `ident_parametros_anamnese`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_anamnese_formulario`
--
ALTER TABLE `ident_parametros_anamnese_formulario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_anamnese_formulario_opcoes`
--
ALTER TABLE `ident_parametros_anamnese_formulario_opcoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_bancos`
--
ALTER TABLE `ident_parametros_bancos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_cadeiras`
--
ALTER TABLE `ident_parametros_cadeiras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_cartoes_bandeiras`
--
ALTER TABLE `ident_parametros_cartoes_bandeiras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_cartoes_operadoras`
--
ALTER TABLE `ident_parametros_cartoes_operadoras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_cartoes_taxas`
--
ALTER TABLE `ident_parametros_cartoes_taxas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_cartoes_taxas_semjuros`
--
ALTER TABLE `ident_parametros_cartoes_taxas_semjuros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_cids`
--
ALTER TABLE `ident_parametros_cids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_especialidades`
--
ALTER TABLE `ident_parametros_especialidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_examedeimagem`
--
ALTER TABLE `ident_parametros_examedeimagem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_fases`
--
ALTER TABLE `ident_parametros_fases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_formasdepagamento`
--
ALTER TABLE `ident_parametros_formasdepagamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_fornecedores`
--
ALTER TABLE `ident_parametros_fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_grauparentesco`
--
ALTER TABLE `ident_parametros_grauparentesco`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_indicacoes`
--
ALTER TABLE `ident_parametros_indicacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_indicacoes_listas`
--
ALTER TABLE `ident_parametros_indicacoes_listas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_medicamentos`
--
ALTER TABLE `ident_parametros_medicamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_planos`
--
ALTER TABLE `ident_parametros_planos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_procedimentos`
--
ALTER TABLE `ident_parametros_procedimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_procedimentos_evolucoes`
--
ALTER TABLE `ident_parametros_procedimentos_evolucoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_procedimentos_fases`
--
ALTER TABLE `ident_parametros_procedimentos_fases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_procedimentos_planos`
--
ALTER TABLE `ident_parametros_procedimentos_planos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_procedimentos_regioes`
--
ALTER TABLE `ident_parametros_procedimentos_regioes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_procedimentos_regioes_faces`
--
ALTER TABLE `ident_parametros_procedimentos_regioes_faces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_procedimentos_regioes_opcoes`
--
ALTER TABLE `ident_parametros_procedimentos_regioes_opcoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_profissoes`
--
ALTER TABLE `ident_parametros_profissoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_servicosdelaboratorio`
--
ALTER TABLE `ident_parametros_servicosdelaboratorio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_servicosdelaboratorio_laboratorios`
--
ALTER TABLE `ident_parametros_servicosdelaboratorio_laboratorios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_parametros_servicosdelaboratorio_materiais`
--
ALTER TABLE `ident_parametros_servicosdelaboratorio_materiais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_profissionais`
--
ALTER TABLE `ident_profissionais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_profissionais_comissionamentogeral`
--
ALTER TABLE `ident_profissionais_comissionamentogeral`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_profissionais_comissionamentopersonalizado`
--
ALTER TABLE `ident_profissionais_comissionamentopersonalizado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_profissionais_horarios`
--
ALTER TABLE `ident_profissionais_horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_tratamentos_procedimentos_opcoes`
--
ALTER TABLE `ident_tratamentos_procedimentos_opcoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_unidades`
--
ALTER TABLE `ident_unidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_usuarios`
--
ALTER TABLE `ident_usuarios`
  MODIFY `id` int(1) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_whatsapp_contatos`
--
ALTER TABLE `ident_whatsapp_contatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ident_whatsapp_instancias`
--
ALTER TABLE `ident_whatsapp_instancias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
