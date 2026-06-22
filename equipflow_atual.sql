-- --------------------------------------------------------
-- Anfitrião:                    vsgate-s1.dei.isep.ipp.pt
-- Versão do servidor:           8.0.45 - MySQL Community Server - GPL
-- SO do servidor:               Linux
-- HeidiSQL Versão:              12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- A despejar estrutura para tabela db1241677.acessorios
CREATE TABLE IF NOT EXISTS `acessorios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_equipamento_pai` int NOT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_bin NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `marca` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `fabricante` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
  `numero_serie` varchar(30) COLLATE utf8mb4_bin NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_bin NOT NULL DEFAULT 'ativo',
  `observacoes` text COLLATE utf8mb4_bin,
  `apagado` tinyint(1) NOT NULL DEFAULT '0',
  `data_apagado` datetime DEFAULT NULL,
  `id_utilizador_apagou` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_acessorio_pai` (`id_equipamento_pai`),
  KEY `fk_acessorio_utilizador` (`id_utilizador_apagou`),
  CONSTRAINT `fk_acessorio_pai` FOREIGN KEY (`id_equipamento_pai`) REFERENCES `equipamentos` (`id`),
  CONSTRAINT `fk_acessorio_utilizador` FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

-- A despejar estrutura para tabela db1241677.consumiveis
CREATE TABLE IF NOT EXISTS `consumiveis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_equipamento_pai` int NOT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_bin NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `quantidade` int NOT NULL DEFAULT '0',
  `id_fornecedor` int NOT NULL,
  `observacoes` text COLLATE utf8mb4_bin,
  `apagado` tinyint(1) NOT NULL DEFAULT '0',
  `data_apagado` datetime DEFAULT NULL,
  `id_utilizador_apagou` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_consumivel_pai` (`id_equipamento_pai`),
  KEY `fk_consumivel_fornecedor` (`id_fornecedor`),
  CONSTRAINT `fk_consumivel_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`),
  CONSTRAINT `fk_consumivel_pai` FOREIGN KEY (`id_equipamento_pai`) REFERENCES `equipamentos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

-- A despejar estrutura para tabela db1241677.documentos
CREATE TABLE IF NOT EXISTS `documentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_equipamento` int NOT NULL,
  `id_fornecedor` int DEFAULT NULL,
  `tipo_documento` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `nome_documento` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `data_documento` date NOT NULL,
  `data_validade` date DEFAULT NULL,
  `caminho_ficheiro` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `apagado` tinyint(1) NOT NULL DEFAULT '0',
  `data_apagado` date DEFAULT NULL,
  `id_utilizador_apagou` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_documentos_equipamento` (`id_equipamento`),
  KEY `fk_documentos_fornecedor` (`id_fornecedor`),
  KEY `fk_documentos_utilizador` (`id_utilizador_apagou`),
  CONSTRAINT `fk_documentos_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`),
  CONSTRAINT `fk_documentos_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`),
  CONSTRAINT `fk_documentos_utilizador` FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`),
  CONSTRAINT `chk_documentos_tipo_documento` CHECK ((`tipo_documento` in (_utf8mb4'manual',_utf8mb4'certificado_calibracao',_utf8mb4'fatura',_utf8mb4'ficha_tecnica',_utf8mb4'certificado_conformidade',_utf8mb4'outro')))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

-- A despejar estrutura para tabela db1241677.equipamentos
CREATE TABLE IF NOT EXISTS `equipamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo_interno` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `designacao` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `categoria` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `marca` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `numero_serie` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `fabricante` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `data_aquisicao` date NOT NULL,
  `ano_fabrico` int NOT NULL,
  `custo_aquisicao` decimal(10,2) DEFAULT NULL,
  `tipo_entrada` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `criticidade` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `observacoes` text COLLATE utf8mb4_bin,
  `id_localizacao` int NOT NULL,
  `apagado` tinyint(1) NOT NULL DEFAULT '0',
  `data_apagado` date DEFAULT NULL,
  `id_utilizador_apagou` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_interno` (`codigo_interno`),
  KEY `fk_equipamentos_localizacao` (`id_localizacao`),
  KEY `fk_equipamentos_utilizador` (`id_utilizador_apagou`),
  CONSTRAINT `fk_equipamentos_localizacao` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id`),
  CONSTRAINT `fk_equipamentos_utilizador` FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`),
  CONSTRAINT `chk_equipamentos_categoria` CHECK ((`categoria` in (_utf8mb4'monitorizacao',_utf8mb4'suporte_vida',_utf8mb4'terapia',_utf8mb4'diagnostico',_utf8mb4'laboratorio',_utf8mb4'esterilizacao',_utf8mb4'reabilitacao'))),
  CONSTRAINT `chk_equipamentos_criticidade` CHECK ((`criticidade` in (_utf8mb4'baixa',_utf8mb4'media',_utf8mb4'alta',_utf8mb4'suporte_vida'))),
  CONSTRAINT `chk_equipamentos_custo` CHECK ((`custo_aquisicao` > 0)),
  CONSTRAINT `chk_equipamentos_data_apagado_aquisicao` CHECK ((`data_apagado` >= `data_aquisicao`)),
  CONSTRAINT `chk_equipamentos_estado` CHECK ((`estado` in (_utf8mb4'ativo',_utf8mb4'manutencao',_utf8mb4'inativo',_utf8mb4'calibracao',_utf8mb4'quarentena',_utf8mb4'abatido'))),
  CONSTRAINT `chk_equipamentos_tipo_entrada` CHECK ((`tipo_entrada` in (_utf8mb4'compra',_utf8mb4'doacao',_utf8mb4'aluguer',_utf8mb4'emprestimo')))
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

