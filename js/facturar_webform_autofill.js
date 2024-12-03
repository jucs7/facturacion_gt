(function (Drupal) {
    Drupal.behaviors.facturacion_gt = {
      attach: function (context) {    
        const clienteField = context.querySelector('#edit-cliente'); // Campo "cliente"
        const productosFields = context.querySelectorAll('#productos_composite_table input.form-element--api-entity-autocomplete'); // Campos de "producto"
        const cantidadFields = context.querySelectorAll('#productos_composite_table input[name*="[cantidad]"]'); // Campos de "cantidad"

        if (clienteField) {
          // Asegurar de que solo se asigne el evento una vez
          if (!clienteField.hasAttribute('data-autofill-initialized')) {
            clienteField.setAttribute('data-autofill-initialized', 'true');

            clienteField.addEventListener('change', function () {
              const value = clienteField.value;

              // Extrae el ID del texto ingresado (formato: "Nombre (ID)")
              const match = value.match(/\((\d+)\)$/); // Busca un número entre paréntesis al final
              const uid = match ? match[1] : null;

              if (uid) {
                fetch(`/facturacion-gt/user-data/${uid}`)
                .then(response => {
                  if (!response.ok) {
                    throw new Error('Error al obtener los datos del usuario');
                  }
                  return response.json();
                })
                .then(data => {
                  // Rellena los campos con los valores obtenidos
                  const nombreField = context.querySelector('#edit-nombre-o-razon-social');
                  const identificacionField = context.querySelector('#edit-identificacion');
                  const tipoPersonaField = context.querySelector('#edit-tipo-de-persona');

                  if (nombreField) {
                    nombreField.value = data.field_nombre_completo;
                  }

                  if (identificacionField) {
                    identificacionField.value = data.field_identificacion;
                  }

                  if (tipoPersonaField) {
                    if (data.field_tipo_de_persona === '1') {
                      tipoPersonaField.value = 'Persona juridica';
                    } else {
                      tipoPersonaField.value = 'Persona natural';
                    }
                  }
                })
                .catch(error => {
                  console.error('Hubo un problema con la solicitud AJAX:', error);
                });
              }
            });
          }
        }

        if (productosFields) {
          productosFields.forEach((field) => {
            // Asegurar de que solo se asigne el evento una vez
            if (!field.hasAttribute('data-autofill-initialized')) {
              field.setAttribute('data-autofill-initialized', 'true');
              
              field.addEventListener('change', function (event) {
                const value = field.value;

                // Extrae el ID del texto ingresado (formato: "Producto (ID)")
                const match = value.match(/\((\d+)\)$/); // Busca un número entre paréntesis al final
                const pid = match ? match[1] : null;

                if (pid) {
                  fetch(`/facturacion-gt/product-data/${pid}`)
                  .then(response => {
                    if (!response.ok) {
                      throw new Error('Error al obtener los datos del producto');
                    }
                    return response.json();
                  })
                  .then(data => {
                    // Rellena los campos con valores obtenidos
                    const precioField = field.closest('tr').querySelector('input[name*="[precio]"]');
                    const cantidadField = field.closest('tr').querySelector('input[name*="[cantidad]"]');
                    const subtotalField = field.closest('tr').querySelector('input[name*="[subtotal]"]');

                    if (precioField) {
                      precioField.value = data.field_precio;
                    }

                    if (cantidadField) {
                      cantidadField.value = 1;
                    }

                    if (subtotalField) {
                      subtotalField.value = precioField.value * cantidadField.value;
                    }
                  })
                  .catch(error => {
                    console.error('Hubo un problema con la solicitud AJAX:', error);
                  });
                }
              });
            }
          });
        }

        if (cantidadFields) {
          cantidadFields.forEach((field) => {
            // Asegurar de que solo se asigne el evento una vez
            if (!field.hasAttribute('data-autofill-initialized')) {
              field.setAttribute('data-autofill-initialized', 'true');

              field.addEventListener('change', function (event) {
                //Actualiza los campos con el valor obtenido
                const cantidadValue = field.value;
                const precioField = field.closest('tr').querySelector('input[name*="[precio]"]');
                const subtotalField = field.closest('tr').querySelector('input[name*="[subtotal]"]');

                if (precioField && subtotalField) {
                  subtotalField.value = precioField.value * cantidadValue;
                }
              });
            }
          });
        }
      }
    };
  })(Drupal);
  