-- Adminer 4.8.1 MySQL 10.4.32-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

SET NAMES utf8mb4;

CREATE DATABASE `gerenciador_senhas` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `gerenciador_senhas`;

CREATE TABLE `tb01_usuarios` (
  `identificador` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) NOT NULL,
  `email` varchar(150) NOT NULL,
  `palavra_passe` varchar(255) NOT NULL,
  `situacao` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`identificador`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `tb02_senhas` (
  `id_senha` int(11) NOT NULL AUTO_INCREMENT,
  `site_origem` varchar(250) NOT NULL,
  `usuario_origem` varchar(200) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `identificador` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `senha` varbinary(255) NOT NULL,
  PRIMARY KEY (`id_senha`),
  KEY `identificador` (`identificador`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `tb02_senhas_ibfk_1` FOREIGN KEY (`identificador`) REFERENCES `tb01_usuarios` (`identificador`) ON DELETE CASCADE,
  CONSTRAINT `tb02_senhas_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `tb03_categorias` (`id_categoria`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `tb03_categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `cor` varchar(6) DEFAULT '242424',
  `identificador` int(11) NOT NULL,
  PRIMARY KEY (`id_categoria`),
  KEY `identificador` (`identificador`),
  CONSTRAINT `tb03_categorias_ibfk_1` FOREIGN KEY (`identificador`) REFERENCES `tb01_usuarios` (`identificador`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 2025-11-14 02:38:34
