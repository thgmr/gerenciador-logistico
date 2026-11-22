<?php
require_once 'config/storage.php';
$dados = carregarDados();
$mensagem = '';
$tipoAlerta = 'success';

if (isset($_GET['excluir'])) {
    $idExcluir = (int)$_GET['excluir'];
    $dados['fretes'] = array_values(array_filter($dados['fretes'], fn($f) => (int)$f['id'] !== $idExcluir));
    salvarDados($dados);
    $mensagem = 'Frete removido com sucesso!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'status') {
    $idFrete = (int)$_POST['frete_id'];
    $novoStatus = htmlspecialchars($_POST['novo_status']);
    foreach ($dados['fretes'] as &$f) {
        if ((int)$f['id'] === $idFrete) {
            $f['status'] = $novoStatus;
            break;
        }
    }
    unset($f);
    salvarDados($dados);
    $mensagem = "Status do frete #{$idFrete} atualizado para {$novoStatus}!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'criar_frete') {
    $transportadorId = (int)($_POST['transportador_id'] ?? 0);
    $origemId = (int)($_POST['origem_id'] ?? 0);
    $destinoId = (int)($_POST['destino_id'] ?? 0);
    $dataFrete = htmlspecialchars($_POST['data_frete'] ?? date('Y-m-d'));
    $status = htmlspecialchars($_POST['status'] ?? 'Pendente');

    $produtosIds = $_POST['produto_id'] ?? [];
    $quantidades = $_POST['quantidade'] ?? [];

    $itensFrete = [];
    $pesoTotal = 0.0;
    $valorTotal = 0.0;

    for ($i = 0; $i < count($produtosIds); $i++) {
        $prodId = (int)$produtosIds[$i];
        $qtd = (int)($quantidades[$i] ?? 0);
        $produtoObj = buscarPorId($dados['produtos'], $prodId);

        if ($produtoObj && $qtd > 0) {
            $subPeso = (float)$produtoObj['peso'] * $qtd;
            $subValor = (float)$produtoObj['valor'] * $qtd;
            $pesoTotal += $subPeso;
            $valorTotal += $subValor;

            $itensFrete[] = [
                'produto_id' => $prodId,
                'nome' => $produtoObj['nome'],
                'sku' => $produtoObj['sku'],
                'quantidade' => $qtd,
                'peso_unit' => (float)$produtoObj['peso'],
                'valor_unit' => (float)$produtoObj['valor'],
                'subtotal_peso' => $subPeso,
                'subtotal_valor' => $subValor
            ];
        }
    }

    if ($transportadorId > 0 && $origemId > 0 && $destinoId > 0 && $origemId !== $destinoId && count($itensFrete) > 0) {
        $dados['fretes'][] = [
            'id' => gerarProximoId($dados['fretes']),
            'transportador_id' => $transportadorId,
            'origem_id' => $origemId,
            'destino_id' => $destinoId,
            'data_frete' => $dataFrete,
            'status' => $status,
            'itens' => $itensFrete,
            'peso_total' => $pesoTotal,
            'valor_total' => $valorTotal
        ];
        salvarDados($dados);
        $mensagem = 'Frete gerado com sucesso!';
    } else {
        $mensagem = 'Verifique os dados: selecione transportador, origem e destino diferentes e ao menos 1 produto.';
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

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-primary">
            <div class="card-body">
                <div class="text-muted small">Fretes Gerados</div>
                <div class="fs-3 fw-bold"><?= count($dados['fretes']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-success">
            <div class="card-body">
                <div class="text-muted small">Produtos Ativos</div>
                <div class="fs-3 fw-bold"><?= count($dados['produtos']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-info">
            <div class="card-body">
                <div class="text-muted small">Endereços Cadastrados</div>
                <div class="fs-3 fw-bold"><?= count($dados['enderecos']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-warning">
            <div class="card-body">
                <div class="text-muted small">Transportadores</div>
                <div class="fs-3 fw-bold"><?= count($dados['transportadores']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-plus-circle me-2 text-primary"></i>Gerar Novo Frete (1 Transportador → Múltiplos Produtos)
    </div>
    <div class="card-body">
        <?php if (empty($dados['produtos']) || count($dados['enderecos']) < 2 || empty($dados['transportadores'])): ?>
            <div class="alert alert-warning mb-0">
                Para gerar um frete, cadastre primeiro: <strong>1 Produto</strong>, <strong>2 Endereços (Origem e Destino)</strong> e <strong>1 Transportador</strong> nos menus acima.
            </div>
        <?php else: ?>
            <form method="POST" action="index.php">
                <input type="hidden" name="acao" value="criar_frete">

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Transportador *</label>
                        <select name="transportador_id" class="form-select" required>
                            <option value="">Selecione o transportador...</option>
                            <?php foreach ($dados['transportadores'] as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= $t['nome'] ?> (<?= $t['placa'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Endereço de Origem *</label>
                        <select name="origem_id" class="form-select" required>
                            <option value="">Selecione a origem...</option>
                            <?php foreach ($dados['enderecos'] as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= $e['apelido'] ?> (<?= $e['cidade'] ?>/<?= $e['uf'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Endereço de Destino *</label>
                        <select name="destino_id" class="form-select" required>
                            <option value="">Selecione o destino...</option>
                            <?php foreach ($dados['enderecos'] as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= $e['apelido'] ?> (<?= $e['cidade'] ?>/<?= $e['uf'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Data do Frete *</label>
                        <input type="date" name="data_frete" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-boxes me-1"></i>Produtos Transportados neste Frete</h6>
                    <button type="button" id="btn-add-produto" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i>Adicionar Produto
                    </button>
                </div>

                <div id="container-produtos-frete">
                    <div class="linha-produto-frete row g-2 align-items-center">
                        <div class="col-md-6">
                            <select name="produto_id[]" class="form-select select-produto" required>
                                <?php foreach ($dados['produtos'] as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-peso="<?= $p['peso'] ?>" data-valor="<?= $p['valor'] ?>">
                                        <?= $p['nome'] ?> (<?= formatarPeso((float)$p['peso']) ?> | <?= formatarMoeda((float)$p['valor']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="quantidade[]" class="form-control input-qtd" min="1" value="1" required placeholder="Qtd">
                        </div>
                        <div class="col-md-3 small text-muted subtotal-linha">
                            Subtotal: 0,00 kg | R$ 0,00
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-produto" disabled>
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <template id="template-linha-produto">
                    <div class="linha-produto-frete row g-2 align-items-center">
                        <div class="col-md-6">
                            <select name="produto_id[]" class="form-select select-produto" required>
                                <?php foreach ($dados['produtos'] as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-peso="<?= $p['peso'] ?>" data-valor="<?= $p['valor'] ?>">
                                        <?= $p['nome'] ?> (<?= formatarPeso((float)$p['peso']) ?> | <?= formatarMoeda((float)$p['valor']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="quantidade[]" class="form-control input-qtd" min="1" value="1" required placeholder="Qtd">
                        </div>
                        <div class="col-md-3 small text-muted subtotal-linha">
                            Subtotal: 0,00 kg | R$ 0,00
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-produto">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </template>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        <span class="me-3">Peso Total: <strong id="total-peso-frete">0,00 kg</strong></span>
                        <span>Valor Carga: <strong id="total-valor-frete">R$ 0,00</strong></span>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-truck me-1"></i>Gerar Frete
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">Fretes Registrados</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Frete</th>
                    <th>Transportador</th>
                    <th>Rota (Origem → Destino)</th>
                    <th>Produtos (1:N)</th>
                    <th>Totais</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados['fretes'])): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhum frete gerado ainda.</td></tr>
                <?php else: ?>
                    <?php foreach (array_reverse($dados['fretes']) as $f):
                        $transp = buscarPorId($dados['transportadores'], (int)$f['transportador_id']);
                        $orig = buscarPorId($dados['enderecos'], (int)$f['origem_id']);
                        $dest = buscarPorId($dados['enderecos'], (int)$f['destino_id']);
                    ?>
                        <tr>
                            <td>
                                <strong>#<?= $f['id'] ?></strong><br>
                                <small class="text-muted"><?= formatarDataBr($f['data_frete']) ?></small>
                            </td>
                            <td><?= $transp ? $transp['nome'] . ' (' . $transp['placa'] . ')' : 'N/A' ?></td>
                            <td>
                                <span class="text-primary fw-semibold"><?= $orig ? $orig['cidade'] . '/' . $orig['uf'] : 'N/A' ?></span>
                                →
                                <span class="text-success fw-semibold"><?= $dest ? $dest['cidade'] . '/' . $dest['uf'] : 'N/A' ?></span>
                            </td>
                            <td>
                                <ul class="mb-0 ps-3 small">
                                    <?php foreach ($f['itens'] as $item): ?>
                                        <li><?= $item['quantidade'] ?>x <?= $item['nome'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>
                                <strong><?= formatarPeso((float)$f['peso_total']) ?></strong><br>
                                <small class="text-muted"><?= formatarMoeda((float)$f['valor_total']) ?></small>
                            </td>
                            <td>
                                <form method="POST" action="index.php">
                                    <input type="hidden" name="acao" value="status">
                                    <input type="hidden" name="frete_id" value="<?= $f['id'] ?>">
                                    <select name="novo_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="Pendente" <?= $f['status'] === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="Em Trânsito" <?= $f['status'] === 'Em Trânsito' ? 'selected' : '' ?>>Em Trânsito</option>
                                        <option value="Entregue" <?= $f['status'] === 'Entregue' ? 'selected' : '' ?>>Entregue</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="index.php?excluir=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Remover este frete?');">
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