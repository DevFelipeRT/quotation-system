<!-- Conteúdo da Página -->
<h1>Gerenciar Itens</h1>

<!-- Formulário para adicionar um item -->
<h2>Adicionar Item</h2>
<form action="items_manager.php" method="POST">
    <input type="hidden" name="action" value="add-item">
    
    <label for="name">Nome:</label>
    <input type="text" name="name" id="name" required>

    <label for="description">Descrição:</label>
    <textarea name="description" id="description"></textarea>

    <label for="price">Preço:</label>
    <input type="number" step="0.01" name="price" id="price">

    <label for="category">Categoria:</label>
    <select name="categoryId" id="category" required>
        <option value="" disabled selected>Selecione uma categoria</option>
        <?php foreach ($categories as $category): ?>
            <option 
                value="<?php echo $category[CATEGORIES_ID_COLUMN]; ?>" 
                data-description="<?php echo $category[CATEGORIES_DESCRIPTION_COLUMN]; ?>"
            >
                <?php echo $category[CATEGORIES_NAME_COLUMN]; ?>
            </option>
        <?php endforeach ?>
    </select>
    <p id="category-description"></p>

    <button type="submit">Adicionar Item</button>
</form>

<!-- Botão para exibir/ocultar categorias -->
<button id="toggleCategoriesBtn">Minhas Categorias</button>

<!-- Div para Gerenciamento de Categorias (inicialmente oculta) -->
<div id="categoryManagement" style="display: none;">
    <h2>Gerenciar Categorias</h2>

    <!-- Formulário para adicionar uma categoria -->
    <h3>Adicionar Categoria</h3>
    <form action="items_manager.php" method="POST">
        <input type="hidden" name="action" value="add-category">

        <label for="categoryName">Nome da Categoria:</label>
        <input type="text" name="name" id="categoryName" required>

        <label for="categoryDescription">Descrição:</label>
        <textarea name="description" id="categoryDescription"></textarea>

        <button type="submit">Adicionar Categoria</button>
    </form>

    <!-- Lista de categorias -->
    <h3>Lista de Categorias</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo htmlspecialchars($category[CATEGORIES_NAME_COLUMN]); ?></td>
                    <td><?php echo htmlspecialchars($category[CATEGORIES_DESCRIPTION_COLUMN]); ?></td>
                    <td>
                        <!-- Botão para abrir o formulário de edição -->
                        <button class="edit-category-btn"
                                data-id="<?php echo $category[CATEGORIES_ID_COLUMN]; ?>"
                                data-name="<?php echo htmlspecialchars($category[CATEGORIES_NAME_COLUMN]); ?>"
                                data-description="<?php echo htmlspecialchars($category[CATEGORIES_DESCRIPTION_COLUMN]); ?>">
                            Editar
                        </button>
                        <!-- Formulário para exclusão da categoria -->
                        <form action="items_manager.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete-category">
                            <input type="hidden" name="id" value="<?php echo $category[CATEGORIES_ID_COLUMN]; ?>">
                            <button type="submit" onclick="return confirm('Ao excluir esta categoria, todos os itens associados serão removidos permanentemente. Você deseja continuar?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulário de Edição (inicialmente escondido) -->
    <div id="editCategoryForm" style="display:none;">
        <h3>Editar Categoria</h3>
        <form action="items_manager.php" method="POST">
            <input type="hidden" name="action" value="update-category">
            <input type="hidden" name="id" id="category-id">
            
            <label for="edit-category-name">Nome:</label>
            <input type="text" name="name" id="edit-category-name" required>
            
            <label for="edit-category-description">Descrição:</label>
            <textarea name="description" id="edit-category-description"></textarea>
            
            <button type="submit">Salvar Alterações</button>
            <button type="button" id="closeEditCategoryForm">Cancelar</button>
        </form>
    </div>
</div>

<!-- Lista de itens -->
<h2>Lista de Itens</h2>
<table border="1">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Preço</th>
            <th>Categoria</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item->getName()); ?></td>
                    <td><?php echo htmlspecialchars($item->getDescription()); ?></td>
                    <td><?php echo number_format($item->getPrice(), 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($item->getCategoryName()); ?></td>
                    <td>
                        <!-- Botão para abrir o formulário de edição -->
                        <button class="edit-item-btn"
                                data-id="<?php echo $item->getId(); ?>"
                                data-name="<?php echo htmlspecialchars($item->getName()); ?>"
                                data-description="<?php echo htmlspecialchars($item->getDescription()); ?>"
                                data-price="<?php echo $item->getPrice(); ?>"
                                data-category-id="<?php echo $item->getCategoryId(); ?>">
                            Editar
                        </button>

                        <!-- Formulário para exclusão do item -->
                        <form action="items_manager.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete-item">
                            <input type="hidden" name="id" value="<?php echo $item->getId(); ?>">
                            <button type="submit" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                        
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Nenhum item encontrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Formulário de edição de itens (inicialmente oculto) -->
<div id="editForm" style="display:none;">
    <h2>Editar Item</h2>
    <form action="items_manager.php" method="POST">
        <input type="hidden" name="action" value="update-item">
        <input type="hidden" name="id" id="editItemId">
        
        <label for="editName">Nome:</label>
        <input type="text" name="name" id="editName" required>

        <label for="editDescription">Descrição:</label>
        <textarea name="description" id="editDescription"></textarea>

        <label for="editPrice">Preço:</label>
        <input type="number" step="0.01" name="price" id="editPrice">

        <label for="editCategory">Categoria:</label>
        <select name="categoryId" id="editCategory" required>
            <option value="" disabled selected>Selecione uma categoria</option>
            <?php foreach ($categories as $category): ?>
                <option 
                    value="<?php echo $category[CATEGORIES_ID_COLUMN]; ?>" 
                    data-description="<?php echo $category[CATEGORIES_DESCRIPTION_COLUMN]; ?>"
                >
                    <?php echo $category[CATEGORIES_NAME_COLUMN]; ?>
                </option>
            <?php endforeach ?>
        </select>
        <p id="editCategory-description"></p>

        <button type="submit">Atualizar Item</button>
        <button type="button" id="closeEditItemForm">Cancelar</button>
    </form>
</div>
