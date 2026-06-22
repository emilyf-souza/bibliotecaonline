<?php
session_start();

// Se o usuário não estiver logado OU não for admin, ele é barrado
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    echo "<script>alert('Acesso negado! Apenas administradores podem acessar esta página.'); window.location.href='index.php';</script>";
    exit(); // Interrompe a execução do código
}
?>

<?php
require_once "db/conexao.php";

// EDITAR
$editando = false;
$livroEdit = null;

if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $q = $conn->query("SELECT * FROM livros WHERE id = $id");
    if ($q->num_rows > 0) {
        $livroEdit = $q->fetch_assoc();
        $editando = true;
    }
}

// EXCLUIR
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $conn->query("DELETE FROM livros WHERE id = $id");
    header("Location: add_livro.php");
    exit;
}

// INSERIR/EDITAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Protegendo as strings contra quebras de aspas no banco de dados
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $autor = $conn->real_escape_string($_POST['autor']);
    $genero = $conn->real_escape_string($_POST['genero']);
    $ano = intval($_POST['ano']);
    $descricao = $conn->real_escape_string($_POST['descricao']);

    // Captura o link da capa digitado no formulário
    $capa = $conn->real_escape_string($_POST['capa']);

    if (!empty($_POST['id'])) {
        $id = intval($_POST['id']);

        // SQL DE ATUALIZAÇÃO
        $conn->query("UPDATE livros SET 
            titulo='$titulo', autor='$autor',
            genero='$genero', ano='$ano',
            descricao='$descricao', capa='$capa'
        WHERE id = $id");
    } else {

        // SQL DE INSERÇÃO
        $conn->query("INSERT INTO livros (titulo, autor, genero, ano, descricao, capa)
            VALUES ('$titulo', '$autor', '$genero', '$ano', '$descricao', '$capa')");
    }

    header("Location: add_livro.php");
    exit;
}

// BUSCAR LIVROS
$result = $conn->query("SELECT * FROM livros ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<link rel="stylesheet" href="assets/style.css?v=123">
<head>
    <meta charset="UTF-8">
    <title>Livros</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: auto;
            display: block;
        }

        h2 {
            margin-top: 40px;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 40px;
        }

        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #eee;
        }

        .botao {
            padding: 6px 10px;
            color: white;
            background: #3498db;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 6px;
            display: inline-block;
        }

        .excluir {
            background: #e74c3c;
        }
    </style>
</head>

<body>

<div class="container">

    <h2><?= $editando ? "Editar Livro" : "Adicionar Livro" ?></h2>

    <form method="POST">
        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= $livroEdit['id'] ?>">
        <?php endif; ?>

        <label>Título:</label>
        <input type="text" name="titulo" value="<?= $editando ? htmlspecialchars($livroEdit['titulo']) : '' ?>" required>

        <label>Autor:</label>
        <input type="text" name="autor" value="<?= $editando ? htmlspecialchars($livroEdit['autor']) : '' ?>" required>

        <label>Gênero:</label>
        <input type="text" name="genero" value="<?= $editando ? htmlspecialchars($livroEdit['genero']) : '' ?>" required>

        <label>Ano:</label>
        <input type="number" name="ano" value="<?= $editando ? htmlspecialchars($livroEdit['ano']) : '' ?>" required>

        <label>Link da Capa (URL da Imagem):</label>
        <input type="text" name="capa" placeholder="https://i.imgur.com/...png" value="<?= $editando ? htmlspecialchars($livroEdit['capa']) : '' ?>">

        <label>Descrição:</label>
        <textarea name="descricao" rows="4"><?= $editando ? htmlspecialchars($livroEdit['descricao']) : '' ?></textarea>

        <button type="submit"><?= $editando ? "Salvar alterações" : "Adicionar Livro" ?></button>
    </form>


    <h2>Livros cadastrados</h2>

    <?php if ($result->num_rows == 0): ?>
        <p>Nenhum livro cadastrado.</p>
    <?php else: ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Gênero</th>
                <th>Ano</th>
                <th>Ações</th>
            </tr>

            <?php while ($livro = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $livro['id'] ?></td>
                    <td><?= htmlspecialchars($livro['titulo']) ?></td>
                    <td><?= htmlspecialchars($livro['autor']) ?></td>
                    <td><?= htmlspecialchars($livro['genero']) ?></td>
                    <td><?= htmlspecialchars($livro['ano']) ?></td>

                    <td>
                        <a class="botao" href="add_livro.php?editar=<?= $livro['id'] ?>">Editar</a>
                        <a class="botao excluir" href="add_livro.php?excluir=<?= $livro['id'] ?>" onclick="return confirm('Excluir?')">Excluir</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

    <?php endif; ?>

</div>

</body>
</html>