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

$id = $_GET['id'];

$conn->query("DELETE FROM livros WHERE id=$id");

header("Location: index.php?msg=Livro excluído com sucesso!");
?>