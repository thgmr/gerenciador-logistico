<?php
require_once 'config/storage.php';
$dados = carregarDados();
$mensagem = '';

if (isset($_GET['excluir'])) {
    $idExcluir = (int)$_GET['excluir'];
    $dados['transportadores'] = array_values(array_filter($dados['transportadores'], fn($t) => (int)$t['id'] !== $idExcluir));
    salvarDados($dados);
    $mensagem = 'Transportador excluído com sucesso!';
}

$transpEdicao = null;
if (isset($_GET['editar'])) {
    $transpEdicao = buscarPorId($dados['transportadores'], (int)$_GET['editar']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nome = htmlspecialchars(trim($_POST['nome'] ?? ''));
    $documento = htmlspecialchars(trim($_POST['documento'] ?? ''));
    $placa = strtoupper(htmlspecialchars(trim($_POST['placa'] ?? '')));
    $veiculo = htmlspecialchars(trim($_POST['veiculo'] ?? ''));
    $telefone = htmlspecialchars(trim($_POST['telefone'] ?? ''));

    if ($nome && $placa && $veiculo) {
        if ($id > 0) {
            foreach ($dados['transportadores'] as &$t) {
                if ((int)$t['id'] === $id) {
                    $t = compact('id', 'nome', 'documento', 'placa', 'veiculo', 'telefone');
                    break;
                }
            }
            unset($t);
            $mensagem = 'Transportador atualizado com sucesso!';
        } else {
            $dados['transportadores'][] = [
                'id' => gerarProximoId($dados['transportadores']),
                'nome' => $nome,
                'documento' => $documento,
                'placa' => $placa,
                'veiculo' => $veiculo,
                'telefone' => $telefone
            ];
            $mensagem = 'Transportador cadastrado com sucesso!';
        }
        salvarDados($dados);
        $transpEdicao = null;
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
        <i class="bi bi-person-badge me-2 text-primary"></i>
        <?= $transpEdicao ? 'Editar Transportador #' . $transpEdicao['id'] : 'Cadastrar Transportador / Motorista' ?>
    </div>
    <div class="card-body">
        <form method="POST" action="transportadores.php" class="row g-3">
            <input type="hidden" name="id" value="<?= $transpEdicao['id'] ?? 0 ?>">

            <div class="col-md-4">
                <label class="form-label">Nome / Transportadora *</label>
                <input type="text" name="nome" class="form-control" required
                       value="<?= $transpEdicao['nome'] ?? '' ?>" placeholder="Ex: Expresso Paraná Ltda">
            </div>
            <div class="col-md-3">
                <label class="form-label">CPF ou CNPJ</label>
                <input type="text" name="documento" class="form-control"
                       value="<?= $transpEdicao['documento'] ?? '' ?>" placeholder="00.000.000/0001-00">
            </div>
            <div class="col-md-2">
                <label class="form-label">Placa Veículo *</label>
                <input type="text" name="placa" class="form-control" required
                       value="<?= $transpEdicao['placa'] ?? '' ?>" placeholder="ABC-1D23">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de Veículo *</label>
                <input type="text" name="veiculo" class="form-control" required
                       value="<?= $transpEdicao['veiculo'] ?? '' ?>" placeholder="Ex: Caminhão Baú 3/4">
            </div>
            <div class="col-md-3">
                <label class="form-label">Telefone / WhatsApp</label>
                <input type="text" name="telefone" class="form-control"
                       value="<?= $transpEdicao['telefone'] ?? '' ?>" placeholder="(44) 99999-0000">
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <?php if ($transpEdicao): ?>
                    <a href="transportadores.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= $transpEdicao ? 'Atualizar Transportador' : 'Salvar Transportador' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">Transportadores Cadastrados (<?= count($dados['transportadores']) ?>)</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nome / Razão Social</th>
                    <th>CPF/CNPJ</th>
                    <th>Veículo / Placa</th>
                    <th>Telefone</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados['transportadores'])): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum transportador cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($dados['transportadores'] as $t): ?>
                        <tr>
                            <td>#<?= $t['id'] ?></td>
                            <td class="fw-semibold"><?= $t['nome'] ?></td>
                            <td><?= $t['documento'] ?></td>
                            <td><?= $t['veiculo'] ?> <span class="badge bg-dark"><?= $t['placa'] ?></span></td>
                            <td><?= $t['telefone'] ?></td>
                            <td class="text-end">
                                <a href="transportadores.php?editar=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="transportadores.php?excluir=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Excluir este transportador?');">
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