<?php
require_once 'includes/funcoes.php';
redirect_if_not_logged();
start_session();

if (($_SESSION['profile'] ?? '') !== 'administrador') {
    header('Location: dashboard.php');
    exit;
}

$caminho_json = __DIR__ . '/conteudo.json';
$conteudo = json_decode(file_get_contents($caminho_json), true);

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---------- HOME ----------
    $conteudo['home']['titulo'] = trim($_POST['home_titulo']);
    $conteudo['home']['texto1'] = trim($_POST['home_texto1']);
    $conteudo['home']['texto2'] = trim($_POST['home_texto2']);

    // ---------- SOBRE NÓS ----------
    $conteudo['sobre_nos']['titulo'] = trim($_POST['sobre_nos_titulo']);
    $conteudo['sobre_nos']['texto'] = trim($_POST['sobre_nos_texto']);
    foreach ($_POST['sobre_nos_cards'] as $i => $card) {
        $conteudo['sobre_nos']['cards'][$i]['titulo'] = trim($card['titulo']);
        $conteudo['sobre_nos']['cards'][$i]['texto'] = trim($card['texto']);
    }

    // ---------- SERVIÇOS ----------
    $conteudo['servicos']['titulo'] = trim($_POST['servicos_titulo']);
    foreach ($_POST['servicos_cards'] as $i => $card) {
        $conteudo['servicos']['cards'][$i]['titulo'] = trim($card['titulo']);
        $conteudo['servicos']['cards'][$i]['texto'] = trim($card['texto']);
    }

    // ---------- VANTAGENS ----------
    $conteudo['vantagens']['titulo'] = trim($_POST['vantagens_titulo']);
    foreach ($_POST['vantagens_cards'] as $i => $card) {
        $conteudo['vantagens']['cards'][$i]['titulo'] = trim($card['titulo']);
        $conteudo['vantagens']['cards'][$i]['texto'] = trim($card['texto']);
    }

    // ---------- FUNCIONALIDADES ----------
    $conteudo['funcionalidades']['titulo'] = trim($_POST['funcionalidades_titulo']);
    foreach ($_POST['funcionalidades_artigos'] as $i => $artigo) {
        $conteudo['funcionalidades']['artigos'][$i]['titulo'] = trim($artigo['titulo']);
        $conteudo['funcionalidades']['artigos'][$i]['texto'] = trim($artigo['texto']);
    }

    // ---------- CONTACTO ----------
    $conteudo['contacto']['titulo'] = trim($_POST['contacto_titulo']);
    $conteudo['contacto']['texto'] = trim($_POST['contacto_texto']);

    // ---------- RODAPÉ ----------
    $conteudo['rodape']['localizacao'] = trim($_POST['rodape_localizacao']);
    $conteudo['rodape']['horario_semana'] = trim($_POST['rodape_horario_semana']);
    $conteudo['rodape']['horario_sabado'] = trim($_POST['rodape_horario_sabado']);
    $conteudo['rodape']['horario_domingo'] = trim($_POST['rodape_horario_domingo']);
    $conteudo['rodape']['email'] = trim($_POST['rodape_email']);
    $conteudo['rodape']['telefone'] = trim($_POST['rodape_telefone']);

    file_put_contents($caminho_json, json_encode($conteudo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $_SESSION['success_message'] = "Conteúdo da página pública atualizado com sucesso.";
    header('Location: editar_pagina_publica.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<?php if (!empty($success_message)) : ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div id="toastSuccess" class="toast align-items-center text-bg-success border-0 show" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($success_message) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 col-lg-10 p-4">
            <section>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0"><strong><i class="fa-solid fa-pen-to-square"></i> Editar Página Pública</strong></h2>
                </div>
                <hr>
            </section>

            <form method="POST">

                <!-- HOME -->
                <div class="card p-3 mb-4">
                    <h5 class="fw-bold" style="color:#0096a6;">Secção Home</h5>
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="home_titulo" class="form-control" value="<?= htmlspecialchars($conteudo['home']['titulo']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Texto 1</label>
                        <textarea name="home_texto1" class="form-control" rows="2" required><?= htmlspecialchars($conteudo['home']['texto1']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Texto 2</label>
                        <textarea name="home_texto2" class="form-control" rows="2" required><?= htmlspecialchars($conteudo['home']['texto2']) ?></textarea>
                    </div>
                </div>

                <!-- SOBRE NÓS -->
                <div class="card p-3 mb-4">
                    <h5 class="fw-bold" style="color:#0096a6;">Secção Sobre Nós</h5>
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="sobre_nos_titulo" class="form-control" value="<?= htmlspecialchars($conteudo['sobre_nos']['titulo']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Texto Introdutório</label>
                        <textarea name="sobre_nos_texto" class="form-control" rows="3" required><?= htmlspecialchars($conteudo['sobre_nos']['texto']) ?></textarea>
                    </div>

                    <?php foreach ($conteudo['sobre_nos']['cards'] as $i => $card) : ?>
                        <div class="border rounded p-3 mb-2">
                            <p class="fw-bold mb-2">Card <?= $i + 1 ?></p>
                            <div class="mb-2">
                                <label class="form-label">Título</label>
                                <input type="text" name="sobre_nos_cards[<?= $i ?>][titulo]" class="form-control" value="<?= htmlspecialchars($card['titulo']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Texto</label>
                                <textarea name="sobre_nos_cards[<?= $i ?>][texto]" class="form-control" rows="2" required><?= htmlspecialchars($card['texto']) ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- SERVIÇOS -->
                <div class="card p-3 mb-4">
                    <h5 class="fw-bold" style="color:#0096a6;">Secção Serviços</h5>
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="servicos_titulo" class="form-control" value="<?= htmlspecialchars($conteudo['servicos']['titulo']) ?>" required>
                    </div>

                    <?php foreach ($conteudo['servicos']['cards'] as $i => $card) : ?>
                        <div class="border rounded p-3 mb-2">
                            <p class="fw-bold mb-2">Serviço <?= $i + 1 ?></p>
                            <div class="mb-2">
                                <label class="form-label">Título</label>
                                <input type="text" name="servicos_cards[<?= $i ?>][titulo]" class="form-control" value="<?= htmlspecialchars($card['titulo']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Texto</label>
                                <textarea name="servicos_cards[<?= $i ?>][texto]" class="form-control" rows="2" required><?= htmlspecialchars($card['texto']) ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- VANTAGENS -->
                <div class="card p-3 mb-4">
                    <h5 class="fw-bold" style="color:#0096a6;">Secção Vantagens</h5>
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="vantagens_titulo" class="form-control" value="<?= htmlspecialchars($conteudo['vantagens']['titulo']) ?>" required>
                    </div>

                    <?php foreach ($conteudo['vantagens']['cards'] as $i => $card) : ?>
                        <div class="border rounded p-3 mb-2">
                            <p class="fw-bold mb-2">Vantagem <?= $i + 1 ?></p>
                            <div class="mb-2">
                                <label class="form-label">Título</label>
                                <input type="text" name="vantagens_cards[<?= $i ?>][titulo]" class="form-control" value="<?= htmlspecialchars($card['titulo']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Texto</label>
                                <textarea name="vantagens_cards[<?= $i ?>][texto]" class="form-control" rows="2" required><?= htmlspecialchars($card['texto']) ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- FUNCIONALIDADES -->
                <div class="card p-3 mb-4">
                    <h5 class="fw-bold" style="color:#0096a6;">Secção Funcionalidades</h5>
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="funcionalidades_titulo" class="form-control" value="<?= htmlspecialchars($conteudo['funcionalidades']['titulo']) ?>" required>
                    </div>

                    <?php foreach ($conteudo['funcionalidades']['artigos'] as $i => $artigo) : ?>
                        <div class="border rounded p-3 mb-2">
                            <p class="fw-bold mb-2">Funcionalidade <?= $i + 1 ?></p>
                            <div class="mb-2">
                                <label class="form-label">Título</label>
                                <input type="text" name="funcionalidades_artigos[<?= $i ?>][titulo]" class="form-control" value="<?= htmlspecialchars($artigo['titulo']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Texto</label>
                                <textarea name="funcionalidades_artigos[<?= $i ?>][texto]" class="form-control" rows="2" required><?= htmlspecialchars($artigo['texto']) ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- CONTACTO -->
                <div class="card p-3 mb-4">
                    <h5 class="fw-bold" style="color:#0096a6;">Secção Contacto</h5>
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="contacto_titulo" class="form-control" value="<?= htmlspecialchars($conteudo['contacto']['titulo']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Texto Introdutório</label>
                        <textarea name="contacto_texto" class="form-control" rows="2" required><?= htmlspecialchars($conteudo['contacto']['texto']) ?></textarea>
                    </div>
                </div>

                <!-- RODAPÉ -->
                <div class="card p-3 mb-4">
                    <h5 class="fw-bold" style="color:#0096a6;">Rodapé / Contactos</h5>
                    <div class="mb-3">
                        <label class="form-label">Localização</label>
                        <textarea name="rodape_localizacao" class="form-control" rows="2" required><?= htmlspecialchars($conteudo['rodape']['localizacao']) ?></textarea>
                        <small class="text-muted">Pode usar &lt;br&gt; para mudar de linha.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Horário (Semana)</label>
                        <input type="text" name="rodape_horario_semana" class="form-control" value="<?= htmlspecialchars($conteudo['rodape']['horario_semana']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Horário (Sábado)</label>
                        <input type="text" name="rodape_horario_sabado" class="form-control" value="<?= htmlspecialchars($conteudo['rodape']['horario_sabado']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Horário (Domingo/Feriados)</label>
                        <input type="text" name="rodape_horario_domingo" class="form-control" value="<?= htmlspecialchars($conteudo['rodape']['horario_domingo']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="rodape_email" class="form-control" value="<?= htmlspecialchars($conteudo['rodape']['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="rodape_telefone" class="form-control" value="<?= htmlspecialchars($conteudo['rodape']['telefone']) ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Alterações
                </button>

            </form>

        </main>

    </div>
</div>

<?php include 'includes/footer.php'; ?>