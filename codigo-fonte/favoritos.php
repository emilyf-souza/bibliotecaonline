<?php session_start(); ?>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "db/conexao.php";

// Bloqueia o acesso se o usuário não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['toast_mensagem'] = "Você precisa estar logado para acessar sua lista de favoritos!";
    $_SESSION['toast_tipo'] = "erro";
    header("Location: cadastro.php");
    exit();
}

$usuario_id = intval($_SESSION['usuario_id']);

// ========================================== //
//         CONFIGURAÇÃO DA PAGINAÇÃO          //
// ========================================== //
$livros_por_pagina = 4; // Define quantos livros aparecerão por página

// Pega a página atual pela URL (ex: favoritos.php?pagina=2). Se não existir, assume a página 1
$pagina_atual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
if ($pagina_atual < 1) { $pagina_atual = 1; }

// Calcula a partir de qual registro o banco vai começar a ler (o OFFSET)
$offset = ($pagina_atual - 1) * $livros_por_pagina;

// Captura a ordenação selecionada (se não houver, define 'recente' como padrão)
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'recente';

// Define o trecho do ORDER BY no SQL baseado na escolha do usuário
switch ($ordem) {
    case 'antigo':
        $orderBy = "favoritos.data_favoritado ASC";
        break;
    case 'alfabetica':
        $orderBy = "livros.titulo ASC";
        break;
    case 'recente':
    default:
        $orderBy = "favoritos.data_favoritado DESC";
        break;
}

// Busca a quantidade total de livros favoritados para calcular as páginas
$sql_total = "SELECT COUNT(*) as total FROM favoritos WHERE usuario_id = $usuario_id";
$res_total = $conn->query($sql_total);
$total_registros = ($res_total) ? intval($res_total->fetch_assoc()['total']) : 0;

// Calcula o total de páginas necessárias (ceil arredonda para cima)
$total_paginas = ceil($total_registros / $livros_por_pagina);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Meus Favoritos - Biblioteca</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header>
  <div class="header-conteudo">
  <a href="index.php" style="display: inline-block; line-height: 0;">
      <img src="assets/bibilioteca-virtual.png" alt="Biblioteca Online" style="height: 50px; width: auto; object-fit: contain;">
    </a>
  </div>

  <div class="menu-usuario-container">
    <div class="usuario-dropdown" onclick="toggleMenu(event)">
        <div class="usuario-avatar">
            <?php echo strtoupper(substr($_SESSION['usuario_nome'], 0, 1)); ?>
        </div>
        <span class="usuario-nome-texto"><?php echo $_SESSION['usuario_nome']; ?></span>
        
        <div class="dropdown-conteudo" id="dropdownMenu">
            <a href="perfil.php" class="btn-meu-pefil">Meu Perfil</a> 
            <a href="sair.php" class="btn-sair">Sair da Conta</a>
        </div>
    </div>
  </div>
</header>

<div class="container">

