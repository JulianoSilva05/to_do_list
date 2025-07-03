-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03-Jul-2025 às 22:06
-- Versão do servidor: 8.0.42
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `todo_list`
--
CREATE DATABASE todo_list;
USE todo_list;
-- --------------------------------------------------------

--
-- Estrutura da tabela `responsibles`
--

CREATE TABLE `responsibles` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `responsibles`
--

INSERT INTO `responsibles` (`id`, `name`, `created_at`) VALUES
(1, 'Matheus Oliveira', '2025-07-03 19:46:52'),
(2, 'Juliano Silva', '2025-07-03 19:46:58'),
(3, 'Marcos Alves', '2025-07-03 19:47:04'),
(4, 'Paula Fernandes', '2025-07-03 19:47:09');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `responsible` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('a_fazer','fazendo','feito') DEFAULT 'a_fazer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `responsible`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(3, 'Criar os formulários para coleta de requisitos.', 2, '2025-06-27', '2025-06-30', 'feito', '2025-07-03 19:48:04'),
(4, 'Aplicar o Formulário de Coleta de requisitos', 3, '2025-06-30', '2025-07-01', 'feito', '2025-07-03 19:54:17'),
(5, 'Analisar os requisitos', 2, '2025-07-02', '2025-07-03', 'a_fazer', '2025-07-03 19:54:44'),
(6, 'Criar o protótipo de baixa fidelidade', 2, '2025-07-08', '2025-07-09', 'a_fazer', '2025-07-03 19:55:24'),
(7, 'Criar o protótipo de alta fidelidade', 4, '2025-07-09', '2025-07-16', 'a_fazer', '2025-07-03 19:57:18'),
(8, 'Modelo conceitual para BD', 2, '2025-07-08', '2025-07-08', 'a_fazer', '2025-07-03 19:57:38'),
(9, 'Modelo Logico do banco de dados', 1, '2025-07-09', '2025-07-11', 'a_fazer', '2025-07-03 19:58:35');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `responsibles`
--
ALTER TABLE `responsibles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Índices para tabela `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_responsible_id` (`responsible`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `responsibles`
--
ALTER TABLE `responsibles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_responsible_id` FOREIGN KEY (`responsible`) REFERENCES `responsibles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
