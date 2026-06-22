<?php
require_once "db/conexao.php";

$id = $_POST['id'];
$titulo = $_POST['titulo'];
$autor = $_POST['autor'];
$genero = $_POST['genero'];
$ano = $_POST['ano'];
$descricao = $_POST['descricao'];

$sql = "UPDATE livros SET 
        titulo='$titulo',
        autor='$autor',
        genero='$genero',
        ano='$ano',
        descricao='$descricao'
        WHERE id=$id";

if ($conn->query($sql)) {
    header("Location: index.php?msg=Livro atualizado com sucesso!");
} else {
    echo "Erro ao atualizar livro: " . $conn->error;
}
?>