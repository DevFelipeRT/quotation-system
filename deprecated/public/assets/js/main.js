document.addEventListener("DOMContentLoaded", function () {
    const page = document.body.getAttribute("file-name");

    if (page) {
        import(`./${page}.js`)
            .then(module => {
                if (module.init) {
                    module.init();
                } else {
                    console.error(`O módulo ${page}.js não possui uma função init.`);
                }
            })
            .catch(error => {
                console.error(`Erro ao carregar o módulo ${page}.js:`, error);
            });
    }
});
