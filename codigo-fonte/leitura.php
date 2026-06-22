<?php
session_start();
require_once "db/conexao.php"; 

// Se o usuário não estiver logado, avisa via Toast na tela de cadastro
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['toast_mensagem'] = "Você precisa estar logado para acessar suas listas de leitura!";
    $_SESSION['toast_tipo'] = "erro";
    header("Location: cadastro.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// =============================================== //
// PROCESSAMENTO DE AÇÕES (MUDAR STATUS / REMOVER) //
// =============================================== //

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        $id_livro = intval($_POST['id_livro']);
        $aba_atual = 'quero'; // valor padrão de segurança

        // Captura o status atual antes de qualquer alteração para saber qual aba manter aberta
        $res_status = $conn->query("SELECT status FROM listas_leitura WHERE id_usuario = $id_usuario AND id_livro = $id_livro");
        if ($res_status && $res_status->num_rows > 0) {
            $dado_status = $res_status->fetch_assoc();
            $status_antigo = strtolower(trim($dado_status['status']));
            if ($status_antigo === 'quero ler') $aba_atual = 'quero';
            if ($status_antigo === 'lendo') $aba_atual = 'lendo';
            if ($status_antigo === 'lido') $aba_atual = 'lido';
        }

        if ($_POST['acao'] === 'mudar_status') {
            $novo_status = $conn->real_escape_string($_POST['novo_status']);
            
            // Pega o status de origem antes de atualizar
            $status_origem = "NULL";
            if (isset($status_antigo)) {
                $status_origem = "'" . $conn->real_escape_string($status_antigo) . "'";
            }

            // Atualiza o status
            $conn->query("UPDATE listas_leitura SET status = '$novo_status' WHERE id_usuario = $id_usuario AND id_livro = $id_livro");
            
            // Grava no histórico a alteração feita por esta página
            $conn->query("INSERT INTO historico_leitura (id_usuario, id_livro, status_origem, status_destino) VALUES ($id_usuario, $id_livro, $status_origem, '$novo_status')");

            $_SESSION['toast_mensagem'] = "Status updated com sucesso!";
            $_SESSION['toast_tipo'] = "sucesso";

            // Se mudou de status, abre a aba de destino do livro
            if ($novo_status === 'quero ler') $aba_atual = 'quero';
            if ($novo_status === 'lendo') $aba_atual = 'lendo';
            if ($novo_status === 'lido') $aba_atual = 'lido';
        } 
        
        if ($_POST['acao'] === 'remover') {
            // Remove da lista atual
            $conn->query("DELETE FROM listas_leitura WHERE id_usuario = $id_usuario AND id_livro = $id_livro");
            
            // Apaga todo o histórico do livro removido para este usuário
            $conn->query("DELETE FROM historico_leitura WHERE id_usuario = $id_usuario AND id_livro = $id_livro");

            $_SESSION['toast_mensagem'] = "Livro removido e histórico limpo!";
            $_SESSION['toast_tipo'] = "erro";
        }

        // Redireciona informando qual aba o script deve forçar a ficar aberta
        header("Location: leitura.php?aba_aberta=" . $aba_atual);
        exit();
    }
}

// ===================================== //
// BUSCA DOS LIVROS DO USUÁRIO NO BANCO  //
// ===================================== //

$sql = "SELECT l.*, ll.status FROM listas_leitura ll 
        INNER JOIN livros l ON ll.id_livro = l.id 
        WHERE ll.id_usuario = $id_usuario";
$result = $conn->query($sql);

$lista_quero = [];
$lista_lendo = [];
$lista_lido = [];

if ($result && $result->num_rows > 0) {
    $idx_quero = 0;
    $idx_lendo = 0;
    $idx_lido = 0;
    while ($livro = $result->fetch_assoc()) {
        $status_banco = strtolower(trim($livro['status']));
        if ($status_banco === 'quero ler') {
            $livro['index_cronologico'] = $idx_quero++;
            $lista_quero[] = $livro;
        }
        if ($status_banco === 'lendo') {
            $livro['index_cronologico'] = $idx_lendo++;
            $lista_lendo[] = $livro;
        }
        if ($status_banco === 'lido') {
            $livro['index_cronologico'] = $idx_lido++;
            $lista_lido[] = $livro;
        }
    }
}

