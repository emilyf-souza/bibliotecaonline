<?php
session_start();
require_once "db/conexao.php";

// Se o usuário não estiver logado, não deixa salvar e avisa
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'toast-erro', 'mensagem' => 'Você precisa estar logado para gerenciar suas listas!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['usuario_id'];
    $id_livro = intval($_POST['id_livro']);
    $status = $conn->real_escape_string($_POST['status']);

    if (!in_array($status, ['quero ler', 'lendo', 'lido'])) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Status inválido']);
        exit;
    }

    //Descobrir o status atual (origem) antes de mudar
    $status_origem = "NULL";
    $sql_busca_antigo = "SELECT status FROM listas_leitura WHERE id_usuario = $id_usuario AND id_livro = $id_livro";
    $res_antigo = $conn->query($sql_busca_antigo);
    
    if ($res_antigo && $res_antigo->num_rows > 0) {
        $dado_antigo = $res_antigo->fetch_assoc();
        $status_origem = "'" . $dado_antigo['status'] . "'";
    }

    // Se já existir o registro do par (usuario, livro), ela faz um UPDATE. Se não existir, faz um INSERT
    $sql = "INSERT INTO listas_leitura (id_usuario, id_livro, status) 
            VALUES ($id_usuario, $id_livro, '$status') 
            ON DUPLICATE KEY UPDATE status = '$status'";

    if ($conn->query($sql)) {
        
        // Se a lista salvou com sucesso, grava no histórico
        $sql_historico = "INSERT INTO historico_leitura (id_usuario, id_livro, status_origem, status_destino) 
                          VALUES ($id_usuario, $id_livro, $status_origem, '$status')";
        $conn->query($sql_historico);

        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Lista atualizada com sucesso!']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao salvar no banco de dados.']);
    }
    exit;
}