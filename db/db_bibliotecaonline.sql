-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 22/06/2026 às 22:36
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
-- Banco de dados: `db_bibliotecaonline`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `livro_id` int(11) NOT NULL,
  `data_favoritado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_leitura`
--

CREATE TABLE `historico_leitura` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_livro` int(11) NOT NULL,
  `status_origem` varchar(50) DEFAULT NULL,
  `status_destino` varchar(50) NOT NULL,
  `data_evento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `listas_leitura`
--

CREATE TABLE `listas_leitura` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_livro` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `livros`
--

CREATE TABLE `livros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `autor` varchar(255) DEFAULT NULL,
  `genero` varchar(100) DEFAULT NULL,
  `ano` int(11) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `capa` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `livros`
--

INSERT INTO `livros` (`id`, `titulo`, `autor`, `genero`, `ano`, `descricao`, `capa`) VALUES
(1, 'O Senhor dos Anéis: A Sociedade do Anel', 'J. R. R. Tolkien', 'Fantasia', 1954, 'A história começa quando o hobbit Frodo herda um Anel mágico, capaz de dominar a Terra-Média — ele e um grupo de companheiros partem para destruir esse anel antes que ele caia nas mãos do mal. Uma jornada épica repleta de magia, amizade, coragem e sacrifício.', 'https://i.imgur.com/6LfAZdB.jpg'),
(3, 'As Crônicas de Nárnia: O Leão, a Feiticeira e o Guarda-Roupa', 'C. S. Lewis', 'Fantasia', 1950, 'Quatro irmãos descobrem uma porta mágica num guarda-roupa que os leva ao reino encantado de Nárnia — lá, animais falam, feitiços existem, e eles vivem uma aventura mágica e cheia de ensinamentos sobre coragem, amizade e justiça', 'https://i.imgur.com/tvlbwVM.jpg'),
(5, 'O Feiticeiro de Terramar', 'Ursula K. Le Guin', 'Fantasia', 1968, 'Conta a história de Ged, um jovem mago de um arquipélago mágico, que comete um erro grave ao mexer com forças proibidas — para corrigir, ele precisa embarcar numa jornada de autoconhecimento e enfrentar medos e sombras internas. Uma fantasia profunda sobre poder, equilíbrio e identidade.', 'https://i.imgur.com/5PVcCq5.jpg'),
(6, 'As Crônicas de Gelo e Fogo: A Guerra dos Tronos', 'George R. R. Martin', 'Fantasia', 1996, 'Num mundo medieval repleto de intrigas políticas, guerras e disputas de poder entre famílias nobres, o livro apresenta várias tramas interligadas, personagens complexos e dilemas morais — ideal pra quem gosta de fantasia com realismo, drama e reviravoltas.', 'https://i.imgur.com/dvL6C5S.jpg'),
(7, 'O Hobbit', 'J. R. R. Tolkien', 'Fantasia', 1937, 'A aventura de Bilbo Bolseiro, um hobbit pacato, que se vê envolvido numa jornada com anões, dragões e tesouros — uma história mais leve e acessível, perfeita como porta de entrada para o universo da fantasia épica.', 'https://i.imgur.com/JgYx6kn.jpg'),
(8, 'A História Sem Fim', 'Michael Ende', 'Fantasia', 1979, 'Um clássico da fantasia que mistura real e fantástico: o jovem Bastian descobre um livro mágico e, ao lê-lo, se vê transportado para um mundo de fantasia onde suas escolhas influenciam a história — uma ode à imaginação, ao poder das histórias e à relação entre leitor e narrativa.', 'https://i.imgur.com/QK0Yvkz.jpg'),
(9, 'A Bússola de Ouro', 'Philip Pullman', 'Fantasia', 1995, 'Em um universo paralelo onde ciência, magia e aventura se misturam, Lyra — a protagonista — embarca numa jornada perigosa para desvendar mistérios, enfrentar instituições poderosas e salvar amigos. Mistura fantasia, filosofia e muita ação.', 'https://i.imgur.com/hr67Aww.jpg'),
(10, 'Mistborn: Nascidos da Bruma', 'Brandon Sanderson', 'Fantasia', 2006, 'Num mundo onde o \"senhor das trevas\" domina com punho de ferro e magia existe através de metais, um grupo improvável planeja derrubar o império. Mistborn mistura ação, magia original e reviravoltas — ótimo para quem gosta de fantasia moderna e intensa.', 'https://i.imgur.com/CLXj7dZ.jpg'),
(11, 'It', 'Stephen King', 'Terror', 1986, 'Um grupo de sete crianças (o “Losers’ Club”) enfrenta uma entidade maligna que se alimenta de seus medos — e que geralmente assume a forma de um palhaço chamado Pennywise. A história alterna entre a infância dos personagens e a fase adulta, retratando como o medo e o trauma moldam suas vidas.', 'https://i.imgur.com/3Lw3IXv.jpg'),
(12, 'O Iluminado', 'Stephen King', 'Terror', 1977, 'Jack Torrance aceita ser zelador de inverno no isolado “Hotel Overlook” com sua esposa e filho. Lá, forças sobrenaturais e o passado sombrio do hotel começam a manifestar-se, influenciando a mente de Jack — enquanto o filho Danny possui um dom psíquico que o faz perceber os horrores ocultos do lugar. Uma obra-prima do terror psicológico.', 'https://i.imgur.com/2Xv0ei6.jpg'),
(13, 'A Assombração da Casa da Colina', 'Shirley Jackson', 'Terror', 1959, 'Um clássico do terror gótico/sobrenatural que narra os eventos perturbadores vividos por personagens que investigam a misteriosa “Hill House”. O horror, mais do que monstros visíveis, está nas tensões psicológicas, medos íntimos e no ambiente opressivo que a mansão impõe.', 'https://i.imgur.com/zUOWQrj.jpg'),
(14, 'Ghost Story', 'Peter Straub', 'Terror', 1979, 'A história gira em torno de um grupo de velhos amigos que se reúnem para contar histórias de terror — o “Chowder Society”. Ao longo da narrativa, segredos sombrios e um passado obscuro começam a emergir, e o terror sobrenatural se mistura com culpa e memórias reprimidas.', 'https://i.imgur.com/2nYsqC3.jpg'),
(15, 'O Exorcista', 'William Peter Blatty', 'Terror', 1971, 'Uma menina de 12 anos é possuída por uma entidade demoníaca. Após falharem tratamentos médicos e psiquiátricos, sua mãe recorre à Igreja, e dois padres enfrentam o mal em um exorcismo marcado por horror físico e espiritual. Uma obra que mistura terror, religião e a fragilidade da sanidade humana.', 'https://i.imgur.com/BNbnEeG.jpg'),
(16, 'Drácula', 'Bram Stoker', 'Terror', 1897, 'Um clássico do terror gótico e da literatura vampírica. Através de diários, cartas e relatos, a obra narra a chegada do conde vampiro à Inglaterra e os esforços de um grupo — liderado por Van Helsing — para deter seu reinado de horror. Influenciou profundamente todo o imaginário de vampiros que conhecemos.', 'https://i.imgur.com/00J5fKP.jpg'),
(17, 'Frankenstein', 'Mary Shelley', 'Terror', 1818, 'Clássico e precursor do terror/científico-gótico. A história de Victor Frankenstein e sua criação questiona os limites da ciência, da criação e da moralidade — ao mesmo tempo em que explora o horror de lidar com algo monstruoso nascido das próprias mãos do homem.', 'https://i.imgur.com/5Huq0IC.jpg'),
(18, 'O Rei de Amarelo', 'Robert W. Chambers', 'Terror', 1895, 'Uma coletânea de dez contos — abordando elementos de terror sobrenatural ou “weird fiction”. O livro leva o nome de uma peça teatral proibida que induz ao desespero ou à loucura aqueles que a leem., que reaparece como tema em algumas das histórias. As histórias evocam horror e estranheza, com elementos de decadência, loucura e mistério sobrenatural. Uma obra histórica que influenciou a estética de horror do final do século XIX e início do século XX.', 'https://i.imgur.com/QJj7Qzn.png?1'),
(19, 'Sempre vivemos no castelo', 'Shirley Jackson', 'Terror', 1962, 'Merricat Blackwood vive com a irmã Constance e o tio Julian. Há algum tempo existiam sete membros na família Blackwood, até que uma dose fatal de arsênico colocada no pote de açúcar matou quase todos. Acusada e posteriormente inocentada pelas mortes, Constance volta para a casa da família, onde Merricat a protege da hostilidade dos habitantes da cidade.\r\n\r\nOs três vivem isolados e felizes, até que o primo Charles resolve fazer uma visita que quebra o frágil equilíbrio encontrado pelas irmãs Blakcwood. Merricat é a única que pressente o iminente perigo desse distúrbio, e fará o que for necessário para proteger Constance.\r\n\r\nEmbora às vezes classificado mais como “mistério/gótico” do que terror puro, o clima da história — de isolamento, segredos familiares e estranheza — o torna uma leitura muito atmosférica e perturbadora. A narrativa em primeira pessoa cria uma sensação constante de desconforto.', 'https://i.imgur.com/NtE9KYq.jpg'),
(20, 'Hell House: A casa do Inferno', 'Richard Matheson', 'Terror', 1971, 'Um grupo investiga uma casa assombrada, conhecida por sua longa história de violência e fenômenos paranormais. A obra mistura horror sobrenatural com suspense e medo psicológico, explorando os limites do desconhecido e da sanidade humana.', 'https://i.imgur.com/yYNlfJd.jpg'),
(21, 'Robinson Crusoé', 'Daniel Defoe', 'Aventura', 1719, 'A história de um marinheiro que naufraga e fica — sozinho — por anos em uma ilha deserta. O livro narra sua luta pela sobrevivência, adaptação à solidão, encontros com nativos e suas reflexões sobre civilização, natureza e humanidade.', 'https://i.imgur.com/LYUnHwE.jpg'),
(22, 'Vinte Mil Léguas Submarinas', 'Júlio Verne', 'Aventura', 1870, 'Uma expedição parte para investigar um “monstro marinho” que aterroriza os oceanos — mas descobrem que o suposto monstro é, na verdade, um submarino extraordinário comandado pelo enigmático capitão Nemo. A obra mistura aventura com imaginação científica e mergulha em descobertas incríveis sob o mar.', 'https://i.imgur.com/HtBjQpz.jpg'),
(23, 'A Volta ao Mundo em 80 Dias', 'Júlio Verne', 'Aventura', 1873, 'Uma aposta ousada: o protagonista tenta dar a volta ao mundo em apenas 80 dias. A jornada envolve viagens por diversos países, obstáculos e peripécias — ideal para quem curte ritmo acelerado, aventura global e cenários variados.', 'https://i.imgur.com/3IOt8gS.jpg'),
(24, 'As Aventuras de Huckleberry Finn', 'Mark Twain', 'Aventura', 1884, 'Acompanhe as aventuras de Huck e seu amigo Jim pelo rio Mississippi, em fuga de sociedade hipócrita e perigos — com liberdade, críticas sociais e um retrato sincero da América do século XIX. Um clássico da aventura e da formação.', 'https://i.imgur.com/fs9Gzke.jpg'),
(25, 'O Príncipe e o Mendigo', 'Mark Twain', 'Aventura', 1881, 'Uma troca de identidades entre dois jovens — um príncipe e um mendigo — desencadeia uma sequência de aventuras, enganos e aprendizados. Mistura aventura, injustiça social e crítica, com ritmo leve e personagens marcantes.', 'https://i.imgur.com/pvH7lS3.jpg'),
(26, 'O Chamado da Floresta', 'Jack London', 'Aventura', 1903, 'A história de um cão domesticado que passa a viver na dura realidade do norte do Canadá, seja como cão de trenó ou sobrevivendo em meio à natureza selvagem. Uma aventura brutal, de sobrevivência, instinto e adaptação.', 'https://i.imgur.com/5ZIFFmz.jpg'),
(27, 'O Velho e o Mar', 'Ernest Hemingway', 'Aventura', 1952, 'Um velho pescador parte sozinho para alto mar em busca de um grande peixe — a luta entre o homem, o animal e a natureza se torna uma metáfora para determinação, dignidade e resistência. Uma aventura curta, intensa e reflexiva.', 'https://i.imgur.com/WX49wDi.jpg'),
(28, 'O Homem Invisível', 'H. G. Wells', 'Aventura', 1897, 'Uma aventura de ficção científica com elementos de mistério: um cientista descobre como tornar-se invisível — e lida com as consequências desse poder. Combina aventura, suspense e questionamentos éticos.', 'https://i.imgur.com/i4Wym1a.jpg'),
(29, 'O Maravilhoso Mágico de Oz', 'L. Frank Baum', 'Aventura', 1900, 'A jovem Dorothy é levada por um tornado a uma terra mágica, e embarca numa jornada cheia de peripécias, personagens inesquecíveis e desafios — um clássico de aventura/fantasia infanto-juvenil.', 'https://i.imgur.com/QF2kaV8.jpg'),
(30, 'Os Filhos do Capitão Grant', 'Júlio Verne', 'Aventura', 1867, 'Um grupo parte em busca do misterioso Capitão Grant, desaparecido após um naufrágio. A narrativa mistura navegação, ilhas desconhecidas, perigos e descobertas — ideal para quem ama aventura e mistério.', 'https://i.imgur.com/iEiNk7b.jpg'),
(32, 'Eu Sou Malala', 'Malala Yousafzai', 'Biografia', 2013, 'A história da jovem paquistanesa que defendeu o direito das meninas à educação, sobreviveu a um ataque do Talibã e se tornou a pessoa mais jovem a ganhar o Prêmio Nobel da Paz.', 'https://i.imgur.com/FtuoPVL.jpg'),
(33, 'Longa Caminhada até à Liberdade', 'Nelson Mandela', 'Biografia', 1994, 'Autobiografia do líder sul-africano, descrevendo sua infância, trajetória política, ativismo contra o apartheid e os 27 anos que passou na prisão.', 'https://i.imgur.com/xom9oFa.jpg'),
(34, 'Alexandre, o Grande', 'Robin Lane Fox', 'Biografia', 1973, 'Uma das biografias mais respeitadas sobre Alexandre, líder militar que conquistou um império gigantesco antes dos 33 anos. A obra investiga sua formação, estratégias militares, personalidade ambiciosa e o impacto que deixou na história mundial.', 'https://i.imgur.com/zu9NxL2.jpg'),
(35, 'Becoming: Minha História', 'Michelle Obama', 'Biografia', 2018, 'Relato íntimo da ex-primeira-dama dos EUA, abordando sua infância, carreira, vida familiar e a experiência na Casa Branca.', 'https://i.imgur.com/sFta2lW.jpg'),
(36, 'O Diário de Anne Frank', 'Anne Frank', 'Biografia', 1947, 'Diário real de uma adolescente judia escondida durante a ocupação nazista na Holanda. Uma das obras mais marcantes sobre o Holocausto.', 'https://i.imgur.com/CAPcgR8.jpg'),
(37, 'Leonardo da Vinci', 'Walter Isaacson', 'Biografia', 2017, 'Biografia que explora a mente brilhante do artista, inventor e cientista renascentista, baseada em seus cadernos, cartas e registros históricos.', 'https://i.imgur.com/TupobwF.jpg'),
(38, 'Frida: A Biografia', 'Hayden Herrera', 'Biografia', 1983, 'A história da pintora mexicana Frida Kahlo: sua vida marcada por dores físicas, seu casamento com Diego Rivera e sua irreverente expressão artística.', 'https://i.imgur.com/blEZtEH.jpg'),
(39, 'Cleópatra: Uma Biografia', 'Stacy Schiff', 'Biografia', 2010, 'Uma biografia profunda e premiada sobre Cleópatra VII, a última rainha do Egito. A autora desfaz mitos criados ao longo dos séculos e apresenta a verdadeira figura histórica: uma líder política brilhante, estrategista, poliglota e poderosa, cuja influência mudou o rumo do Mediterrâneo antigo. O livro combina história rigorosa com narrativa envolvente.', 'https://i.imgur.com/y4YLqpG.jpg'),
(40, 'John', 'Cynthia Lennon', 'Biografia', 2006, 'Relato da primeira esposa de John Lennon, oferecendo uma visão íntima da formação dos Beatles e da vida pessoal do músico.', 'https://i.imgur.com/h4uO79h.jpg'),
(41, 'O Morro dos Ventos Uivantes', 'Emily Brontë', 'Romance', 1847, 'Uma história intensa de amor obsessivo, vingança e destruição entre Catherine e Heathcliff. Um clássico sobre paixão e tragédia.', 'https://i.imgur.com/styPcXA.jpg'),
(42, 'Filhos e Amantes', 'D. H. Lawrence', 'Romance', 1912, 'Romance psicológico que explora conflitos familiares, amadurecimento emocional e relações amorosas complexas em uma cidade mineradora inglesa.', 'https://i.imgur.com/ooo2BDf.jpg'),
(43, 'Amada', 'Toni Morrison', 'Romance', 1987, 'Uma poderosa história sobre maternidade, trauma e amor, seguindo a vida de uma ex-escrava assombrada pelo passado e pelo fantasma de sua filha.', 'https://i.imgur.com/X1fV1My.jpg'),
(44, 'O Amor nos Tempos do Cólera', 'Gabriel García Márquez', 'Romance', 1985, 'Um amor que atravessa décadas, marcado por espera, desejo e amadurecimento. Um dos romances mais poéticos de Márquez.', 'https://i.imgur.com/IgqwZ1Y.jpg'),
(45, 'A Amiga Genial', 'Elena Ferrante', 'Romance', 2011, 'Primeiro livro da tetralogia napolitana, acompanha a amizade intensa entre duas mulheres, cruzando amores, conflitos e escolhas de vida.', 'https://i.imgur.com/iSX3SWB.jpg'),
(46, 'Persuasão', 'Jane Austen', 'Romance', 1817, 'Anne Elliot reencontra o amor que perdeu no passado e precisa enfrentar suas dúvidas, sua família e sua própria evolução emocional.', 'https://i.imgur.com/0Eosbov.jpg'),
(47, 'Orgulho e Preconceito', 'Jane Austen', 'Romance', 1813, 'Romance clássico entre Elizabeth Bennet e Mr. Darcy, explorando julgamentos precipitados, orgulho, classe social e autoconhecimento.', 'https://i.imgur.com/O2c7kMA.jpg'),
(48, 'Jane Eyre', 'Charlotte Brontë', 'Romance', 1847, 'História de superação, independência, amor e dilemas morais na vida de uma jovem órfã que enfrenta injustiças desde a infância.', 'https://i.imgur.com/ZqsoUAs.jpg'),
(49, 'Anna Kariênina', 'Liev Tolstói', 'Romance', 1877, 'Um dos maiores romances da literatura mundial, mergulha nas consequências emocionais e sociais de uma paixão proibida.', 'https://i.imgur.com/G1VsLOc.jpg'),
(50, 'Razão e Sensibilidade', 'Jane Austen', 'Romance', 1811, 'Duas irmãs com personalidades opostas enfrentam paixões, expectativas sociais e frustrações amorosas em busca de felicidade.', 'https://i.imgur.com/QfVa8u9.jpg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('comum','admin') DEFAULT 'comum',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `criado_em`) VALUES
(1, 'Administrador', 'admin@gmail.com', 'senha-do-admin', 'admin', '2026-06-15 18:55:01'),
(2, 'Usuário1', 'usuario1@email.com', 'user123', 'comum', '2026-06-15 18:55:01'),
(3, 'Usuário 2', 'usuario2@gmail.com', 'user123', 'comum', '2026-06-16 14:08:46');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_livro` (`usuario_id`,`livro_id`),
  ADD KEY `livro_id` (`livro_id`);

--
-- Índices de tabela `historico_leitura`
--
ALTER TABLE `historico_leitura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_livro` (`id_livro`);

--
-- Índices de tabela `listas_leitura`
--
ALTER TABLE `listas_leitura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_livro` (`id_usuario`,`id_livro`),
  ADD KEY `id_livro` (`id_livro`);

--
-- Índices de tabela `livros`
--
ALTER TABLE `livros`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT de tabela `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_leitura`
--
ALTER TABLE `historico_leitura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `listas_leitura`
--
ALTER TABLE `listas_leitura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `livros`
--
ALTER TABLE `livros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`livro_id`) REFERENCES `livros` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_leitura`
--
ALTER TABLE `historico_leitura`
  ADD CONSTRAINT `historico_leitura_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_leitura_ibfk_2` FOREIGN KEY (`id_livro`) REFERENCES `livros` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `listas_leitura`
--
ALTER TABLE `listas_leitura`
  ADD CONSTRAINT `listas_leitura_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `listas_leitura_ibfk_2` FOREIGN KEY (`id_livro`) REFERENCES `livros` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
