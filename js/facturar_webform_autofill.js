(function (Drupal) {
    Drupal.behaviors.facturacion_gt = {
      attach: function (context) {    
        // Selecciona el campo "cliente" usando un selector CSS
        const clienteField = context.querySelector('#select2-edit-cliente-container');
        
        if (clienteField) {
          // Asegúrate de que solo se asigne el evento una vez
          if (!clienteField.hasAttribute('data-autofill-initialized')) {
            clienteField.setAttribute('data-autofill-initialized', 'true');

            // Crea un MutationObserver para observar cambios en el atributo 'title'
            const observer = new MutationObserver(mutations => {
              mutations.forEach(mutation => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'title') {
                  const username = clienteField.getAttribute('title');
                  console.log('La biblioteca está cargada IDENTIFICACION', username);
                  
                  if (username) {
                    fetch(`/facturacion-gt/user-data/${username}`)
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
                          tipoPersonaField.value = data.field_tipo_de_persona;
                        }
                      })
                      .catch(error => {
                        console.error('Hubo un problema con la solicitud AJAX:', error);
                      });
                  }
                }
              });
            });
          
            // Configura el observer para observar cambios en los atributos
            observer.observe(clienteField, {
              attributes: true // Observa cambios en los atributos
            });
          }
        }
      }
    };
  })(Drupal);
  