<?php 
session_start(); 

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "db/conexao.php";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Biblioteca Online</title>
  <link rel="stylesheet" href="assets/style.css?v=1003">
  <link rel="icon" href="assets/favicon.png?v=1">
</head>
<body>

<header>
  <div class="header-conteudo">
  <a href="index.php" style="display: inline-block; line-height: 0;">
      <img src="assets/bibilioteca-virtual.png" alt="Biblioteca Online" style="height: 50px; width: auto; object-fit: contain;">
    </a>
  </div>

  <div class="menu-usuario-container">
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <a href="leitura.php" class="btn-entrar-topo" style="display: inline-flex; align-items: center; gap: 6px;">
            <img src="assets/icone-lista.png" style="width: 16px; height: 16px;" alt="Listas"> 
            Lista de leitura
        </a>

        <a href="favoritos.php" class="btn-cadastrar-topo" style="display: inline-flex; align-items: center; gap: 6px;">
            <img src="assets/meus-favoritos.png" style="width: 16px; height: 16px;" alt="Favoritos"> 
            Meus Favoritos
        </a>

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
    <?php else: ?>
        <a href="login.php" class="btn-entrar-topo">Entrar</a>
        <a href="cadastro.php" class="btn-cadastrar-topo">Cadastrar-se</a>
    <?php endif; ?>
  </div>
</header>

