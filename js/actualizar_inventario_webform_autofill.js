(function (Drupal) {
    Drupal.behaviors.facturacion_gt = {
        attach: function (context) {
            const productosFields = context.querySelectorAll(
                'input[name*="[producto]"]'
            );

            if (productosFields) {
                productosFields.forEach((field) => {
                    // Asegurar de que solo se asigne el evento una vez
                    if (!field.hasAttribute("data-autofill-initialized")) {
                        field.setAttribute("data-autofill-initialized", "true");

                        field.addEventListener("change", function (event) {
                            const value = field.value;

                            // Extrae el ID del texto ingresado (formato: "Producto (ID)")
                            const match = value.match(/\((\d+)\)$/); // Busca un número entre paréntesis al final
                            const pid = match ? match[1] : null;

                            if (pid) {
                                fetch(`/facturacion-gt/product-data/${pid}`)
                                    .then((response) => {
                                        if (!response.ok) {
                                            throw new Error(
                                                "Error al obtener los datos del producto"
                                            );
                                        }
                                        return response.json();
                                    })
                                    .then((data) => {
                                        // Rellena el campo de stock
                                        const stockField = field
                                            .closest("tr")
                                            .querySelector('input[name*="[stock]"]');

                                        if (stockField) {
                                            stockField.value = data.field_stock;
                                        }
                                    })
                                    .catch((error) => {
                                        console.error(
                                            "Hubo un problema con la solicitud AJAX:",
                                            error
                                        );
                                    });
                            }
                        });
                    }
                });
            }
        },
    };
})(Drupal);
