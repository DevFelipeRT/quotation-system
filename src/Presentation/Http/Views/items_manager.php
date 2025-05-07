<?php require __DIR__ . '/Partials/header.php'; ?>

<?php
/** @var array $items */
/** @var array $categories */
?>

<h1><?= htmlspecialchars($headerTitle ?? 'Gerenciar Itens') ?></h1>

<!-- Formulário para adicionar item -->
<h2>Adicionar Item</h2>
<form action="/itemsManager/create" method="POST">
    <label for="name">Nome:</label>
    <input type="text" id="name" name="name" required>

    <label for="description">Descrição:</label>
    <textarea id="description" name="description"></textarea>

    <label for="price">Preço:</label>
    <input type="number" step="0.01" min="0" id="price" name="price" required>

    <label for="category_id">Categoria:</label>
    <select id="category_id" name="category_id" required>
        <option value="" disabled selected>Selecione uma categoria</option>
        <?php foreach ($categories as $category): ?>
            <option 
                value="<?= htmlspecialchars($category['id']) ?>"
                data-description="<?= htmlspecialchars($category['description']) ?>"
            >
                <?= htmlspecialchars($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p id="category-description"></p>

    <button type="submit">Adicionar Item</button>
</form>

<!-- Toggle de gerenciamento de categorias -->
<button id="toggleCategoriesBtn">Minhas Categorias</button>

<div id="categoryManagement" style="display: none;">
    <h2>Gerenciar Categorias</h2>

    <!-- Adicionar categoria -->
    <h3>Adicionar Categoria</h3>
    <form action="/itemsManager/categories/create" method="POST">
        <label for="categoryName">Nome:</label>
        <input type="text" id="categoryName" name="name" required>

        <label for="categoryDescription">Descrição:</label>
        <textarea id="categoryDescription" name="description"></textarea>

        <button type="submit">Adicionar Categoria</button>
    </form>

    <!-- Lista de categorias -->
    <h3>Lista de Categorias</h3>
    <table>
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
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td><?= htmlspecialchars($category['description']) ?></td>
                    <td>
                        <button class="edit-category-btn"
                                data-id="<?= $category['id'] ?>"
                                data-name="<?= htmlspecialchars($category['name']) ?>"
                                data-description="<?= htmlspecialchars($category['description']) ?>">
                            Editar
                        </button>
                        <form action="/itemsManager/categories/delete" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $category['id'] ?>">
                            <button type="submit" onclick="return confirm('Ao excluir esta categoria, todos os itens associados serão removidos. Continuar?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulário de edição de categoria -->
    <div id="editCategoryForm" style="display:none;">
        <h3>Editar Categoria</h3>
        <form action="/itemsManager/categories/update" method="POST">
            <input type="hidden" id="category-id" name="id">

            <label for="edit-category-name">Nome:</label>
            <input type="text" id="edit-category-name" name="name" required>

            <label for="edit-category-description">Descrição:</label>
            <textarea id="edit-category-description" name="description"></textarea>

            <button type="submit">Salvar Alterações</button>
            <button type="button" id="closeEditCategoryForm">Cancelar</button>
        </form>
    </div>
</div>

<!-- Lista de Itens -->
<h2>Lista de Itens</h2>
<table>
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
                    <td><?= htmlspecialchars($item->getName()) ?></td>
                    <td><?= htmlspecialchars($item->getDescription()) ?></td>
                    <td>R$ <?= number_format($item->getPrice(), 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($item->getCategoryName()) ?></td>
                    <td>
                        <button class="edit-item-btn"
                                data-id="<?= $item->getId() ?>"
                                data-name="<?= htmlspecialchars($item->getName()) ?>"
                                data-description="<?= htmlspecialchars($item->getDescription()) ?>"
                                data-price="<?= $item->getPrice() ?>"
                                data-category-id="<?= $item->getCategoryId() ?>">
                            Editar
                        </button>
                        <form action="/itemsManager/delete" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $item->getId() ?>">
                            <button type="submit" onclick="return confirm('Tem certeza que deseja excluir este item?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Nenhum item cadastrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Formulário de edição de item -->
<div id="editForm" style="display:none;">
    <h2>Editar Item</h2>
    <form action="/itemsManager/update" method="POST">
        <input type="hidden" id="editItemId" name="id">

        <label for="editName">Nome:</label>
        <input type="text" id="editName" name="name" required>

        <label for="editDescription">Descrição:</label>
        <textarea id="editDescription" name="description"></textarea>

        <label for="editPrice">Preço:</label>
        <input type="number" step="0.01" id="editPrice" name="price" required>

        <label for="editCategory">Categoria:</label>
        <select id="editCategory" name="category_id" required>
            <option value="" disabled selected>Selecione uma categoria</option>
            <?php foreach ($categories as $category): ?>
                <option 
                    value="<?= $category['id'] ?>"
                    data-description="<?= $category['description'] ?>"
                >
                    <?= $category['name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p id="editCategory-description"></p>

        <button type="submit">Atualizar Item</button>
        <button type="button" id="closeEditItemForm">Cancelar</button>
    </form>
</div>

<?php require __DIR__ . '/Partials/footer.php'; ?>