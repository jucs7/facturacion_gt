(function (Drupal) {
    Drupal.behaviors.facturacion_gt = {
      attach: function (context) {    
        // Selecciona el campo "cliente"
        const clienteField = context.querySelector('#edit-cliente');
        
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
      }
    };
  })(Drupal);
  