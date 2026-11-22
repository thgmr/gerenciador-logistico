<?php
require_once 'config/storage.php';
$dados = carregarDados();
$mensagem = '';
$tipoAlerta = 'success';

if (isset($_GET['excluir'])) {
    $idExcluir = (int)$_GET['excluir'];
    $dados['produtos'] = array_values(array_filter($dados['produtos'], fn($p) => (int)$p['id'] !== $idExcluir));
    salvarDados($dados);
    $mensagem = 'Produto excluído com sucesso!';
}

$produtoEdicao = null;
if (isset($_GET['editar'])) {
    $produtoEdicao = buscarPorId($dados['produtos'], (int)$_GET['editar']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nome = htmlspecialchars(trim($_POST['nome'] ?? ''));
    $sku = htmlspecialchars(trim($_POST['sku'] ?? ''));
    $peso = (float)str_replace(',', '.', $_POST['peso'] ?? 0);
    $valor = (float)str_replace(',', '.', $_POST['valor'] ?? 0);

    if ($nome && $sku && $peso > 0) {
        if ($id > 0) {
            foreach ($dados['produtos'] as &$p) {
                if ((int)$p['id'] === $id) {
                    $p['nome'] = $nome;
                    $p['sku'] = $sku;
                    $p['peso'] = $peso;
                    $p['valor'] = $valor;
                    break;
                }
            }
            unset($p);
            $mensagem = 'Produto atualizado com sucesso!';
        } else {
            $dados['produtos'][] = [
                'id' => gerarProximoId($dados['produtos']),
                'nome' => $nome,
                'sku' => $sku,
                'peso' => $peso,
                'valor' => $valor
            ];
            $mensagem = 'Produto cadastrado com sucesso!';
        }
        salvarDados($dados);
        $produtoEdicao = null;
    } else {
        $mensagem = 'Preencha todos os campos obrigatórios corretamente.';
        $tipoAlerta = 'danger';
    }
}

require_once 'includes/header.php';
?>

<?php if ($mensagem): ?>
    <div class="alert alert-<?= $tipoAlerta ?> alert-dismissible fade show">
        <?= $mensagem ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-tags me-2 text-primary"></i>
        <?= $produtoEdicao ? 'Editar Produto #' . $produtoEdicao['id'] : 'Cadastrar Novo Produto' ?>
    </div>
    <div class="card-body">
        <form method="POST" action="produtos.php" class="row g-3">
            <input type="hidden" name="id" value="<?= $produtoEdicao['id'] ?? 0 ?>">

            <div class="col-md-5">
                <label class="form-label">Nome / Descrição do Produto *</label>
                <input type="text" name="nome" class="form-control" required
                       value="<?= $produtoEdicao['nome'] ?? '' ?>" placeholder="Ex: Caixa de Peças Automotivas">
            </div>
            <div class="col-md-3">
                <label class="form-label">Código SKU *</label>
                <input type="text" name="sku" class="form-control" required
                       value="<?= $produtoEdicao['sku'] ?? '' ?>" placeholder="Ex: SKU-001">
            </div>
            <div class="col-md-2">
                <label class="form-label">Peso Unitário (kg) *</label>
                <input type="number" step="0.01" min="0.01" name="peso" class="form-control" required
                       value="<?= $produtoEdicao['peso'] ?? '' ?>" placeholder="0.00">
            </div>
            <div class="col-md-2">
                <label class="form-label">Valor Unitário (R$) *</label>
                <input type="number" step="0.01" min="0" name="valor" class="form-control" required
                       value="<?= $produtoEdicao['valor'] ?? '' ?>" placeholder="0.00">
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <?php if ($produtoEdicao): ?>
                    <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= $produtoEdicao ? 'Atualizar Produto' : 'Salvar Produto' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">Produtos Cadastrados (<?= count($dados['produtos']) ?>)</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>SKU</th>
                    <th>Produto</th>
                    <th>Peso Unit.</th>
                    <th>Valor Unit.</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados['produtos'])): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum produto cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($dados['produtos'] as $p): ?>
                        <tr>
                            <td>#<?= $p['id'] ?></td>
                            <td><span class="badge bg-secondary"><?= $p['sku'] ?></span></td>
                            <td class="fw-semibold"><?= $p['nome'] ?></td>
                            <td><?= formatarPeso((float)$p['peso']) ?></td>
                            <td><?= formatarMoeda((float)$p['valor']) ?></td>
                            <td class="text-end">
                                <a href="produtos.php?editar=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="produtos.php?excluir=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Excluir este produto?');">
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