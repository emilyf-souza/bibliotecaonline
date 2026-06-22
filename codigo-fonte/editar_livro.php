<?php
session_start();

// Se o usuário não estiver logado OU não for admin, ele é barrado
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    echo "<script>alert('Acesso negado! Apenas administradores podem acessar esta página.'); window.location.href='index.php';</script>";
    exit(); // Interrompe a execução do código imediatamente
}
?>

<?php
require_once "db/conexao.php";

if (!isset($_GET['id'])) {
    die("ID do livro não informado.");
}

$id = $_GET['id'];

// Buscar livro
$sql = "SELECT * FROM livros WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Livro não encontrado.");
}

$livro = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Livro</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

<h1>✏️ Editar Livro</h1>

<form action="atualizar_livro.php" method="POST">

    <input type="hidden" name="id" value="<?php echo $livro['id']; ?>">

    <label>Título:</label>
    <input type="text" name="titulo" value="<?php echo htmlspecialchars($livro['titulo']); ?>" required>

    <label>Autor:</label>
    <input type="text" name="autor" value="<?php echo htmlspecialchars($livro['autor']); ?>" required>

    <label>Gênero:</label>
<select name="genero">
    <option value="Fantasia" <?php if($livro['genero']=="Fantasia") echo "selected"; ?>>Fantasia</option>
    <option value="Terror" <?php if($livro['genero']=="Terror") echo "selected"; ?>>Terror</option>
    <option value="Aventura" <?php if($livro['genero']=="Aventura") echo "selected"; ?>>Aventura</option>
    <option value="Biografia" <?php if($livro['genero']=="Biografia") echo "selected"; ?>>Biografia</option>
    <option value="Romance" <?php if($livro['genero']=="Romance") echo "selected"; ?>>Romance</option>
</select>

    <label>Ano:</label>
    <input type="number" name="ano" value="<?php echo htmlspecialchars($livro['ano']); ?>">

    <div class="linha-sinopse">
    <label>Sinopse:</label>
    <textarea 
    name="descricao" 
    class="sinopse-edit" 
    style="resize:none; width:500px; height:150px; overflow-y:auto; align-items: center;" 
    required
><?php echo htmlspecialchars($livro['descricao']); ?></textarea>
</div>

    <button type="submit">Salvar alterações</button>

</form>

<br>

<a href="index.php">⬅ Voltar</a>

</div>

</body>
</html>