// ========================================= //
// CÁLCULO DINÂMICO DE PAGINAÇÃO POR BLOCO   //
// ========================================= //

$livros_por_pagina = 4; 

// Captura a página atual de cada aba via URL individualizada
$p_quero = isset($_GET['p_quero']) ? max(1, intval($_GET['p_quero'])) : 1;
$p_lendo = isset($_GET['p_lendo']) ? max(1, intval($_GET['p_lendo'])) : 1;
$p_lido  = isset($_GET['p_lido'])  ? max(1, intval($_GET['p_lido']))  : 1;

// Totais de registros originais antes do corte
$tot_quero = count($lista_quero);
$tot_lendo = count($lista_lendo);
$tot_lido  = count($lista_lido);

// Totais de páginas de cada aba
$paginas_quero = ceil($tot_quero / $livros_por_pagina);
$paginas_lendo = ceil($tot_lendo / $livros_por_pagina);
$paginas_lido  = ceil($tot_lido  / $livros_por_pagina);

// Fatiamento dos arrays para exibir apenas o escopo da página atual
$lista_quero_paginada = array_slice($lista_quero, ($p_quero - 1) * $livros_por_pagina, $livros_por_pagina);
$lista_lendo_paginada = array_slice($lista_lendo, ($p_lendo - 1) * $livros_por_pagina, $livros_por_pagina);
$lista_lido_paginada  = array_slice($lista_lido,  ($p_lido  - 1) * $livros_por_pagina, $livros_por_pagina);


// Função auxiliar para formatar a data
function formatarDataHistorico($data_banco) {
    if (!$data_banco) return '';
    return date('d/m/Y', strtotime($data_banco));
}

