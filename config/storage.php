<?php
// Garante fuso horário de Brasília
date_default_timezone_set('America/Sao_Paulo');

define('DATA_FILE', __DIR__ . '/../data/banco_logistico.json');

/**
 * Carrega toda a base de dados JSON ou inicializa a estrutura padrão
 */
function carregarDados(): array {
    if (!file_exists(dirname(DATA_FILE))) {
        mkdir(dirname(DATA_FILE), 0777, true);
    }
    if (!file_exists(DATA_FILE)) {
        $estruturaInicial = [
            'produtos' => [],
            'enderecos' => [],
            'transportadores' => [],
            'fretes' => []
        ];
        file_put_contents(DATA_FILE, json_encode($estruturaInicial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $estruturaInicial;
    }
    $conteudo = json_decode(file_get_contents(DATA_FILE), true);
    return is_array($conteudo) ? $conteudo : [
        'produtos' => [], 'enderecos' => [], 'transportadores' => [], 'fretes' => []
    ];
}

/**
 * Salva os dados atualizados no arquivo JSON
 */
function salvarDados(array $dados): void {
    file_put_contents(DATA_FILE, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Gera próximo ID numérico sequencial para cada tabela/entidade
 */
function gerarProximoId(array $lista): int {
    if (empty($lista)) {
        return 1;
    }
    $ids = array_column($lista, 'id');
    return max($ids) + 1;
}

/**
 * Busca um registro pelo ID dentro de uma coleção
 */
function buscarPorId(array $lista, int $id): ?array {
    foreach ($lista as $item) {
        if ((int)$item['id'] === $id) {
            return $item;
        }
    }
    return null;
}

/**
 * Funções de transformação e formatação de dados em PHP
 */
function formatarMoeda(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatarPeso(float $peso): string {
    return number_format($peso, 2, ',', '.') . ' kg';
}

function formatarDataBr(string $dataIso): string {
    $dt = DateTime::createFromFormat('Y-m-d', $dataIso);
    return $dt ? $dt->format('d/m/Y') : $dataIso;
}