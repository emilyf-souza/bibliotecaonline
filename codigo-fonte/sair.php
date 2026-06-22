<?php
session_start();
session_unset(); // Limpa todas as variáveis da sessão
session_destroy(); // Destrói a sessão ativa

// Redireciona o usuário de volta para a página inicial
header("Location: index.php");
exit();
?>