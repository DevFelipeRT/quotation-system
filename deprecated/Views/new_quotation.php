<h2>Criar Novo Orçamento</h2>

<!-- Formulário do Orçamento -->
<form id="orcamento-form" action="salvar_orcamento.php" method="POST">
    <label for="nome_orcamento">Nome do Orçamento:</label>
    <input type="text" id="nome_orcamento" name="nome_orcamento">

    <label for="descricao_orcamento">Descrição do Orçamento:</label>
    <input type="text" id="descricao_orcamento" name="descricao_orcamento">

    <!-- Seção de Ajustes Gerais -->
    <h3>Ajustes Gerais</h3>
    
    <label for="taxa_geral">Taxa Geral (%):</label>
    <input type="number" id="taxa_geral" step="0.01" value="0">
    <button type="button" id="adicionar_taxa_geral">Adicionar Taxa</button>

    <label for="desconto_geral">Desconto Geral (%):</label>
    <input type="number" id="desconto_geral" step="0.01" value="0">
    <button type="button" id="adicionar_desconto_geral">Adicionar Desconto</button>

    <!-- Seção de Adição de Itens -->
    <h3>Adicionar Itens</h3>
    <label for="buscar_item">Buscar Item:</label>
    <input type="text" id="buscar_item" placeholder="Digite para buscar...">

    <select id="lista_itens">
        <option value="">Selecione um item</option>
        <?php foreach ($itensDisponiveis as $item): ?>
            <option value="<?= $item['id'] ?>" data-nome="<?= $item['nome'] ?>" data-preco="<?= $item['preco'] ?>">
                <?= $item['nome'] ?> - R$ <?= number_format($item['preco'], 2, ',', '.') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="quantidade">Quantidade:</label>
    <input type="number" id="quantidade" value="1" min="1">

    <label for="taxa_item">Taxa (%):</label>
    <input type="number" id="taxa_item" step="0.01" value="0">
    <button type="button" id="adicionar_taxa_item">Adicionar Taxa</button>

    <label for="desconto_item">Desconto (%):</label>
    <input type="number" id="desconto_item" step="0.01" value="0">
    <button type="button" id="adicionar_desconto_item">Adicionar Desconto</button>

    <button type="button" id="adicionar_item">Adicionar Item</button>

    <!-- Tabela de Itens Adicionados -->
    <h3>Itens no Orçamento</h3>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Taxa (%)</th>
                <th>Desconto (%)</th>
                <th>Subtotal</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="lista_orcamento"></tbody>
    </table>

    <!-- Resumo do Orçamento -->
    <h3>Resumo do Orçamento</h3>
    <p>Subtotal: R$ <span id="subtotal_geral">0.00</span></p>
    <p>Total com Ajustes: R$ <span id="total_geral">0.00</span></p>

    <button type="submit">Salvar Orçamento</button>
</form>
