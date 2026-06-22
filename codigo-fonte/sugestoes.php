<?php
if (basename($_SERVER['PHP_SELF']) == 'sugestoes.php') {
    exit('Acesso direto não permitido.');
}

// ESTADO 1: USUÁRIO VISITANTE (NÃO LOGADO)
if (!isset($_SESSION['usuario_id'])): ?>
    <div class="card-empty-state" style="background-color: rgb(205, 220, 235); border-left: 4px solid #2c3e50; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
        <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 1.2em;">Desbloqueie Recomendações Personalizadas!</h4>
        <p style="margin: 0 0 15px 0; color: #57606f;">Crie uma conta para receber recomendações exclusivas de livros baseadas no seu gosto literário.</p>
        <a href="cadastro.php" style="  text-decoration: none;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: bold;
  transition: all 0.3s ease;
  display: inline-block;   color: white !important;
  background: #2c3e50;">Cadastrar-se agora</a>
    </div>

<?php else: 
    $id_usuario = intval($_SESSION['usuario_id']);

    $sql_interacoes = "SELECT 
        (SELECT COUNT(*) FROM listas_leitura WHERE id_usuario = $id_usuario) + 
        (SELECT COUNT(*) FROM favoritos WHERE usuario_id = $id_usuario) AS total";
    
    $res_interacoes = $conn->query($sql_interacoes);
    $total_interacoes = 0;
    if ($res_interacoes) {
        $dados_int = $res_interacoes->fetch_assoc();
        $total_interacoes = intval($dados_int['total']);
    }

    // ESTADO 2: USUÁRIO LOGADO SEM HISTÓRICO
    if ($total_interacoes === 0): ?>
        <div class="card-empty-state" style="background-color:rgb(205, 220, 235); border-left: 4px solid #2c3e50; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 1.2em;">Seu feed de recomendações está quase pronto!</h4>
            <p style="margin: 0; color: #57606f; line-height: 1.5;">
            <strong>Adicione livros na lista de leitura</strong> ou favorite-os clicando no ícone de <strong>coração</strong> para que nosso algoritmo entenda o seu perfil e recomende as melhores leituras para você!
            </p>
        </div>

    <?php else: 
        // ESTADO 3: USUÁRIO COM HISTÓRICO (ALGORITMO)
        $sql_recomendacoes = "
            SELECT l.*, generos_score.score
            FROM livros l
            INNER JOIN (
                SELECT genre_calc.genero, SUM(genre_calc.peso) as score
                FROM (
                    SELECT l_sub.genero, 1 as peso 
                    FROM listas_leitura ll_sub
                    INNER JOIN livros l_sub ON ll_sub.id_livro = l_sub.id
                    WHERE ll_sub.id_usuario = $id_usuario AND ll_sub.status = 'quero ler'
                    
                    UNION ALL
                    
                    SELECT l_sub.genero, 2 as peso 
                    FROM listas_leitura ll_sub
                    INNER JOIN livros l_sub ON ll_sub.id_livro = l_sub.id
                    WHERE ll_sub.id_usuario = $id_usuario AND ll_sub.status IN ('lendo', 'lido')
                    
                    UNION ALL
                    
                    SELECT l_fav.genero, 3 as peso 
                    FROM favoritos f_sub
                    INNER JOIN livros l_fav ON f_sub.livro_id = l_fav.id
                    WHERE f_sub.usuario_id = $id_usuario
                ) as genre_calc
                GROUP BY genre_calc.genero
            ) as generos_score ON l.genero = generos_score.genero
            WHERE l.id NOT IN (
                SELECT id_livro FROM listas_leitura WHERE id_usuario = $id_usuario
            )
            ORDER BY generos_score.score DESC, l.id DESC
            LIMIT 4";

        $result_rec = $conn->query($sql_recomendacoes);

        if ($result_rec && $result_rec->num_rows > 0): ?>
            
            <h2 style="display: flex; align-items: center; gap: 10px; margin-top: 30px; color: #2c3e50;">
                <span style="font-size: 1.3em;"><img src='assets/recomendados.png' style=' width: 24px; height: 24px; object-fit: contain;'></span> Recomendados para Você
            </h2>
            
            <div class="prateleira-recomendacoes" style="display: flex; gap: 20px; overflow-x: auto; padding: 15px 5px; margin-bottom: 30px;">
                
                <?php while ($livro_rec = $result_rec->fetch_assoc()): 
                    $capa = !empty($livro_rec['capa']) ? $livro_rec['capa'] : 'assets/sem-capa.png';
                    
                    // Prepara os dados do livro de forma segura para passar pro JavaScript do Modal
                    $jsonLivro = json_encode([
                        'id' => $livro_rec['id'],
                        'titulo' => $livro_rec['titulo'],
                        'autor' => $livro_rec['autor'],
                        'genero' => $livro_rec['genero'],
                        'ano' => $livro_rec['ano'] ?? 'Não informado',
                        'descricao' => $livro_rec['descricao'] ?? 'Sem sinopse disponível.',
                        'capa' => $capa
                    ], JSON_HEX_APOS | JSON_HEX_QUOT);
                ?>
                    <div class="card-recomendado" style="">
                        <div>
                            <div style="text-align: center; margin-bottom: 12px;">
                                <img src="<?php echo $capa; ?>" style="width: 110px; height: 160px; object-fit: cover; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
                            </div>
                            <h4 style="margin: 0 0 8px 0; font-size: 1.05em; color: #2c3e50; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($livro_rec['titulo']); ?>">
                                <?php echo htmlspecialchars($livro_rec['titulo']); ?>
                            </h4>
                            <p style="margin: 0 0 8px 0; font-size: 0.9em; color: #7f8c8d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><strong>Autor:</strong> <?php echo htmlspecialchars($livro_rec['autor']); ?></p>
                            <span style="display: inline-block; background: #f1f2f6; color: #57606f; font-size: 0.8em; padding: 3px 8px; border-radius: 20px; margin-bottom: 15px;">
                             <?php echo htmlspecialchars($livro_rec['genero']); ?>
                            </span>
                        </div>
                        
                        <button class='btn-sinopse' onclick='abrirModalRecomendacao(<?php echo $jsonLivro; ?>, true)'>
                            Ver sinopse
                        </button>
                    </div>
                <?php endwhile; ?>
                
            </div>

            <script>
            // Executa a ação do botão e recarrega as recomendações
            function executarAcaoModal(idLivro, status, titulo) {
                // Fecha o modal removendo a classe do index
                document.getElementById('modalRec').classList.remove('mostrar');
                
                // Executa a função global que está no index.php
                adicionarLista(idLivro, status, titulo);
                
                // Pequeno delay para recarregar e atualizar a lista da prateleira de sugestões
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            }
            </script>

        <?php 
        endif;
    endif;
endif; 
?>