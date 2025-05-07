<?php 

$objects = [];
foreach ($quotations as $quotation) {
    $quotationObjects[$quotation->getId()] = [
        'object' => $quotation
    ];
}
$session->setData('quotationObjects', $quotationObjects);

// echo '<pre>';
// var_dump($categories);
// echo '</pre>';


usort($quotations, function($a, $b) {
    return $b->getCreationDate()->getTimestamp() <=> $a->getCreationDate()->getTimestamp();
});


?>

<h1>Meus Orçamentos</h1>

<div class="new-quotation" style="width: 24vw;">
        <button id="new-quotation-btn" class="btn btn-primary">Novo Orçamento</button>
</div>

<div class="quotations-container" style="display: flex; position: relative; flex-wrap: wrap; align-items: flex-start;">
    <!-- Botão para novo orçamento -->
    

    <!-- Container de formulário de novo orçamento, inicialmente oculto -->
    <div class="quotation-card" id="quotation-form-container" style="display: none; width: 24vw; height: 180px;">
        <div class="quotation-header">
            <h5 class="quotation-name">Criar Novo Orçamento</h5>
        </div>
        <div class="quotation-body">
            <!-- Formulário de novo orçamento -->
            <form id="quotation-form" action="<?php echo $actionFileName; ?>" method="POST">
                <input type="hidden" name="action" value="add-quotation">
                <input type="hidden" name="controller" value="quotation">
                <div>
                    <label for="quotation-name" class="form-label">Nome do Orçamento</label>
                    <input type="text" class="form-control" id="quotation-name" name="name" required>
                </div>
                <div>
                    <label for="quotation-description" class="form-label">Descrição</label>
                    <textarea class="form-control" id="quotation-description" name="description"></textarea>
                </div>
                <div class="quotation-actions">
                    <button type="submit" id="save-form-btn">Salvar Orçamento</button>
                </div>
            </form>
            
        </div>
        <button type="button" class="btn btn-danger" id="close-form-btn">Cancelar</button>
    </div>

    <!-- Exibindo orçamentos -->
    <?php foreach ($quotations as $quotation): ?>
        <div class="quotation-card" id="quotation-<?php echo $quotation->getId(); ?>" style="width: 24vw; height: 160px;">
            <div class="quotation-header">
                <h5 class="quotation-name"><?php echo htmlspecialchars($quotation->getName()); ?></h5>
                <p class="quotation-description"><?php echo htmlspecialchars($quotation->getDescription()); ?></p>
            </div>
            <div class="quotation-body">
                <p><strong>Data de Criação:</strong> <?php echo $quotation->getCreationDate()->format('d/m/Y H:i'); ?></p>
                <p><strong>Cliente:</strong> <?php echo $quotation->getClientId() ? 'Cliente ' . $quotation->getClientId() : 'Não especificado'; ?></p>
            </div>
            <div class="quotation-actions">
                <button class="btn btn-secondary edit-btn" data-id="<?php echo $quotation->getId(); ?>">Editar</button>

                <!-- Formulário de exclusão -->
                <form method="POST" action="<?php echo $actionFileName; ?>" onsubmit="return confirm('Tem certeza que deseja excluir este orçamento?');">
                    <input type="hidden" name="action" value="delete-quotation">
                    <input type="hidden" name="controller" value="quotation">
                    <input type="hidden" name="id" value="<?php echo $quotation->getId(); ?>">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>

            <!-- Formulário de Edição Oculto (área expandida) -->
            <div class="edit-form" id="edit-form-<?php echo $quotation->getId(); ?>" style="display: none; background: #f8f9fa; padding: 10px; border: 1px solid #ccc; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 80%; height: 80%;">
                <form method="POST" action="<?php echo $actionFileName; ?>">
                    <input type="hidden" name="action" value="update-quotation">
                    <input type="hidden" name="controller" value="quotation">
                    <input type="hidden" name="id" value="<?php echo $quotation->getId(); ?>">
                    
                    <div class="form-group">
                        <label for="name-<?php echo $quotation->getId(); ?>">Nome:</label>
                        <input type="text" id="name-<?php echo $quotation->getId(); ?>" name="name" value="<?php echo htmlspecialchars($quotation->getName()); ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="description-<?php echo $quotation->getId(); ?>">Descrição:</label>
                        <textarea id="description-<?php echo $quotation->getId(); ?>" name="description" class="form-control"><?php echo htmlspecialchars($quotation->getDescription()); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="client-<?php echo $quotation->getId(); ?>">Cliente:</label>
                        <input type="number" id="client-<?php echo $quotation->getId(); ?>" name="clientId" value="<?php echo $quotation->getClientId(); ?>" class="form-control">
                    </div>

                    <!-- Exibição dos Itens do Orçamento -->
                    <h5>Itens do Orçamento</h5>
                    <div id="quotation-items-<?php echo $quotation->getId(); ?>">
                        <?php foreach ($quotation->getItems() as $item): ?>
                            <div class="item-row" data-id="<?php echo $item->getId(); ?>" style="border: 1px solid #ccc; padding: 5px; margin-bottom: 5px;">
                                <input type="hidden" name="items[<?php echo $item->getId(); ?>][id]" value="<?php echo $item->getId(); ?>">

                                <label>Item:</label>
                                <input type="text" name="items[<?php echo $item->getId(); ?>][name]" value="<?php echo htmlspecialchars($item->getItem()->getName()); ?>" class="form-control" readonly>

                                <label>Quantidade:</label>
                                <input type="number" name="items[<?php echo $item->getId(); ?>][quantity]" value="<?php echo $item->getQuantity(); ?>" class="form-control">

                                <label>Tipo:</label>
                                <select name="items[<?php echo $item->getId(); ?>][typeId]" class="form-control">
                                    <?php foreach ($quotationItemTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>" <?php echo ($item->getTypeId() == $type['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="button" class="btn btn-danger remove-item-btn" data-id="<?php echo $item->getId(); ?>">Remover</button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group">
                        <h5>Adicionar Novo Item</h5>
                        <!-- Botões de categorias -->
                        <div class="btn-group mb-3">
                            <?php foreach ($categories as $category): ?>
                                <button type="button" class="btn btn-secondary category-btn" data-category="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['nome']); ?>">
                                    <?php echo htmlspecialchars($category['nome']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Ações do Formulário" -->
                    <div class="form-group">
                        <!-- Botão de Salvar Alterações -->
                        <button type="submit" class="btn btn-success">Salvar Alterações</button>
                    </div>
                </form>
                
                <!-- Formulário para adicionar um item -->
                <div>
                    
                    <!-- Container dos campos (inicialmente oculto) -->
                    <div id="item-container" style="display: none;">
                        <form action="<?php echo $actionFileName; ?>" method="post" id="add-item-form">
                            <input type="hidden" name="action" value="add-quotation-item">
                            <input type="hidden" name="controller" value="quotation-item">
                            <div class="form-group">
                                <label id="item-label">Selecionar Item:</label>
                                <select name="item_id" class="form-control" id="item-select" required>
                                    <option value="">Escolha um Item</option>
                                    <?php foreach ($items as $item): ?>
                                        <option value="<?php echo $item->getId(); ?>" data-category="<?php echo $item->getCategoryId(); ?>">
                                            <?php echo htmlspecialchars($item->getName()); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Quantidade:</label>
                                <input type="number" name="quantity" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Tipo:</label>
                                <select name="type_id" class="form-control">
                                    <option value="">Escolha um tipo</option>
                                    <?php foreach ($quotationItemTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Adicionar Item</button>
                        </form>
                    </div>
                </div>
                
                <!-- Botão de Cancelar -->
                <button type="button" class="btn btn-secondary cancel-btn" data-id="<?php echo $quotation->getId(); ?>">Cancelar</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>


<!-- Scripts JavaScript para manipulação dos botões -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        console.log("DOM completamente carregado e analisado.");

        // Botão de novo orçamento
        const newQuotationBtn = document.getElementById('new-quotation-btn');
        const quotationFormContainer = document.getElementById('quotation-form-container');
        if (newQuotationBtn && quotationFormContainer) {
            newQuotationBtn.addEventListener('click', function () {
                quotationFormContainer.style.display = 
                    quotationFormContainer.style.display === "none" || quotationFormContainer.style.display === "" 
                    ? "block" 
                    : "none";
            });
        }

        // Botão de cancelar para fechar o formulário
        const closeFormBtn = document.getElementById('close-form-btn');
        if (closeFormBtn) {
            closeFormBtn.addEventListener('click', function () {
                quotationFormContainer.style.display = "none";
            });
        }

        // Botões de editar orçamento
        document.querySelectorAll('.edit-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const quotationId = this.getAttribute('data-id');
                const editForm = document.getElementById('edit-form-' + quotationId);
                if (editForm) {
                    // Oculta o container de itens ao abrir o formulário de edição
                    const itemContainer = editForm.querySelector("#item-container");
                    if (itemContainer) {
                        itemContainer.style.display = "none";
                    }
                    editForm.style.display = 'block';
                }
            });
        });

        // Botões de cancelar edição
        document.querySelectorAll('.cancel-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const quotationId = this.getAttribute('data-id');
                const editForm = document.getElementById('edit-form-' + quotationId);
                if (editForm) {
                    editForm.style.display = 'none';
                }
            });
        });

        // Botões de categorias
        document.querySelectorAll(".edit-form").forEach(function (editForm) {
            const categoryButtons = editForm.querySelectorAll(".category-btn");
            const itemContainer = editForm.querySelector("#item-container");
            const itemLabel = editForm.querySelector("#item-label");
            const itemSelect = editForm.querySelector("#item-select");

            if (categoryButtons.length > 0 && itemContainer && itemLabel && itemSelect) {
                const allOptions = Array.from(itemSelect.options).slice(1); // Remove a opção inicial "Escolha um Item"

                categoryButtons.forEach(button => {
                    button.addEventListener("click", function () {
                        const categoryId = this.getAttribute("data-category");
                        const categoryName = this.getAttribute("data-name");

                        if (categoryId && categoryName) {
                            console.log(`Botão de categoria clicado: ${categoryName} (ID: ${categoryId})`);

                            // Define o título dinamicamente
                            itemLabel.textContent = `Selecionar Item (${categoryName}):`;

                            // Limpa o select e adiciona apenas os itens da categoria selecionada
                            itemSelect.innerHTML = '<option value="">Escolha um Item</option>';
                            let hasItems = false;

                            allOptions.forEach(option => {
                                if (option.getAttribute("data-category") === categoryId) {
                                    itemSelect.appendChild(option.cloneNode(true));
                                    hasItems = true;
                                }
                            });

                            // Exibe o container somente se houver itens na categoria
                            itemContainer.style.display = hasItems ? "block" : "none";

                            if (!hasItems) {
                                alert(`Nenhum item disponível para a categoria: ${categoryName}`);
                            }
                        } else {
                            console.error("Atributos 'data-category' ou 'data-name' ausentes no botão.");
                        }
                    });
                });
            } else {
                console.error("Elementos necessários para os botões de categoria não foram encontrados no formulário de edição.");
            }
        });
    });
</script>
