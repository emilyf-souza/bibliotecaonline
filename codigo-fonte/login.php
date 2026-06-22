<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Biblioteca Virtual</title>
    <link rel="stylesheet" href="assets/style.css"> 
    <style>

        .login-container {
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
        
        .btn-login {
            width: 100%;
            padding: 10px;
            background-color: #007BFF; 
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        .btn-login:hover {
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

    <div class="login-container">
        <h2>Acessar Conta</h2>
        
        <form action="logar.php" method="POST">
            
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
            </div>

            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required placeholder="Sua senha">
                
                <button type="button" id="btn-olho" style="background: transparent; border:none; position: absolute; right: 10px; top: 40px; cursor: pointer; box-shadow: none;" onclick="toggleSenha()">
                   <img src="assets/olho-fechado.png" id="icone-olho" alt="Mostrar senha" style="width: 20px; height: auto;"> 
                </button>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <div class="links-uteis">
            <a href="cadastro.php">Não tem uma conta? Cadastre-se aqui</a>
        </div>
    </div>

    <div id="toast-container"></div>

<script>

// Função padrão para desenhar Toasts na tela
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
if (isset($_SESSION['toast_mensagem'])) {
    echo "<script>mostrarToastLogin('" . $_SESSION['toast_mensagem'] . "', '" . $_SESSION['toast_tipo'] . "');</script>";
    unset($_SESSION['toast_mensagem']);
    unset($_SESSION['toast_tipo']);
}
?>

</body>
</html>