// Função auxiliar para renderizar os cards com botões corrigidos
function exibirCardLivroLeitura($livro) {
    global $conn, $id_usuario;
    $imagem_capa = !empty($livro['capa']) ? $livro['capa'] : 'assets/sem-capa.png';
    $status_atual = strtolower(trim($livro['status']));
    $id_livro = $livro['id'];
    $index_cronologico = $livro['index_cronologico'];

    // Busca as datas de eventos deste livro
    $sql_hist = "SELECT status_destino, MIN(data_evento) as data_registro 
                 FROM historico_leitura 
                 WHERE id_usuario = $id_usuario AND id_livro = $id_livro 
                 GROUP BY status_destino";
    $res_hist = $conn->query($sql_hist);
    
    $datas = [
        'quero ler' => null,
        'lendo' => null,
        'lido' => null
    ];

    if ($res_hist && $res_hist->num_rows > 0) {
        while ($hist = $res_hist->fetch_assoc()) {
            $status_dest = strtolower(trim($hist['status_destino']));
            if (array_key_exists($status_dest, $datas)) {
                $datas[$status_dest] = $hist['data_registro'];
            }
        }
    }

    $frase_historico = "Você ";
    $tem_historico = false;

    if ($datas['quero ler']) {
        $frase_historico .= "encontrou este livro e o salvou em 'Quero ler' em <strong>" . formatarDataHistorico($datas['quero ler']) . "</strong>. ";
        $tem_historico = true;
    }

    if ($datas['lendo']) {
        if ($tem_historico) {
            $frase_historico .= "Começou sua leitura em <strong>" . formatarDataHistorico($datas['lendo']) . "</strong>";
        } else {
            $frase_historico .= "começou a leitura deste livro em <strong>" . formatarDataHistorico($datas['lendo']) . "</strong>";
        }
        $tem_historico = true;
    }

    if ($datas['lido']) {
        if ($datas['lendo']) {
            $frase_historico .= " e a terminou em <strong>" . formatarDataHistorico($datas['lido']) . "</strong>.";
        } else if ($datas['quero ler']) {
            $frase_historico .= " e marcou diretamente como concluído em <strong>" . formatarDataHistorico($datas['lido']) . "</strong>.";
        } else {
            $frase_historico .= "salvou este livro direto como concluído em <strong>" . formatarDataHistorico($datas['lido']) . "</strong>.";
        }
        $tem_historico = true;
    }

    if (!$tem_historico) {
        $frase_historico = "Nenhum histórico registrado para este livro ainda.";
    }

    // Garante que o botão herde o visual do 'btn-opcoes' mesmo dentro do formulário
    $estilo_botao_forcado = "display: block; width: 100%; margin-bottom: 10px; text-align: center; border: 1px solid #3498db; background-color: #3498db; color: white; padding: 8px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.2s;";

    echo "
    <div class='livro' data-index='{$index_cronologico}' data-titulo='" . htmlspecialchars($livro['titulo'], ENT_QUOTES) . "' style='position: relative; margin-bottom: 20px; padding-bottom: 15px;'>
        <div class='livro-conteudo-flex'>
            <div class='livro-capa-container'>
                <img src='{$imagem_capa}' class='imagem-capa-livro' alt='Capa'>
            </div>
            
            <div class='livro-detalhes-container' style='width: 100%;'>
                <h3>" . htmlspecialchars($livro['titulo']) . "</h3>
                <div class='info'>
                    <p><strong>Autor:</strong> " . htmlspecialchars($livro['autor']) . "</p>
                    <p><strong>Gênero:</strong> " . htmlspecialchars($livro['genero']) . "</p>
                </div>

                <details style='margin-top: 12px; background: #f8f9fa; border-radius: 6px; padding: 5px 10px; border: 1px solid #e9ecef;'>
                    <summary style='font-size: 0.85em; color: #7f8c8d; cursor: pointer; font-weight: bold; user-select: none;'>↺ Ver Histórico</summary>
                    <p style='margin: 8px 0 3px 0; font-size: 0.88em; color: #2c3e50; line-height: 1.4;'>
                        {$frase_historico}
                    </p>
                </details>

                <details class='menu-editar-leitura' style='margin-top: 10px;'>
                    <summary>Editar</summary>
                    
                    <div class='opcoes-dropdown-leitura' style='background: white; padding: 10px; border-radius: 6px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1); margin-top: 5px;'>
                        <form method='POST' style='margin: 0; background: transparent; padding: 0; border-radius: 0; box-shadow: none;'>
                            <input type='hidden' name='id_livro' value='{$id_livro}'>
                            <input type='hidden' name='acao' value='mudar_status'>
                            
                            " . ($status_atual !== 'quero ler' ? "<button type='submit' name='novo_status' value='quero ler' class='btn-opcoes' style='{$estilo_botao_forcado}'>Quero ler</button>" : "") . "
                            " . ($status_atual !== 'lendo' ? "<button type='submit' name='novo_status' value='lendo' class='btn-opcoes' style='{$estilo_botao_forcado}'>Lendo</button>" : "") . "
                            " . ($status_atual !== 'lido' ? "<button type='submit' name='novo_status' value='lido' class='btn-opcoes' style='{$estilo_botao_forcado}'>Lido</button>" : "") . "
                        </form>
                    </div>
                </details>

            </div>
        </div>

        <form method='POST' style='margin: 0; background: transparent; padding: 0; border-radius: 0; box-shadow: none; position: absolute; right: 15px; top: 15px;'>
            <input type='hidden' name='id_livro' value='{$id_livro}'>
            <input type='hidden' name='acao' value='remover'>
            <button type='submit' class='btn-deletar' title='Remover da Lista' style='box-shadow: none; background: transparent; border: none; cursor: pointer; padding: 0;'>
                <img src='assets/deletar.png' class='icone-acao' style='background: transparent' alt='Remover'>
            </button>
        </form>
    </div>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Lista de Leitura</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .accordion-secao {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        .accordion-botao {
            width: 100%;
            background: #f8f9fa;
            border: none;
            outline: none;
            text-align: left;
            padding: 15px 20px;
            font-size: 1.3em;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.2s;
            user-select: none;
        }
        .accordion-botao:hover {
            background: #e9ecef;
        }
        .accordion-botao-titulo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-size: 1em;
        }
        .accordion-botao::after {
            content: '▼';
            font-size: 0.7em;
            color: #7f8c8d;
            transition: transform 0.3s;
        }
        .accordion-secao.ativo .accordion-botao::after {
            transform: rotate(-180deg);
        }
        .accordion-conteudo {
            display: none;
            padding: 20px;
            border-top: 1px solid #ddd;
        }
        .accordion-secao.ativo .accordion-conteudo {
            display: block;
        }
        .topo-filtro-flex {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }
        .select-ordenacao {
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 0.9em;
            cursor: pointer;
            background: #fff;
        }
        .paginacao-leitura {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 20px;
        }
        .paginacao-leitura a {
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-decoration: none;
            color: #3498db;
            font-size: 0.9em;
        }
    </style>
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

<div class="container" style="margin-top: 40px;">

    <div style="margin-bottom: 30px;">
        <a href="index.php" class="btn-opcoes">￩ Voltar</a>
    </div>

    <div class="accordion-secao <?php echo (isset($_GET['aba_aberta']) && $_GET['aba_aberta'] === 'quero') || (isset($_GET['p_quero']) && intval($_GET['p_quero']) > 1) || (!isset($_GET['aba_aberta']) && !isset($_GET['p_lendo']) && !isset($_GET['p_lido']) && !isset($_GET['p_quero'])) ? 'ativo' : ''; ?>" id="secao-quero">
        <button class="accordion-botao" onclick="toggleAccordion('secao-quero')">
            <h2 class="accordion-botao-titulo">
                <img src="assets/quero-ler.png" class="icone-titulo-lista" alt="Quero ler"> 
                Quero ler (<?php echo $tot_quero; ?>)
            </h2>
        </button>
        <div class="accordion-conteudo">
            <div class="topo-filtro-flex">
                <span style="font-size: 0.88em; color: #666;">Ordenar:</span>
                <select class="select-ordenacao" onchange="ordenarCards(this, 'quero')">
                    <option value="recentes">Adicionados recentemente</option>
                    <option value="antigos">Adicionados há mais tempo</option>
                    <option value="alfabetica">Ordem Alfabética</option>
                </select>
            </div>
            <div id="quero">
                <?php 
                if (empty($lista_quero_paginada)) {
                    echo "<p style='color: #7f8c8d; font-style: italic;'>Nenhum livro nesta lista.</p>";
                } else {
                    foreach ($lista_quero_paginada as $livro) {
                        exibirCardLivroLeitura($livro);
                    }
                }
                ?>
            </div>

            <?php if ($paginas_quero > 1): ?>
                <div class="paginacao-leitura">
                    <?php if ($p_quero > 1): ?>
                        <a href="leitura.php?p_quero=<?php echo $p_quero - 1; ?>&aba_aberta=quero">&laquo; Anterior</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $paginas_quero; $i++): ?>
                        <a href="leitura.php?p_quero=<?php echo $i; ?>&aba_aberta=quero" style="<?php echo $i === $p_quero ? 'background: #3498db; color: white; border-color: #3498db; font-weight: bold;' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($p_quero < $paginas_quero): ?>
                        <a href="leitura.php?p_quero=<?php echo $p_quero + 1; ?>&aba_aberta=quero">Próximo &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="accordion-secao <?php echo (isset($_GET['aba_aberta']) && $_GET['aba_aberta'] === 'lendo') || (isset($_GET['p_lendo']) && intval($_GET['p_lendo']) > 1) ? 'ativo' : ''; ?>" id="secao-lendo">
        <button class="accordion-botao" onclick="toggleAccordion('secao-lendo')">
            <h2 class="accordion-botao-titulo">
                <img src="assets/lendo.png" class="icone-titulo-lista" alt="Lendo"> 
                Lendo (<?php echo $tot_lendo; ?>)
            </h2>
        </button>
        <div class="accordion-conteudo">
            <div class="topo-filtro-flex">
                <span style="font-size: 0.88em; color: #666;">Ordenar:</span>
                <select class="select-ordenacao" onchange="ordenarCards(this, 'lendo')">
                    <option value="recentes">Adicionados recentemente</option>
                    <option value="antigos">Adicionados há mais tempo</option>
                    <option value="alfabetica">Ordem alfabética</option>
                </select>
            </div>
            <div id="lendo">
                <?php 
                if (empty($lista_lendo_paginada)) {
                    echo "<p style='color: #7f8c8d; font-style: italic;'>Nenhum livro nesta lista.</p>";
                } else {
                    foreach ($lista_lendo_paginada as $livro) {
                        exibirCardLivroLeitura($livro);
                    }
                }
                ?>
            </div>

            <?php if ($paginas_lendo > 1): ?>
                <div class="paginacao-leitura">
                    <?php if ($p_lendo > 1): ?>
                        <a href="leitura.php?p_lendo=<?php echo $p_lendo - 1; ?>&aba_aberta=lendo">&laquo; Anterior</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $paginas_lendo; $i++): ?>
                        <a href="leitura.php?p_lendo=<?php echo $i; ?>&aba_aberta=lendo" style="<?php echo $i === $p_lendo ? 'background: #3498db; color: white; border-color: #3498db; font-weight: bold;' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($p_lendo < $paginas_lendo): ?>
                        <a href="leitura.php?p_lendo=<?php echo $p_lendo + 1; ?>&aba_aberta=lendo">Próximo &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="accordion-secao <?php echo (isset($_GET['aba_aberta']) && $_GET['aba_aberta'] === 'lido') || (isset($_GET['p_lido']) && intval($_GET['p_lido']) > 1) ? 'ativo' : ''; ?>" id="secao-lido">
        <button class="accordion-botao" onclick="toggleAccordion('secao-lido')">
            <h2 class="accordion-botao-titulo">
                <img src="assets/lido.png" class="icone-titulo-lista" alt="Lido"> 
                Lido (<?php echo $tot_lido; ?>)
            </h2>
        </button>
        <div class="accordion-conteudo">
            <div class="topo-filtro-flex">
                <span style="font-size: 0.88em; color: #666;">Ordenar:</span>
                <select class="select-ordenacao" onchange="ordenarCards(this, 'lido')">
                    <option value="recentes">Adicionados recentemente</option>
                    <option value="antigos">Adicionados há mais tempo</option>
                    <option value="alfabetica">Ordem alfabética</option>
                </select>
            </div>
            <div id="lido">
                <?php 
                if (empty($lista_lido_paginada)) {
                    echo "<p style='color: #7f8c8d; font-style: italic;'>Nenhum livro nesta lista.</p>";
                } else {
                    foreach ($lista_lido_paginada as $livro) {
                        exibirCardLivroLeitura($livro);
                    }
                }
                ?>
            </div>

            <?php if ($paginas_lido > 1): ?>
                <div class="paginacao-leitura">
                    <?php if ($p_lido > 1): ?>
                        <a href="leitura.php?p_lido=<?php echo $p_lido - 1; ?>&aba_aberta=lido">&laquo; Anterior</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $paginas_lido; $i++): ?>
                        <a href="leitura.php?p_lido=<?php echo $i; ?>&aba_aberta=lido" style="<?php echo $i === $p_lido ? 'background: #3498db; color: white; border-color: #3498db; font-weight: bold;' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($p_lido < $paginas_lido): ?>
                        <a href="leitura.php?p_lido=<?php echo $p_lido + 1; ?>&aba_aberta=lido">Próximo &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<div id="toast-container"></div>

