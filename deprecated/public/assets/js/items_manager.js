export function init() {
    console.log("Módulo de categorias iniciado.");

    // Exibir/ocultar o gerenciador de categorias
    document.addEventListener("click", (event) => {
        if (event.target.matches("#toggleCategoriesBtn")) {
            const categoryManagement = document.getElementById('categoryManagement');
            if (categoryManagement) {
                categoryManagement.style.display = categoryManagement.style.display === 'none' ? 'block' : 'none';
            }
        }
    });

    // Abrir o formulário de edição da categoria
    document.addEventListener("click", (event) => {
        if (event.target.matches(".edit-category-btn")) {
            const { id, name, description } = event.target.dataset;
            document.getElementById('category-id').value = id;
            document.getElementById('edit-category-name').value = name;
            document.getElementById('edit-category-description').value = description;
            document.getElementById('editCategoryForm').style.display = 'block';
        }
    });

    // Fechar o formulário de edição da categoria
    document.getElementById("closeEditCategoryForm")?.addEventListener("click", () => {
        document.getElementById('editCategoryForm').style.display = 'none';
    });

    // Abrir o formulário de edição do item
    document.addEventListener("click", (event) => {
        if (event.target.matches(".edit-item-btn")) {
            const { id, name, description, price, categoryId } = event.target.dataset;
            document.getElementById('editItemId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editPrice').value = price;
            document.getElementById('editCategory').value = categoryId;

            // Atualiza a descrição da categoria
            const selectedOption = document.querySelector(`#editCategory option[value="${categoryId}"]`);
            const categoryDescription = selectedOption ? selectedOption.getAttribute('data-description') : '';
            document.getElementById('editCategory-description').textContent = categoryDescription;

            document.getElementById('editForm').style.display = 'block';
        }
    });

    // Fechar o formulário de edição do item
    document.getElementById("closeEditItemForm")?.addEventListener("click", () => {
        document.getElementById('editForm').style.display = 'none';
    });

    // Atualizar a descrição ao selecionar uma categoria
    function updateDescription(event) {
        const selectedOption = event.target.options[event.target.selectedIndex];
        const description = selectedOption?.getAttribute('data-description') || '';
        document.getElementById(`${event.target.id}-description`).textContent = description;
    }

    // Adicionar o evento de mudança nos selects de categorias
    document.querySelectorAll('#category, #editCategory').forEach(select => {
        select.addEventListener('change', updateDescription);
        select.addEventListener('change', (event) => {
            if (!event.target.value) {
                document.getElementById(`${event.target.id}-description`).textContent = '';
            }
        });
    });
}