<div class="container">

  <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
      <div style="margin-bottom: 20px; display: flex; justify-content: center;">
          <a href="add_livro.php" class="btn-cadastrar-livro">Cadastrar Livros</a>
      </div>
  <?php endif; ?>

  <div class="linha-busca" style="display: flex; flex-direction: column; gap: 15px;">
    
    <form method="GET" class="form-busca" style="width: 100%; margin: 0;">
        <input type="text" name="titulo" placeholder="Buscar por título" value="<?php echo isset($_GET['titulo']) ? htmlspecialchars($_GET['titulo']) : ''; ?>">
        <input type="text" name="autor" placeholder="Buscar por autor" value="<?php echo isset($_GET['autor']) ? htmlspecialchars($_GET['autor']) : ''; ?>">

        <select name="genero" class="form-busca">
            <option value="">Todos os gêneros</option>
            <option value="Fantasia" <?php echo (isset($_GET['genero']) && $_GET['genero'] === 'Fantasia') ? 'selected' : ''; ?>>Fantasia</option>
            <option value="Terror" <?php echo (isset($_GET['genero']) && $_GET['genero'] === 'Terror') ? 'selected' : ''; ?>>Terror</option>
            <option value="Aventura" <?php echo (isset($_GET['genero']) && $_GET['genero'] === 'Aventura') ? 'selected' : ''; ?>>Aventura</option>
            <option value="Biografia" <?php echo (isset($_GET['genero']) && $_GET['genero'] === 'Biografia') ? 'selected' : ''; ?>>Biografia</option>
            <option value="Romance" <?php echo (isset($_GET['genero']) && $_GET['genero'] === 'Romance') ? 'selected' : ''; ?>>Romance</option>
        </select>

        <button type="submit" class='btn-cadastrar-livro'>🔍︎ Buscar</button>
    </form>
  </div>

  <?php include "sugestoes.php"; ?>

  <hr>

  <?php

 // ========================================== //
 //         CONFIGURAÇÃO DA PAGINAÇÃO          //
 // ========================================== //

  $livros_por_pagina = 12; 
  $pagina_atual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
  if ($pagina_atual < 1) { $pagina_atual = 1; }
  $offset = ($pagina_atual - 1) * $livros_por_pagina;

  $condicoes = [];
  $url_params = ""; 

  if (!empty($_GET['titulo'])) {
      $titulo = $conn->real_escape_string($_GET['titulo']);
      $condicoes[] = "titulo LIKE '%$titulo%'";
      $url_params .= "&titulo=" . urlencode($_GET['titulo']);
  }
  if (!empty($_GET['autor'])) {
      $autor = $conn->real_escape_string($_GET['autor']);
      $condicoes[] = "autor LIKE '%$autor%'";
      $url_params .= "&autor=" . urlencode($_GET['autor']);
  }
  if (!empty($_GET['genero'])) {
      $genero = $conn->real_escape_string($_GET['genero']);
      $condicoes[] = "genero = '$genero'";
      $url_params .= "&genero=" . urlencode($_GET['genero']);
  }

  $sql_total = "SELECT COUNT(*) as total FROM livros";
  if (count($condicoes) > 0) {
      $sql_total .= " WHERE " . implode(" AND ", $condicoes);
  }
  $res_total = $conn->query($sql_total);
  $total_registros = ($res_total) ? intval($res_total->fetch_assoc()['total']) : 0;
  $total_paginas = ceil($total_registros / $livros_por_pagina);

  $id_sessao_user = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 0;
  $sql = "SELECT livros.*, 
          (SELECT COUNT(*) FROM favoritos WHERE favoritos.livro_id = livros.id AND favoritos.usuario_id = $id_sessao_user) as eh_favorito 
          FROM livros";

  if (count($condicoes) > 0) {
      $sql .= " WHERE " . implode(" AND ", $condicoes);
  }

  $sql .= " LIMIT $livros_por_pagina OFFSET $offset";
  $result = $conn->query($sql);

  echo "<h2>Resultados ($total_registros)</h2>";

  if (!$result || $result->num_rows == 0) {
    echo "<p>Nenhum livro encontrado.</p>";
  } else {
    while ($livro = $result->fetch_assoc()) {

        $imagem_capa = !empty($livro['capa']) ? $livro['capa'] : 'assets/sem-capa.png';
        
        $imagem_coracao = "assets/coracao-vazio.png";
        $titulo_favorito = "Favoritar Livro";

        if (isset($_SESSION['usuario_id'])) {
            $imagem_coracao = ($livro['eh_favorito'] > 0) ? "assets/coracao-cheio.png" : "assets/coracao-vazio.png";
            $titulo_favorito = ($livro['eh_favorito'] > 0) ? "Remover dos Favoritos" : "Favoritar Livro";
        }

        $dadosJson = htmlspecialchars(json_encode($livro, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');

        echo "<div class='livro'>
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
                </div>

                <button class='btn-sinopse' onclick='abrirModalRecomendacao({$dadosJson})'>Ver sinopse</button>

                <p class='sinopse' style='display:none;'>
                    " . htmlspecialchars($livro['descricao']) . "
                </p>

                <div class='acoes'>
                    <span>Adicionar à:</span>
                    <a href='#' class='btn-opcoes' style='border: none; box-shadow: none' onclick=\"event.preventDefault(); adicionarLista({$livro['id']}, 'quero ler', '" . htmlspecialchars($livro['titulo'], ENT_QUOTES) . "'); return false;\">Quero ler</a>
                    <a href='#' class='btn-opcoes' onclick=\"event.preventDefault(); adicionarLista({$livro['id']}, 'lendo', '" . htmlspecialchars($livro['titulo'], ENT_QUOTES) . "'); return false;\">Lendo</a>
                    <a href='#' class='btn-opcoes' onclick=\"event.preventDefault(); adicionarLista({$livro['id']}, 'lido', '" . htmlspecialchars($livro['titulo'], ENT_QUOTES) . "'); return false;\">Lido</a>
                    
                    <button class='btn-favorito' onclick=\"alternarFavorito(this, {$livro['id']}, '" . htmlspecialchars($livro['titulo'], ENT_QUOTES) . "')\" title='{$titulo_favorito}'>
                        <img class='icone-coracao' src='{$imagem_coracao}' alt='Favorito'>
                    </button>
                </div>
            </div>

        </div>";

        if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin') {
            echo '
            <a class="btn-editar" href="add_livro.php?editar=' . $livro['id'] . '" title="Editar">
                <img src="assets/editar.png" class="icone-acao" alt="Editar">
            </a>';
            echo '
            <a class="btn-deletar"
            href="add_livro.php?excluir=' . $livro['id'] . '"
            title="Deletar"
            onclick="perguntarAntesDeletar(event, this)">
             <img src="assets/deletar.png" class="icone-acao" alt="Deletar">
         </a>';
        };

        echo "</div>"; 
    }
  }
  ?>

  <?php if ($total_paginas > 1): ?>
      <div class="paginacao-container" style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 30px; margin-bottom: 20px;">
          
          <?php if ($pagina_atual > 1): ?>
              <a class="link-paginacao" href="index.php?pagina=<?php echo $pagina_atual - 1; ?><?php echo $url_params; ?>" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #3498db;">&laquo; Anterior</a>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
              <a class="link-paginacao" href="index.php?pagina=<?php echo $i; ?><?php echo $url_params; ?>" 
                 style="padding: 8px 14px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; <?php echo $i === $pagina_atual ? 'background-color: #3498db; color: white; border-color: #3498db; font-weight: bold;' : 'color: #2c3e50;'; ?>">
                  <?php echo $i; ?>
              </a>
          <?php endfor; ?>

          <?php if ($pagina_atual < $total_paginas): ?>
              <a class="link-paginacao" href="index.php?pagina=<?php echo $pagina_atual + 1; ?><?php echo $url_params; ?>" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #3498db;">Próximo &raquo;</a>
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

// Monitoramento de scroll para links e formulários, mantendo a posição ao recarregar a página
document.addEventListener("DOMContentLoaded", function() {

    // Injeta a rolagem atual nos links da paginação assim que o usuário clica neles
    const linksPaginacao = document.querySelectorAll(".link-paginacao");
    linksPaginacao.forEach(link => {
        link.addEventListener("click", function(e) {
            e.preventDefault();
            const scrollPos = window.scrollY || window.pageYOffset;
            const urlOriginal = this.getAttribute("href");
            window.location.href = urlOriginal + "&scroll=" + Math.round(scrollPos);
        });
    });

    // Se a página acabou de carregar com o parâmetro '?scroll=', faz o ajuste instantâneo
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('scroll')) {
        const scrollPos = parseInt(urlParams.get('scroll'));
        if (!isNaN(scrollPos)) {
            window.scrollTo(0, scrollPos);
            // Limpa o parâmetro da URL de forma "silenciosa" para manter o link organizado
            const novaUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search.replace(/[&?]scroll=\d+/, '').replace(/^&/, '?');
            window.history.replaceState({ path: novaUrl }, '', novaUrl);
        }
    }
});