<script>
function toggleAccordion(idSecao) {
    const secao = document.getElementById(idSecao);
    if (secao) secao.classList.toggle('ativo');
}

function ordenarCards(select, idContainer) {
    const container = document.getElementById(idContainer);
    const cards = Array.from(container.querySelectorAll('.livro'));
    const criterio = select.value;

    if (cards.length === 0) return;

    cards.sort((a, b) => {
        if (criterio === 'recentes') {
            return parseInt(b.getAttribute('data-index')) - parseInt(a.getAttribute('data-index'));
        } else if (criterio === 'antigos') {
            return parseInt(a.getAttribute('data-index')) - parseInt(b.getAttribute('data-index'));
        } else if (criterio === 'alfabetica') {
            const tA = a.getAttribute('data-titulo').toLowerCase();
            const tB = b.getAttribute('data-titulo').toLowerCase();
            return tA.localeCompare(tB, 'pt-BR');
        }
    });

    container.innerHTML = '';
    cards.forEach(card => container.appendChild(card));
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.select-ordenacao').forEach(select => {
        select.dispatchEvent(new Event('change'));
    });
});

function mostrarToastLeitura(mensagem, tipo = 'sucesso') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
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
    if (menu) menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

document.addEventListener('click', function() {
    const menu = document.getElementById('dropdownMenu');
    if (menu) menu.style.display = 'none';
});

<?php
if (isset($_SESSION['toast_mensagem'])) {
    echo "mostrarToastLeitura('" . $_SESSION['toast_mensagem'] . "', '" . $_SESSION['toast_tipo'] . "');";
    unset($_SESSION['toast_mensagem']);
    unset($_SESSION['toast_tipo']);
}
?>
</script>

</body>
</html>