-- A despejar estrutura para tabela db1241677.equipamento_fornecedor
CREATE TABLE IF NOT EXISTS `equipamento_fornecedor` (
  `id_equipamento` int NOT NULL,
  `id_fornecedor` int NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id_equipamento`,`id_fornecedor`),
  KEY `fk_equipamento_fornecedor_fornecedor` (`id_fornecedor`),
  CONSTRAINT `fk_equipamento_fornecedor_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`),
  CONSTRAINT `fk_equipamento_fornecedor_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`),
  CONSTRAINT `chk_equipamento_fornecedor_tipo` CHECK ((`tipo` in (_utf8mb4'fabricante',_utf8mb4'distribuidor',_utf8mb4'assistencia',_utf8mb4'consumiveis')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

-- A despejar estrutura para tabela db1241677.fornecedores
CREATE TABLE IF NOT EXISTS `fornecedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_empresa` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `nif` varchar(9) COLLATE utf8mb4_bin NOT NULL,
  `morada` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `website` varchar(150) COLLATE utf8mb4_bin DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `pessoa_contacto` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `telefone_pessoa_contacto` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `observacoes` text COLLATE utf8mb4_bin,
  `apagado` tinyint(1) NOT NULL DEFAULT '0',
  `data_apagado` date DEFAULT NULL,
  `id_utilizador_apagou` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nif` (`nif`),
  KEY `fk_fornecedores_utilizador` (`id_utilizador_apagou`),
  CONSTRAINT `fk_fornecedores_utilizador` FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`),
  CONSTRAINT `chk_fornecedores_tipo` CHECK ((`tipo` in (_utf8mb4'fabricante',_utf8mb4'distribuidor',_utf8mb4'assistencia',_utf8mb4'consumiveis')))
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

-- A despejar estrutura para tabela db1241677.garantias_contratos
CREATE TABLE IF NOT EXISTS `garantias_contratos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_equipamento` int NOT NULL,
  `id_fornecedor` int NOT NULL,
  `data_inicio_garantia` date DEFAULT NULL,
  `data_fim_garantia` date DEFAULT NULL,
  `tem_contrato_manutencao` tinyint(1) NOT NULL DEFAULT '0',
  `tipo_contrato` varchar(50) COLLATE utf8mb4_bin DEFAULT NULL,
  `periodicidade` varchar(50) COLLATE utf8mb4_bin DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_bin,
  `caminho_ficheiro` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `apagado` tinyint(1) NOT NULL DEFAULT '0',
  `data_apagado` date DEFAULT NULL,
  `id_utilizador_apagou` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_garantias_contratos_equipamento` (`id_equipamento`),
  KEY `fk_garantias_contratos_utilizador` (`id_utilizador_apagou`),
  KEY `fk_garantias_contratos_fornecedor` (`id_fornecedor`),
  CONSTRAINT `fk_garantias_contratos_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`),
  CONSTRAINT `fk_garantias_contratos_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`),
  CONSTRAINT `fk_garantias_contratos_utilizador` FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`),
  CONSTRAINT `chk_garantias_contratos_datas` CHECK ((`data_fim_garantia` >= `data_inicio_garantia`)),
  CONSTRAINT `chk_garantias_contratos_periodicidade` CHECK ((`periodicidade` in (_utf8mb4'mensal',_utf8mb4'trimestral',_utf8mb4'semestral',_utf8mb4'anual',_utf8mb4'nao_aplicavel'))),
  CONSTRAINT `chk_garantias_contratos_tipo_contrato` CHECK ((`tipo_contrato` in (_utf8mb4'garantia_fabricante',_utf8mb4'manutencao_preventiva',_utf8mb4'manutencao_corretiva',_utf8mb4'manutencao_completa',_utf8mb4'outro')))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

-- A despejar estrutura para tabela db1241677.localizacoes
CREATE TABLE IF NOT EXISTS `localizacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `edificio` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `piso` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `servico` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `sala_internamento_gabinete` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `apagado` tinyint(1) NOT NULL DEFAULT '0',
  `data_apagado` date DEFAULT NULL,
  `id_utilizador_apagou` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_localizacoes_utilizador` (`id_utilizador_apagou`),
  CONSTRAINT `fk_localizacoes_utilizador` FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

-- A despejar estrutura para tabela db1241677.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_utilizador` int DEFAULT NULL,
  `tipo_evento` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `descricao` text COLLATE utf8mb4_bin,
  `data_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_logs_utilizador` (`id_utilizador`),
  CONSTRAINT `fk_logs_utilizador` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id`),
  CONSTRAINT `chk_logs_tipo_evento` CHECK ((`tipo_evento` in (_utf8mb4'login_sucesso',_utf8mb4'login_falhado',_utf8mb4'erro_bd',_utf8mb4'equipamento_criado',_utf8mb4'equipamento_editado',_utf8mb4'equipamento_desativado',_utf8mb4'fornecedor_criado',_utf8mb4'fornecedor_editado',_utf8mb4'fornecedor_desativado',_utf8mb4'localizacao_criada',_utf8mb4'localizacao_editada',_utf8mb4'localizacao_desativada',_utf8mb4'documento_criado',_utf8mb4'documento_desativado',_utf8mb4'garantia_criada',_utf8mb4'garantia_desativada')))
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

-- A despejar estrutura para tabela db1241677.utilizadores
CREATE TABLE IF NOT EXISTS `utilizadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `perfil` varchar(20) COLLATE utf8mb4_bin NOT NULL COMMENT 'administrador ou tecnico',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `utilizadores_index_0` (`email`),
  CONSTRAINT `chk_utilizadores_perfil` CHECK ((`perfil` in (_utf8mb4'administrador',_utf8mb4'tecnico')))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Exportação de dados não seleccionada.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