function abrirModalRecomendacao(livro, ehSugestao = false) {
    document.getElementById('modalRecCapa').src = livro.capa ? livro.capa : 'assets/sem-capa.png';
    document.getElementById('modalRecTitulo').innerText = livro.titulo;
    document.getElementById('modalRecAutor').innerText = livro.autor;
    document.getElementById('modalRecGenero').innerText = livro.genero;
    document.getElementById('modalRecAno').innerText = livro.ano;
    document.getElementById('modalRecSinopse').innerText = livro.descricao ? livro.descricao : 'Nenhuma sinopse disponível.';

    const containerAcoes = document.getElementById('modalRecAcoes');
    if (containerAcoes) {
        if (ehSugestao) {
            const tituloEscapado = livro.titulo.replace(/'/g, "\\'");
            containerAcoes.innerHTML = `
                <span style="font-size: 0.9em; color: #666; margin-right: auto;">Adicionar à:</span>
                <a href="#" class="btn-opcoes" style="font-size: 0.85em; text-decoration: none;" onclick="event.preventDefault(); document.getElementById('modalRec').classList.remove('mostrar'); adicionarLista(${livro.id}, 'quero ler', '${tituloEscapado}'); return false;">Quero ler</a>
                <a href="#" class="btn-opcoes" style="font-size: 0.85em; text-decoration: none;" onclick="event.preventDefault(); document.getElementById('modalRec').classList.remove('mostrar'); adicionarLista(${livro.id}, 'lendo', '${tituloEscapado}'); return false;">Lendo</a>
                <a href="#" class="btn-opcoes" style="font-size: 0.85em; text-decoration: none;" onclick="event.preventDefault(); document.getElementById('modalRec').classList.remove('mostrar'); adicionarLista(${livro.id}, 'lido', '${tituloEscapado}'); return false;">Lido</a>
            `;
        } else {
            containerAcoes.innerHTML = '';
        }
    }

    document.getElementById('modalRec').classList.add('mostrar');
}

function adicionarLista(idLivro, status, titulo) {
    const dados = new FormData();
    dados.append('id_livro', idLivro);
    dados.append('status', status);

    fetch('atualizar_lista.php', {
        method: 'POST',
        body: dados
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'sucesso') {
            mostrarToast(`O livro "${titulo}" foi adicionado à lista ${status}!`, 'sucesso');
        } else {
            mostrarToast(data.mensagem, 'erro');
            if (data.mensagem.includes('logado')) {
                setTimeout(() => { window.location.href = 'cadastro.php'; }, 2000);
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarToast('Erro de conexão ao salvar a lista.', 'erro');
    });
}

function alternarFavorito(botao, idLivro, titulo) {
    const dados = new FormData();
    dados.append('id_livro', idLivro);

    fetch('atualizar_favorito.php', {
        method: 'POST',
        body: dados
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'sucesso') {
            const imgCoracao = botao.querySelector('img');
            if (data.acao === 'adicionado') {
                imgCoracao.src = 'assets/coracao-cheio.png';
                botao.title = 'Remover dos Favoritos';
                mostrarToast(`"${titulo}" foi adicionado aos seus Favoritos!`, 'sucesso');
            } else {
                imgCoracao.src = 'assets/coracao-vazio.png';
                botao.title = 'Favoritar Livro';
                mostrarToast(`"${titulo}" foi removido dos seus Favoritos.`, 'sucesso');
            }
        } else {
            mostrarToast(data.mensagem, 'erro');
            if (data.mensagem.includes('logado')) {
                setTimeout(() => { window.location.href = 'cadastro.php'; }, 2000);
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarToast('Erro de conexão ao favoritar.', 'erro');
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

function toggleMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('dropdownMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

document.addEventListener('click', function() {
    const menu = document.getElementById('dropdownMenu');
    if (menu) menu.style.display = 'none';
});
</script>

<div id="meuModalConfirmacao" class="modal-confirmacao-fundo">
    <div class="modal-confirmacao-caixa">
        <h4>⚠️ Atenção!</h4>
        <p>Tem certeza absoluta que deseja prosseguir com esta exclusão?</p>
        <div class="modal-botoes">
            <button type="button" class="btn-modal btn-modal-cancelar" id="btnModalCancelar">Não, cancelar</button>
            <button type="button" class="btn-modal btn-modal-confirmar" id="btnModalConfirmar">Sim, excluir</button>
        </div>
    </div>
</div>

<script>
let urlAlvoExclusao = null;
let formularioAlvoExclusao = null;

function perguntarAntesDeletar(event, elemento) {
    event.preventDefault(); 
    
    if (elemento.tagName === 'A') {
        urlAlvoExclusao = elemento.href;
        formularioAlvoExclusao = null;
    } else {
        formularioAlvoExclusao = elemento;
        urlAlvoExclusao = null;
    }
    
    const modal = document.getElementById('meuModalConfirmacao');
    modal.classList.add('mostrar');
}

document.getElementById('btnModalCancelar').addEventListener('click', function() {
    document.getElementById('meuModalConfirmacao').classList.remove('removed');
    document.getElementById('meuModalConfirmacao').classList.remove('mostrar');
});

document.getElementById('btnModalConfirmar').addEventListener('click', function() {
    document.getElementById('meuModalConfirmacao').classList.remove('mostrar');
    if (urlAlvoExclusao) {
        window.location.href = urlAlvoExclusao; 
    } else if (formularioAlvoExclusao) {
        formularioAlvoExclusao.submit(); 
    }
});
</script>

</body>
</html>
