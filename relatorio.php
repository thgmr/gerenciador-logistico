
<?php
require_once 'config/storage.php';
$dados = carregarDados();

$dataFiltro = $_GET['data'] ?? date('Y-m-d');
$fretesDoDia = array_values(array_filter($dados['fretes'], fn($f) => $f['data_frete'] === $dataFiltro));

if (isset($_GET['baixar']) && $_GET['baixar'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_fretes_' . $dataFiltro . '.csv"');

    $saida = fopen('php://output', 'w');
    fprintf($saida, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($saida, ['ID Frete', 'Data', 'Transportador', 'Placa', 'Origem', 'Destino', 'Produtos (1:N)', 'Peso Total (kg)', 'Valor Total (R$)', 'Status'], ';');

    foreach ($fretesDoDia as $f) {
        $t = buscarPorId($dados['transportadores'], (int)$f['transportador_id']);
        $o = buscarPorId($dados['enderecos'], (int)$f['origem_id']);
        $d = buscarPorId($dados['enderecos'], (int)$f['destino_id']);

        $listaProdutos = implode(' | ', array_map(fn($i) => $i['quantidade'] . 'x ' . $i['nome'], $f['itens']));

        fputcsv($saida, [
            $f['id'],
            formatarDataBr($f['data_frete']),
            $t['nome'] ?? 'N/A',
            $t['placa'] ?? 'N/A',
            $o ? $o['apelido'] . ' (' . $o['cidade'] . '/' . $o['uf'] . ')' : 'N/A',
            $d ? $d['apelido'] . ' (' . $d['cidade'] . '/' . $d['uf'] . ')' : 'N/A',
            $listaProdutos,
            number_format((float)$f['peso_total'], 2, ',', '.'),
            number_format((float)$f['valor_total'], 2, ',', '.'),
            $f['status']
        ], ';');
    }
    fclose($saida);
    exit;
}

require_once 'includes/header.php';
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-calendar-check me-2 text-primary"></i>Relatório Diário de Fretes
    </div>
    <div class="card-body">
        <form method="GET" action="relatorio.php" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Selecione a Data do Relatório</label>
                <input type="date" name="data" class="form-control" value="<?= htmlspecialchars($dataFiltro) ?>">
            </div>
            <div class="col-md-8 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Filtrar Dia
                </button>
                <a href="relatorio.php?data=<?= urlencode($dataFiltro) ?>&baixar=csv" class="btn btn-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Baixar Relatório (.CSV)
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">
        Fretes do dia <?= formatarDataBr($dataFiltro) ?> (<?= count($fretesDoDia) ?> registro(s))
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Transportador</th>
                    <th>Origem → Destino</th>
                    <th>Itens da Carga</th>
                    <th>Peso Total</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($fretesDoDia)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhum frete registrado nesta data.</td></tr>
                <?php else: ?>
                    <?php foreach ($fretesDoDia as $f):
                        $t = buscarPorId($dados['transportadores'], (int)$f['transportador_id']);
                        $o = buscarPorId($dados['enderecos'], (int)$f['origem_id']);
                        $d = buscarPorId($dados['enderecos'], (int)$f['destino_id']);
                    ?>
                        <tr>
                            <td>#<?= $f['id'] ?></td>
                            <td><?= $t['nome'] ?? 'N/A' ?></td>
                            <td><?= $o['cidade'] ?? '' ?>/<?= $o['uf'] ?? '' ?> → <?= $d['cidade'] ?? '' ?>/<?= $d['uf'] ?? '' ?></td>
                            <td>
                                <?php foreach ($f['itens'] as $item): ?>
                                    <div><?= $item['quantidade'] ?>x <?= $item['nome'] ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td><?= formatarPeso((float)$f['peso_total']) ?></td>
                            <td><?= formatarMoeda((float)$f['valor_total']) ?></td>
                            <td><span class="badge bg-primary"><?= $f['status'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>