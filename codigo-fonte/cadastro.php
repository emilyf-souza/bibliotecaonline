<?php 
session_start(); 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Biblioteca Virtual</title>
    
    <link rel="stylesheet" href="assets/style.css?v=1010">
    
    <link rel="icon" href="assets/favicon.png?v=1">
    
    <style>
        .cadastro-container {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            font-family: Arial, sans-serif;
        }
        
        .form-group {
            margin-bottom: 15px;
            position: relative;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        
        .form-group input {
            width: 100%;
            padding: 8px;
            padding-right: 40px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn-cadastro {
            width: 100%;
            padding: 10px;
            background-color: #007BFF; 
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-cadastro:hover {
            background-color: #0056b3;
        }
        .links-uteis {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="cadastro-container">
        <h2>Criar Nova Conta</h2>
        
        <form action="cadastrar.php" method="POST">
            
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required placeholder="Seu nome">
            </div>

            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
            </div>

            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required placeholder="Crie uma senha">

                <button type="button" id="btn-olho" style="background: transparent; border:none; position: absolute; right: 10px; top: 40px; cursor: pointer; box-shadow: none;" onclick="toggleSenha()">
                   <img src="assets/olho-fechado.png" id="icone-olho" alt="Mostrar senha" style="width: 20px; height: auto;"> 
                </button>
            </div>

            <button type="submit" class="btn-cadastro">Cadastrar</button>
        </form>

        <div class="links-uteis">
            <a href="login.php">Já tem uma conta? Faça login aqui</a>
        </div>
    </div>

    <div id="toast-container"></div>

<script>
// Função que aplica exatamente a classe recebida do PHP ('erro' ou 'sucesso')
function mostrarToastCadastro(mensagem, tipo = 'sucesso') {
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

function toggleSenha() {
    const campoSenha = document.getElementById('senha');
    const iconeOlho = document.getElementById('icone-olho');
    
    if (campoSenha.type === 'password') {
        campoSenha.type = 'text';
        iconeOlho.src = 'assets/olho-aberto.png'; 
        iconeOlho.alt = 'Ocultar senha';
    } else {
        campoSenha.type = 'password';
        iconeOlho.src = 'assets/olho-fechado.png'; 
        iconeOlho.alt = 'Mostrar senha';
    }
}
</script>

<?php
// Captura o aviso do leitura.php e exibe na tela de cadastro!
if (isset($_SESSION['toast_mensagem'])) {
    echo "<script>mostrarToastCadastro('" . $_SESSION['toast_mensagem'] . "', '" . $_SESSION['toast_tipo'] . "');</script>";
    unset($_SESSION['toast_mensagem']);
    unset($_SESSION['toast_tipo']);
}
?>

</body>
</html>
