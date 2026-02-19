<?php
session_start();
include_once 'config/conexao.php'; // Garanta que este caminho está correto

// 1. Segurança: Verifica login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id'];

// 2. Consulta no Banco (Apenas tickets deste usuário)
$sql = "SELECT id, tipo, patrimonio, assunto, categoria, prioridade, status, data_abertura 
        FROM chamados 
        WHERE usuario_id = ? 
        ORDER BY data_abertura DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/meus-tickets.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="assets/img/F.png">
    <script src="assets/js/script.js"></script>
    <title>ForteCare | Meus Tickets</title>
</head>

<body>
    <Header>
        <div class="logo">
            <img src="assets/img/fortecare h branco.png" alt="Logo ForteCare">
        </div>

        <div class="navigation" id="navigation">
            <nav>
                <a href="inicio.php">Inicio</a>
                <a href="meus-tickets.php"  class="select-a">Meus Tickets</a>
                <a href="adm-painel.php" >Central de Administração</a>
            </nav>
        </div>


        <div class="btn-account">
            <div class="user-menu">
                <a href="#" class="btn-usuario" id="btnUsuario" title="Minha Conta">
                    <i class='bx bxs-user'></i>
                </a>

                <div class="user-dropdown" id="userDropdown">
                    <div class="txt-user-div">
                        <h3 class="txt-user">
                            Minha conta
                        </h3>   
                    </div>

                    <p class="user-name"> <span><Strong>Nome: </Strong></span><?= $_SESSION['nome'] ?></p>
                    <p class="user-email"> <span><Strong>E-mail: </Strong></span><?= $_SESSION['email'] ?></p>

                <div class="user-info">
                    <span><strong>Setor:</strong> <?= $_SESSION['setor'] ?></span>

                </div>
                <div class="user-info">
                    <span><strong>Perfil:</strong> <?= $_SESSION['perfil'] ?></span>
                </div>

                <div class="txt-p">
                    <p>
                        <span>* </span>Caso seja necessário editar alguma informação, entre em contato com o setor de TI.
                    </p>
                </div>
                    <div class="user-actions">
                        <a href="alterar-senha.php" class="btn-action">Alterar Senha</a>
                        <a href="logout.php" class="btn-logout">Sair</a>
                    </div>
                </div>
            </div>
        </div>

                    <!-- BOTÃO HAMBURGUER (MOBILE) -->
        <div class="menu-toggle" id="menuToggle">
            <i class='bx bx-menu'></i>
        </div>
    </Header>

    <section class="wrapper">   
        <article class="content-box">
            
            <div class="tickets-header">
                <h2>Meus Tickets</h2>
                <a href="novo-ticket.php" class="btn-novo">
                    <i class='bx bx-plus'></i> Novo Ticket
                </a>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
                <div class="alert-success">
                    <i class='bx bx-check-circle'></i> Ticket criado com sucesso!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso_conclusao'): ?>
                <div class="alert-success" >
                    <i class='bx bx-check-double'></i> Chamado concluído e fechado com sucesso!
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table-tickets">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Patrimônio</th>
                            <th>Categoria</th>
                            <th>Assunto</th>
                            <th>Prioridade</th>
                            <th>Status</th>
                            <th>Confirmação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($ticket = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?= $ticket['id'] ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($ticket['data_abertura'])) ?></td>
                                    <td><?= htmlspecialchars($ticket['tipo']) ?></td>
                                    <td><?= htmlspecialchars($ticket['patrimonio']) ?></td>
                                    <td><?= htmlspecialchars($ticket['categoria']) ?></td>
                                    <td><?= htmlspecialchars($ticket['assunto']) ?></td>
                                    
                                    <td>
                                        <span class="priority <?= strtolower($ticket['prioridade']) ?>">
                                            <?= htmlspecialchars($ticket['prioridade']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php 
                                            $cssStatus = strtolower(str_replace(' ', '-', $ticket['status'])); 
                                        ?>
                                        <span class="status-badge <?= $cssStatus ?>">
                                            <?= htmlspecialchars($ticket['status']) ?>
                                        </span>
                                    </td>

                                    <td >
                                        <?php 
                                            // Verifica os status
                                            $status = $ticket['status'];
                                            $isConcluido = ($status == 'Concluido');
                                            $podeMarcar = ($status == 'Resolvido'); // Status onde o técnico diz que acabou
                                        ?>

                                        <input type="checkbox" 
                                            class="check-conclusao"
                                            
                                            <?php if ($isConcluido) echo 'checked disabled'; ?>
                                            <?php if (!$isConcluido && !$podeMarcar) echo 'disabled'; ?>
                                            
                                            onclick="confirmarConclusao(this, <?= $ticket['id'] ?>)"
                                            title="<?= $podeMarcar ? 'Marcar como resolvido' : 'Aguarde o técnico resolver' ?>">
                                    </td>
                                    
                                    <td >
                                        <a href="ver-ticket.php?id=<?= $ticket['id'] ?>" class="btn-view" title="Ver Detalhes">
                                            <i class='bx bx-search-alt'></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="empty-state">
                                    <i class='bx bx-ghost'></i>
                                    <p>Você ainda não abriu nenhum chamado.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </article>
    </section>

    <footer class="copy">
        <section>
            <p>&copy; 2026 ForteCare. Todos os direitos reservados.</p>
        </section>
        <section>
            <p> Desenvolvido com ❤️ por <a href="#" target="_blank">HR | DEV</a>.</p>
        </section>
    </footer>

<script>
    function confirmarConclusao(checkbox, idTicket) {
        if (confirm('O técnico marcou como resolvido. Você confirma a conclusão do chamado?')) {
            // ATENÇÃO AQUI: Não use "adm/", use o caminho relativo correto.
            // Se "meus-tickets.php" está na raiz, o caminho é "adm/confirmar_ticket.php".
            window.location.href = "adm/confirmar-ticket.php?id=" + idTicket;
        } else {
            checkbox.checked = false;
        }
    }
</script>
</body>
</html>