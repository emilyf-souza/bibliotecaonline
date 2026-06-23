<?php
session_start();
require_once "db/conexao.php";

// Se o usuário não estiver logado, chuta de volta para o cadastro/login
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['toast_mensagem'] = "Você precisa estar logado para acessar seu perfil!";
    $_SESSION['toast_tipo'] = "erro";
    header("Location: cadastro.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// Busca os dados atualizados do usuário no banco de dados
$sql_user = "SELECT nome, email, senha FROM usuarios WHERE id = $id_usuario";
$res_user = $conn->query($sql_user);
$usuario = $res_user->fetch_assoc();

// ========================================== //
// PROCESSAMENTO DO FORMULÁRIO DE ATUALIZAÇÃO //
// ========================================== //

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_nome = $conn->real_escape_string(trim($_POST['nome']));
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    $sucesso = true;
    $erros = [];

    // Atualização do Nome (Sempre permitida se não estiver vazio)
    if (!empty($novo_nome) && $novo_nome !== $usuario['nome']) {
        $conn->query("UPDATE usuarios SET nome = '$novo_nome' WHERE id = $id_usuario");
        $_SESSION['usuario_nome'] = $novo_nome; // Atualiza o nome na sessão
        $usuario['nome'] = $novo_nome; // Atualiza a variável para exibir na tela
    }

    // Fluxo de Alteração de Senha (Só entra se o usuário preencher os campos de senha)
    if (!empty($senha_atual) || !empty($nova_senha) || !empty($confirmar_senha)) {
        
        // Validação básica: preencheu um, tem que preencher todos
        if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
            $erros[] = "Para alterar a senha, preencha todos os campos de senha.";
            $sucesso = false;
        } else {
            // TRAVA DE SEGURANÇA 1: Verifica se a senha atual confere com o banco
            if (!password_verify($senha_atual, $usuario['senha'])) {
                $erros[] = "A senha atual informada está incorreta.";
                $sucesso = false;
            }
            
            // TRAVA DE SEGURANÇA 2: Verifica se a nova senha bate com a confirmação
            if ($nova_senha !== $confirmar_senha) {
                $erros[] = "A nova senha e a confirmação não coincidem.";
                $sucesso = false;
            }

            // Se passou em todas as travas, faz a alteração criptografada
            if ($sucesso) {
                $nova_senha_cripto = password_hash($nova_senha, PASSWORD_DEFAULT);
                $conn->query("UPDATE usuarios SET senha = '$nova_senha_cripto' WHERE id = $id_usuario");
                
                // Atualiza o array local para o caso de novas validações na mesma requisição
                $usuario['senha'] = $nova_senha_cripto; 
                $_SESSION['toast_mensagem'] = "Dados e senha atualizados com sucesso!";
                $_SESSION['toast_tipo'] = "sucesso";
            }
        }
    } else {
        // Se o usuário só mudou o nome e não tentou mexer na senha
        $_SESSION['toast_mensagem'] = "Nome atualizado com sucesso!";
        $_SESSION['toast_tipo'] = "sucesso";
    }

    // Se houve erros de validação de senha, configura o Toast de erro
    if (!$sucesso && !empty($erros)) {
        $_SESSION['toast_mensagem'] = implode(" | ", $erros);
        $_SESSION['toast_tipo'] = "erro";
    }

    header("Location: perfil.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil - Biblioteca</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" href="assets/favicon.png?v=1">
    <style>

        .perfil-box {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            max-width: 500px;
            margin: 40px auto;
        }
        .form-grupo {
            margin-bottom: 20px;
        }
        .form-grupo label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        .form-grupo input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .secao-divisoria {
            border-top: 1px solid #eee;
            margin-top: 30px;
            padding-top: 20px;
        }
        .btn-salvar-perfil {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
            font-weight: bold;
        }
        .btn-salvar-perfil:hover { background-color: #27ae60; }
        .email-bloqueado { background-color: #f5f5f5; color: #7f8c8d; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="container">

<div style="margin-bottom: 50px;">
<a href="index.php" class="btn-opcoes">￩ Voltar</a>
    </div>

    <div class="perfil-box">
        <h2>Gerenciar Meus Dados</h2>
        <p style="color: #7f8c8d; font-size: 0.9em; margin-bottom: 25px;">Altere seu nome ou atualize sua senha de acesso com segurança.</p>

        <form method="POST" action="perfil.php">
            <div class="form-grupo">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
            </div>

            <div class="form-grupo">
                <label>E-mail (Não alterável)</label>
                <input type="text" class="email-bloqueado" value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly>
            </div>

            <div class="secao-divisoria">
                <h3 style="margin-bottom: 15px; color: #2c3e50;">Alterar Senha</h3>
                <p style="color: #95a5a6; font-size: 0.85em; margin-bottom: 15px;">Deixe estes campos em branco caso queira manter sua senha atual.</p>
                
                <div class="form-grupo">
                    <label for="senha_atual">Senha Atual</label>
                    <input type="password" id="senha_atual" name="senha_atual" placeholder="Digite sua senha corrente">
                </div>

                <div class="form-grupo">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-grupo">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a nova senha">
                </div>
            </div>

            <button type="submit" class="btn-salvar-perfil">Salvar Alterações</button>
        </form>
    </div>
</div>

<div id="toast-container"></div>

<script>
    
// Mantendo o sistema de feedback visual dinâmico (Toast)
function mostrarToastPerfil(mensagem, tipo = 'sucesso') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast-alerta toast-${tipo}`;
    toast.innerHTML = `<span>${mensagem}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-sumir');
        setTimeout(() => { toast.remove(); }, 600);
    }, 4000);
}

<?php
if (isset($_SESSION['toast_mensagem'])) {
    echo "mostrarToastPerfil('" . $_SESSION['toast_mensagem'] . "', '" . $_SESSION['toast_tipo'] . "');";
    unset($_SESSION['toast_mensagem']);
    unset($_SESSION['toast_tipo']);
}
?>
</script>
</body>
</html>
