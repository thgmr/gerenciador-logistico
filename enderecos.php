<?php
require_once 'config/storage.php';
$dados = carregarDados();
$mensagem = '';

if (isset($_GET['excluir'])) {
    $idExcluir = (int)$_GET['excluir'];
    $dados['enderecos'] = array_values(array_filter($dados['enderecos'], fn($e) => (int)$e['id'] !== $idExcluir));
    salvarDados($dados);
    $mensagem = 'Endereço excluído com sucesso!';
}

$enderecoEdicao = null;
if (isset($_GET['editar'])) {
    $enderecoEdicao = buscarPorId($dados['enderecos'], (int)$_GET['editar']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $apelido = htmlspecialchars(trim($_POST['apelido'] ?? ''));
    $cep = htmlspecialchars(trim($_POST['cep'] ?? ''));
    $logradouro = htmlspecialchars(trim($_POST['logradouro'] ?? ''));
    $numero = htmlspecialchars(trim($_POST['numero'] ?? ''));
    $cidade = htmlspecialchars(trim($_POST['cidade'] ?? ''));
    $uf = strtoupper(htmlspecialchars(trim($_POST['uf'] ?? '')));

    if ($apelido && $logradouro && $cidade && $uf) {
        if ($id > 0) {
            foreach ($dados['enderecos'] as &$e) {
                if ((int)$e['id'] === $id) {
                    $e = compact('id', 'apelido', 'cep', 'logradouro', 'numero', 'cidade', 'uf');
                    break;
                }
            }
            unset($e);
            $mensagem = 'Endereço atualizado com sucesso!';
        } else {
            $dados['enderecos'][] = [
                'id' => gerarProximoId($dados['enderecos']),
                'apelido' => $apelido,
                'cep' => $cep,
                'logradouro' => $logradouro,
                'numero' => $numero,
                'cidade' => $cidade,
                'uf' => $uf
            ];
            $mensagem = 'Endereço cadastrado com sucesso!';
        }
        salvarDados($dados);
        $enderecoEdicao = null;
    }
}

require_once 'includes/header.php';
?>

<?php if ($mensagem): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $mensagem ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-geo-alt me-2 text-primary"></i>
        <?= $enderecoEdicao ? 'Editar Endereço #' . $enderecoEdicao['id'] : 'Cadastrar Endereço (Origem / Destino)' ?>
    </div>
    <div class="card-body">
        <form method="POST" action="enderecos.php" class="row g-3">
            <input type="hidden" name="id" value="<?= $enderecoEdicao['id'] ?? 0 ?>">

            <div class="col-md-4">
                <label class="form-label">Identificação / Unidade *</label>
                <input type="text" name="apelido" class="form-control" required
                       value="<?= $enderecoEdicao['apelido'] ?? '' ?>" placeholder="Ex: Centro Distribuição Maringá">
            </div>
            <div class="col-md-2">
                <label class="form-label">CEP</label>
                <input type="text" name="cep" class="form-control"
                       value="<?= $enderecoEdicao['cep'] ?? '' ?>" placeholder="87000-000">
            </div>
            <div class="col-md-4">
                <label class="form-label">Logradouro *</label>
                <input type="text" name="logradouro" class="form-control" required
                       value="<?= $enderecoEdicao['logradouro'] ?? '' ?>" placeholder="Av. Brasil">
            </div>
            <div class="col-md-2">
                <label class="form-label">Número</label>
                <input type="text" name="numero" class="form-control"
                       value="<?= $enderecoEdicao['numero'] ?? '' ?>" placeholder="1200">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cidade *</label>
                <input type="text" name="cidade" class="form-control" required
                       value="<?= $enderecoEdicao['cidade'] ?? '' ?>" placeholder="Maringá">
            </div>
            <div class="col-md-2">
                <label class="form-label">UF *</label>
                <input type="text" name="uf" maxlength="2" class="form-control" required
                       value="<?= $enderecoEdicao['uf'] ?? '' ?>" placeholder="PR">
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <?php if ($enderecoEdicao): ?>
                    <a href="enderecos.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= $enderecoEdicao ? 'Atualizar Endereço' : 'Salvar Endereço' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">Endereços Cadastrados (<?= count($dados['enderecos']) ?>)</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Unidade / Apelido</th>
                    <th>Endereço Completo</th>
                    <th>Cidade / UF</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados['enderecos'])): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhum endereço cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($dados['enderecos'] as $e): ?>
                        <tr>
                            <td>#<?= $e['id'] ?></td>
                            <td class="fw-semibold"><?= $e['apelido'] ?></td>
                            <td><?= $e['logradouro'] ?>, <?= $e['numero'] ?> (CEP: <?= $e['cep'] ?>)</td>
                            <td><?= $e['cidade'] ?> - <?= $e['uf'] ?></td>
                            <td class="text-end">
                                <a href="enderecos.php?editar=<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="enderecos.php?excluir=<?= $e['id'] ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Excluir este endereço?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>