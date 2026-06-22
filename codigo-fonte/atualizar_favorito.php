<?php
session_start();
require_once "db/conexao.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Você precisa estar logado para favoritar livros!']);
    exit;
}

if (!isset($_POST['id_livro'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos.']);
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$livro_id = intval($_POST['id_livro']);

// Verifica se já está favoritado
$check = $conn->query("SELECT id FROM favoritos WHERE usuario_id = $usuario_id AND livro_id = $livro_id");

if ($check && $check->num_rows > 0) {
    // Se já está favoritado, o usuário clicou para desfavoritar (remover)
    $conn->query("DELETE FROM favoritos WHERE usuario_id = $usuario_id AND livro_id = $livro_id");
    echo json_encode(['status' => 'sucesso', 'acao' => 'removido']);
} else {
    // Se não estava favoritado, insere no banco
    $conn->query("INSERT INTO favoritos (usuario_id, livro_id) VALUES ($usuario_id, $livro_id)");
    echo json_encode(['status' => 'sucesso', 'acao' => 'adicionado']);
}
exit;