<?php
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XPRESS TRANSPORTES - Gerenciador Logístico</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-truck me-2"></i>XPRESS TRANSPORTES
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtual === 'index.php' ? 'active fw-semibold' : '' ?>" href="index.php">
                        <i class="bi bi-box-seam me-1"></i>Fretes (1:N)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtual === 'produtos.php' ? 'active fw-semibold' : '' ?>" href="produtos.php">
                        <i class="bi bi-tags me-1"></i>Produtos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtual === 'enderecos.php' ? 'active fw-semibold' : '' ?>" href="enderecos.php">
                        <i class="bi bi-geo-alt me-1"></i>Endereços
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtual === 'transportadores.php' ? 'active fw-semibold' : '' ?>" href="transportadores.php">
                        <i class="bi bi-person-badge me-1"></i>Transportadores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtual === 'relatorio.php' ? 'active fw-semibold' : '' ?>" href="relatorio.php">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i>Relatório Diário
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container pb-5">