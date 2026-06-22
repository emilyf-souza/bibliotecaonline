<?php
// Importa a conexão com o banco
require_once 'db/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Pega os dados e protege contra SQL Injection
    $nome = $conn->real_escape_string(htmlentities($_POST['nome']));
    $email = $conn->real_escape_string(htmlentities($_POST['email']));
    $senha = $_POST['senha']; 

    // Verifica se o e-mail digitado já existe no banco de dados
    $sql_checar = "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1";
    $resultado_checar = $conn->query($sql_checar);

    if ($resultado_checar && $resultado_checar->num_rows > 0) {
        // Se já existe cadastro com o e-mail, barra o cadastro
        echo "<script>alert('Este e-mail já está cadastrado! Tente outro.'); window.location.href='cadastro.html';</script>";
    } else {
        // Se o e-mail for novo, insere na tabela de usuários
        // Concede ao usuário o tipo 'comum' como padrão
        $sql_inserir = "INSERT INTO usuarios (nome, email, senha) VALUES ('$nome', '$email', '$senha')";

        if ($conn->query($sql_inserir) === TRUE) {
            // Cadastro feito com sucesso
            echo "<script>alert('Conta criada com sucesso! Faça seu login.'); window.location.href='login.html';</script>";
        } else {
            // Erro inesperado do banco
            echo "<script>alert('Erro ao criar conta. Tente novamente.'); window.location.href='cadastro.html';</script>";
        }
    }
} else {
    // Bloqueia o acesso direto pela URL
    header("Location: cadastro.html");
    exit();
}
?>

<div id="toast-container"></div>

<script>
// Função para desenhar o Toast na tela
function mostrarToastLogin(mensagem, tipo = 'sucesso') {
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
</script>