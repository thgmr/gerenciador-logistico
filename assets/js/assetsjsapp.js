<?php
document.addEventListener('DOMContentLoaded', () => {
    const containerItens = document.getElementById('container-produtos-frete');
    const btnAdicionar = document.getElementById('btn-add-produto');
    const templateLinha = document.getElementById('template-linha-produto');

    if (!containerItens || !btnAdicionar || !templateLinha) return;

    function recalcularTotais() {
        let pesoTotal = 0;
        let valorTotal = 0;

        const linhas = containerItens.querySelectorAll('.linha-produto-frete');
        linhas.forEach(linha => {
            const select = linha.querySelector('.select-produto');
            const inputQtd = linha.querySelector('.input-qtd');
            const spanSubtotal = linha.querySelector('.subtotal-linha');

            const optionSelecionada = select.options[select.selectedIndex];
            const pesoUnit = parseFloat(optionSelecionada?.dataset?.peso || 0);
            const valorUnit = parseFloat(optionSelecionada?.dataset?.valor || 0);
            const qtd = parseInt(inputQtd.value || 0, 10);

            const subPeso = pesoUnit * qtd;
            const subValor = valorUnit * qtd;

            pesoTotal += subPeso;
            valorTotal += subValor;

            if (spanSubtotal) {
                spanSubtotal.textContent = `Subtotal: ${subPeso.toFixed(2).replace('.', ',')} kg | R$ ${subValor.toFixed(2).replace('.', ',')}`;
            }
        });

        const displayPeso = document.getElementById('total-peso-frete');
        const displayValor = document.getElementById('total-valor-frete');
        if (displayPeso) displayPeso.textContent = `${pesoTotal.toFixed(2).replace('.', ',')} kg`;
        if (displayValor) displayValor.textContent = `R$ ${valorTotal.toFixed(2).replace('.', ',')}`;

        const botoesRemover = containerItens.querySelectorAll('.btn-remover-produto');
        botoesRemover.forEach(btn => {
            btn.disabled = botoesRemover.length === 1;
        });
    }

    btnAdicionar.addEventListener('click', () => {
        const clone = templateLinha.content.cloneNode(true);
        containerItens.appendChild(clone);
        recalcularTotais();
    });

    containerItens.addEventListener('click', (e) => {
        const btnRemover = e.target.closest('.btn-remover-produto');
        if (btnRemover) {
            const linhas = containerItens.querySelectorAll('.linha-produto-frete');
            if (linhas.length > 1) {
                btnRemover.closest('.linha-produto-frete').remove();
                recalcularTotais();
            }
        }
    });

    containerItens.addEventListener('change', (e) => {
        if (e.target.classList.contains('select-produto')) {
            recalcularTotais();
        }
    });

    containerItens.addEventListener('input', (e) => {
        if (e.target.classList.contains('input-qtd')) {
            recalcularTotais();
        }
    });

    recalcularTotais();
});