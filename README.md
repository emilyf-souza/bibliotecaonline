# Aplicação web "Biblioteca Online"

Uma aplicação web desenvolvida como projeto acadêmico para o gerenciamento de acervo literário, controle de progresso de leitura e organização de títulos favoritos.

## Visão Geral
O projeto consiste em uma plataforma onde usuários podem explorar um catálogo de livros, atualizar e registrar seu status de leitura em um histórico, salvar seus títulos favoritos e receber recomendações personalizadas baseadas em suas listas.

## Tecnologias Utilizadas
* **Back-end:** PHP 8.x (Gerenciamento de sessões, controle de autenticação e processamento de dados)
* **Banco de Dados:** MySQL (Persistência de dados e relacionamentos)
* **Front-end:** HTML5, CSS3 Customizado e JavaScript Assíncrono (Fetch API / DOM)
* **Hospedagem:** InfinityFree

## Funcionalidades Principais
* **Sistema de Autenticação:** Controle de acesso seguro para usuários comuns e administradores com níveis de permissão diferenciados.
* **Busca Avançada e Filtros:** Mecanismo dinâmico que realiza o cruzamento de dados por título, autor e gênero literário diretamente no banco de dados.
* **Lista de leitura:** Painel personalizado onde o usuário organiza suas leituras em três estados ("Quero ler", "Lendo" e "Lido"), atualizados instantaneamente via requisições assíncronas (AJAX/Fetch API) sem recarregamento de página.
* **Lista de favoritos:** Espaço dedicado para o usuário marcar e gerenciar seus livros favoritos através de um sistema de clique rápido (ícone de coração), também integrado com AJAX para uma resposta visual imediata.
* **Recomendação de livros:** Sistema que exibe sugestões de leitura na página principal (através de um carrossel dinâmico), permitindo que o usuário abra modais para ler a sinopse completa e interagir diretamente com o livro.
* **Histórico de leitura:** Recurso que registra a evolução do leitor em cada livro, permitindo o acompanhamento de datas de modificação de status.
* **Paginação Física de Resultados:** Lógica matemática implementada com as cláusulas `LIMIT` e `OFFSET` no SQL para configurar o limite de cards de livros permitidos por página.

## Estrutura do Banco de Dados
O banco de dados é composto pelas seguintes tabelas:
* **`usuarios`**: Armazena os dados do usuário (`id`, `nome`, `e-mail`, `senha`) e o tipo/nível de acesso (`comum`/`admin`).
* **`livros`**: Guarda os dados de cada livro (`id`, `titulo`, `autor`, `genero`, `ano`, `descricao`, `capa`).
* **`listas_leitura`**: Gerencia a lista de leitura do usuário por status (`quero ler`, `lendo`, `lido`).
* **`historico_leitura`**: Registra as datas de modificações de status de cada livro salvo na lista de leitura (`status_origem`, `status_destino`, `data_evento`).
* **`favoritos`**: Gerencia os livros favoritados por cada usuário, asssim como a data em que foi favoritado (`data_favoritado`). 

## Como Instalar e Rodar o Projeto Localmente

### Pré-requisitos
* Servidor local Apache com PHP e MySQL (Ex: XAMPP, WampServer ou Laragon).
* Navegador web atualizado.

### Passo a Passo
1. **Clonar o repositório ou baixar os arquivos:**
   Faça o download do código-fonte e extraia a pasta dentro do diretório padrão do seu servidor local (ex: `C:/xampp/htdocs/nome-do-seu-projeto`).

2. **Configurar o Banco de Dados:**
   * Abra o `phpMyAdmin` no seu navegador (`http://localhost/phpmyadmin`).
   * Crie um novo banco de dados (ex: `biblioteca_db`).
   * Vá na aba "Importar", selecione o arquivo SQL (que está salvo na pasta db deste repositório) e clique em executar.

3. **Ajustar a Conexão no PHP:**
   * Abra o arquivo de conexão (geralmente em `db/conexao.php`) e verifique se as credenciais locais estão corretas:
     ```php
     $host = "localhost";
     $usuario = "root";
     $senha = ""; // ou a senha do seu banco local
     $banco = "NOME_DO_SEU_BANCO";
     ```

4. **Executar a Aplicação:**
   * Abra o navegador e digite: `http://localhost/nome-do-seu-projeto/index.php`

---
Desenvolvido por Emily F. de Souza — Entrega final do projeto de extensão de ADS.
