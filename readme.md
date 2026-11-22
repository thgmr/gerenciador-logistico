# XPRESS TRANSPORTES - Gerenciador Logístico Web

Projeto prático desenvolvido para a disciplina **Fundamentos de Programação para Internet** (UNIPAR EAD).

## Funcionalidades
- **Cadastro e Edição de Produtos:** controle de SKU, peso e valor unitário.
- **Cadastro e Edição de Endereços:** gestão de pontos de origem e destino.
- **Cadastro e Edição de Transportadores:** dados de motoristas, veículos e placas.
- **Geração de Fretes (Relação 1:N):** vincula 1 transportador a múltiplos produtos adicionados dinamicamente com JavaScript e cálculo automático de peso/valor.
- **Relatório Diário:** consulta filtrada por data com exportação/download em formato `.CSV`.

## Tecnologias Utilizadas
- **Backend:** PHP 8+ (persistência em JSON e transformação/formatação de dados)
- **Frontend:** HTML5, CSS3, Bootstrap 5 e JavaScript (ES6)
- **Ambiente Local:** XAMPP (Apache)
- **Versionamento:** Git e GitHub

## Como Executar
1. Copie a pasta `gerenciador-logistico` para `C:\xampp\htdocs\`.
2. Inicie o serviço **Apache** no painel de controle do XAMPP.
3. Acesse `http://localhost/gerenciador-logistico/` no navegador.