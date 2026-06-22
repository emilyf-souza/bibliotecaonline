<?php
session_start();

require_once 'db/conexao.php';

// Verifica se os dados vieram via método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os dados digitados e protege contra invasões simples (SQL Injection)
    $email = $conn->real_escape_string(htmlentities($_POST['email']));
    $senha = $_POST['senha']; 

    // Busca no banco de dados se existe um usuário com o e-mail digitado
    $sql = "SELECT * FROM usuarios WHERE email = '$email' LIMIT 1";
    $resultado = $conn->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        // Se encontrou o e-mail, pega os dados do usuário do banco
        $usuario = $resultado->fetch_assoc();

        // Verifica se a senha digitada bate com a senha do banco
        if ($senha === $usuario['senha']) {
            
            // Se a senha estiver correta: Preenche a sessão com os dados do usuário
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];

            // Redireciona o usuário para a página principal do site
            header("Location: index.php");
            exit();

        } else {
            // Se a senha estiver incorreta: Mostra mensagem informando 
            echo "<script>alert('E-mail ou senha incorretos!'); window.location.href='login.html';</script>";
        }
    } else {
        // E-mail não encontrado
        echo "<script>alert('E-mail ou senha incorretos!'); window.location.href='login.html';</script>";
    }
} else {
    // Se tentarem acessar esse arquivo direto pela URL, manda de volta para o login
    header("Location: login.html");
    exit();
}
?>