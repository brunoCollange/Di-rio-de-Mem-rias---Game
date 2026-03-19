-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/03/2026 às 23:38
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `diario`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `memories`
--

CREATE TABLE `memories` (
  `id` int(11) NOT NULL,
  `spot_id` varchar(36) NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT 'Nova memória',
  `body` text DEFAULT NULL,
  `icon` varchar(10) DEFAULT '✨',
  `reversed` tinyint(1) DEFAULT 0,
  `image_path` varchar(512) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `spots`
--

CREATE TABLE `spots` (
  `id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'Novo local',
  `world_x` float NOT NULL DEFAULT 0,
  `world_y` float NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `music_path` varchar(512) DEFAULT NULL,
  `music_name` varchar(255) DEFAULT NULL,
  `is_secret` tinyint(1) DEFAULT 0,
  `secret_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `stickers`
--

CREATE TABLE `stickers` (
  `id` int(11) NOT NULL,
  `world_x` float NOT NULL DEFAULT 0,
  `world_y` float NOT NULL DEFAULT 0,
  `image_path` varchar(512) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$06$QJoL/O4yGShxPBEAXXZAxein1C1RATt.I0TzphmS2SjIgV0/0Ms6S');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `memories`
--
ALTER TABLE `memories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_memories_spot` (`spot_id`,`position`);

--
-- Índices de tabela `spots`
--
ALTER TABLE `spots`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `stickers`
--
ALTER TABLE `stickers`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `memories`
--
ALTER TABLE `memories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT de tabela `stickers`
--
ALTER TABLE `stickers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `memories`
--
ALTER TABLE `memories`
  ADD CONSTRAINT `memories_ibfk_1` FOREIGN KEY (`spot_id`) REFERENCES `spots` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