<a href="index.php" class="btn-opcoes">￩ Voltar</a>

  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px; gap: 15px;">
      <h2 style="margin: 0;">Livros Favoritados (<span id='contador-total'><?php echo $total_registros; ?></span>)</h2>
      
      <form method="GET" action="favoritos.php" style="margin: 0; display: flex; align-items: center; gap: 8px;">
          <label for="ordem" style="font-size: 0.9em; color: #57606f; font-weight: bold;">Ordenar por:</label>
          <select name="ordem" id="ordem" onchange="this.form.submit()" style="padding: 6px 12px; border: 1px solid #ccc; border-radius: 4px; background-color: #fff; color: #2c3e50; font-size: 0.9em; cursor: pointer;">
              <option value="recente" <?php echo $ordem === 'recente' ? 'selected' : ''; ?>>Adicionados recentemente</option>
              <option value="antigo" <?php echo $ordem === 'antigo' ? 'selected' : ''; ?>>Adicionados há mais tempo</option>
              <option value="alfabetica" <?php echo $ordem === 'alfabetica' ? 'selected' : ''; ?>>Ordem alfabética</option>
          </select>
      </form>
  </div>

  <?php
  
  // Busca os livros aplicando o LIMIT e o OFFSET dinâmicos da paginação
  $sql = "SELECT livros.*, favoritos.data_favoritado 
          FROM favoritos 
          INNER JOIN livros ON favoritos.livro_id = livros.id 
          WHERE favoritos.usuario_id = $usuario_id 
          ORDER BY $orderBy
          LIMIT $livros_por_pagina OFFSET $offset";

  $result = $conn->query($sql);

  echo "<div id='container-favoritos'>";

  if (!$result || $result->num_rows == 0) {
      echo "<p id='msg-vazio'>Você ainda não favoritou nenhum livro nesta página. Volte à página inicial ou mude de página!</p>";
  } else {
      while ($livro = $result->fetch_assoc()) {

          $dadosJson = htmlspecialchars(json_encode($livro, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');        
          $imagem_capa = !empty($livro['capa']) ? $livro['capa'] : 'assets/sem-capa.png';
          $data_formatada = date('d/m/Y', strtotime($livro['data_favoritado']));

          echo "<div class='livro' id='card-livro-{$livro['id']}'>
          <div class='livro-conteudo-flex'>
              
              <div class='livro-capa-container'>
                  <img src='{$imagem_capa}' class='imagem-capa-livro' alt='Capa do Livro'>
              </div>

              <div class='livro-detalhes-container'>
                  <h3>" . htmlspecialchars($livro['titulo']) . "</h3>

                  <div class='info'>
                      <p><strong>Autor:</strong> " . htmlspecialchars($livro['autor']) . "</p>
                      <p><strong>Gênero:</strong> " . htmlspecialchars($livro['genero']) . "</p>
                      <p><strong>Ano:</strong> " . htmlspecialchars($livro['ano']) . "</p>
                      <p style='font-weight: bold; margin-top: 8px;'>
                          Favoritado em: {$data_formatada}
                      </p>
                  </div>

                  <button class='btn-sinopse' onclick='abrirModalRecomendacao({$dadosJson})'>Ver sinopse</button>

                  <p class='sinopse' style='display:none;'>
                      " . htmlspecialchars($livro['descricao']) . "
                  </p>
              </div>

          </div>

          <a class='btn-deletar' 
             href='#' 
             title='Remover dos Favoritos' 
             onclick=\"removerFavoritoDireto(event, {$livro['id']}, '" . htmlspecialchars($livro['titulo'], ENT_QUOTES) . "')\">
              <img src='assets/deletar.png' class='icone-acao' alt='Remover'>
          </a>

          </div>";
      }
  }
  
  echo "</div>"; 
  ?>

  <?php if ($total_paginas > 1): ?>
      <div class="paginacao-container" style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 30px; margin-bottom: 20px;">
          
          <?php if ($pagina_atual > 1): ?>
              <a href="favoritos.php?pagina=<?php echo $pagina_atual - 1; ?>&ordem=<?php echo $ordem; ?>" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #3498db;">&laquo; Anterior</a>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
              <a href="favoritos.php?pagina=<?php echo $i; ?>&ordem=<?php echo $ordem; ?>" 
                 style="padding: 8px 14px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; <?php echo $i === $pagina_atual ? 'background-color: #3498db; color: white; border-color: #3498db; font-weight: bold;' : 'color: #2c3e50;'; ?>">
                  <?php echo $i; ?>
              </a>
          <?php endfor; ?>

          <?php if ($pagina_atual < $total_paginas): ?>
              <a href="favoritos.php?pagina=<?php echo $pagina_atual + 1; ?>&ordem=<?php echo $ordem; ?>" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #3498db;">Próximo &raquo;</a>
          <?php endif; ?>

      </div>
  <?php endif; ?>

</div>

<div id="modalRec" class="modal-confirmacao-fundo" onclick="document.getElementById('modalRec').classList.remove('mostrar')">
    <div class="modal-confirmacao-caixa" onclick="event.stopPropagation()" style="max-width: 500px; text-align: left;">
        <span class="fechar-modal" onclick="document.getElementById('modalRec').classList.remove('mostrar')" style="float: right; cursor: pointer; font-size: 1.5em;">&times;</span>
        
        <div style="display: flex; gap: 15px; margin-top: 10px;">
            <img id="modalRecCapa" src="" style="width: 100px; height: 150px; object-fit: cover; border-radius: 4px;">
            <div>
                <h3 id="modalRecTitulo" style="margin: 0 0 10px 0;"></h3>
                <p style="margin: 4px 0;"><strong>Autor:</strong> <span id="modalRecAutor"></span></p>
                <p style="margin: 4px 0;"><strong>Gênero:</strong> <span id="modalRecGenero"></span></p>
                <p style="margin: 4px 0;"><strong>Ano:</strong> <span id="modalRecAno"></span></p>
            </div>
        </div>
        <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
        <h4>Sinopse:</h4>
        <p id="modalRecSinopse" style="line-height: 1.4; color: #555; max-height: 200px; overflow-y: auto;"></p>
        
        <div id="modalRecAcoes" style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end; align-items: center;"></div>
    </div>
</div>

<script>
function abrirModalRecomendacao(livro) {
    document.getElementById('modalRecCapa').src = livro.capa ? livro.capa : 'assets/sem-capa.png';
    document.getElementById('modalRecTitulo').innerText = livro.titulo;
    document.getElementById('modalRecAutor').innerText = livro.autor;
    document.getElementById('modalRecGenero').innerText = livro.genero;
    document.getElementById('modalRecAno').innerText = livro.ano;
    document.getElementById('modalRecSinopse').innerText = livro.descricao ? livro.descricao : 'Nenhuma sinopse disponível.';

    const containerAcoes = document.getElementById('modalRecAcoes');
    if (containerAcoes) {
        containerAcoes.innerHTML = '';
    }

    document.getElementById('modalRec').classList.add('mostrar');
}

function toggleMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('dropdownMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

document.addEventListener('click', function() {
    const menu = document.getElementById('dropdownMenu');
    if (menu) menu.style.display = 'none';
});

function removerFavoritoDireto(event, idLivro, titulo) {
    event.preventDefault(); 

    const botaoClicado = event.currentTarget;
    const dados = new FormData();
    dados.append('id_livro', idLivro);

    fetch('atualizar_favorito.php', {
        method: 'POST',
        body: dados
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'sucesso' && data.acao === 'removido') {
            
            let card = document.getElementById(`card-livro-${idLivro}`) || botaoClicado.closest('.livro');
            
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    card.remove(); 
                    
                    const todosOsCardsRestantes = document.querySelectorAll('#container-favoritos .livro');
                    const totalAtual = todosOsCardsRestantes.length;
                    
                    // Se o usuário deletar o último item da página atual, dá um reload para ajustar a paginação
                    if (totalAtual === 0) {
                        window.location.reload();
                    } else {
                        // Se ainda restarem itens na tela, só atualiza o número total do cabeçalho
                        const contador = document.getElementById('contador-total');
                        if (contador) {
                            // Diminui 1 do número total que buscamos no PHP
                            let numTotal = parseInt(contador.textContent);
                            contador.textContent = numTotal - 1;
                        }
                    }
                }, 300);
            }
            
            mostrarToast(`"${titulo}" foi removido dos seus Favoritos.`, 'sucesso');
        } else {
            mostrarToast(data.mensagem || 'Erro ao remover favorito.', 'erro');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarToast('Erro de conexão ao remover dos favoritos.', 'erro');
    });
}

function mostrarToast(mensagem, tipo = 'sucesso') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast-alerta toast-${tipo}`;
    toast.innerHTML = `<span>${mensagem}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-sumir');
        setTimeout(() => { toast.remove(); }, 600);
    }, 3000);
}
</script>

</body>
</html>