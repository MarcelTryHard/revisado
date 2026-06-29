-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/05/2026 às 16:54
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `elite`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cliente_nome` varchar(100) NOT NULL,
  `cliente_email` varchar(100) NOT NULL,
  `cliente_telefone` varchar(20) NOT NULL,
  `servico` varchar(50) NOT NULL,
  `data` date NOT NULL,
  `horario` time NOT NULL,
  `status` enum('agendado','cancelado','concluido') DEFAULT 'agendado',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `usuario_id`, `cliente_nome`, `cliente_email`, `cliente_telefone`, `servico`, `data`, `horario`, `status`, `created_at`) VALUES
(1, 1, 'pedro', 'pedro@gmail.com', '45555555', 'corte', '2026-04-17', '12:00:00', 'cancelado', '2026-04-10 11:39:44'),
(2, 2, 'Igor', 'igor@gmail.com', '45555555', 'combo', '2026-04-22', '11:00:00', 'agendado', '2026-04-10 11:44:45'),
(3, 3, 'JOAO VITOR', 'pedro@gmail.com', '45555555', 'barba', '2026-05-07', '12:00:00', 'agendado', '2026-05-05 13:21:49'),
(4, 4, 'JOAO VITOR', 'pedro@gmail.com', '45555555', 'corte', '2026-05-27', '17:00:00', 'agendado', '2026-05-25 14:49:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('cliente','admin') DEFAULT 'cliente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `created_at`) VALUES
(1, 'pedro', 'pedro@gmail.com', '$2y$10$QN8OFYDd19XH6L2vSHNGzuBEVye6F06JSVW0thQdOw4IwgyLoo2HC', 'cliente', '2026-04-10 11:38:31'),
(2, 'joao', 'joaovictor@gmail.com', '$2y$10$Fa8.OlR2NcR.eHsvnv6al.ooHE9K5WG1ukDgBKf/b.XzOYTYFKIda', 'cliente', '2026-04-10 11:43:38'),
(3, 'marcel', 'marcel@gmail.com', '$2y$10$ZUnzIL4L4B2.GJUetO9my.3Jd2tpwOY7L4i1IIBvACZFkGPkhrWMW', 'admin', '2026-05-05 13:20:31'),
(4, 'APARECIDA DA SILVA FERREIRA', 'aparecida.ferreira@gmail.com', '$2y$10$sfLvw3VfyHO5rb7u8UqC/.xDABqtlNZYioKlYj50QHSLSLJ.C.s8.', 'cliente', '2026-05-25 14:34:00'),
(5, 'Pati', 'pati@gmail.com', '$2y$10$TNcSfffiA5Jn5TC3Rc67TefB1b./UP52j7RQwJRpjztRa.HDeE0NC', 'cliente', '2026-05-25 14:51:03');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
