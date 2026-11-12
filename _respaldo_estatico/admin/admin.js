(function () {
  'use strict';

  const config = window.SUPABASE_CONFIG;
  const isConfigured = config && config.url.startsWith('https://') && !config.anonKey.startsWith('PON_AQUI');
  const loginView = document.querySelector('#login-view');
  const adminView = document.querySelector('#admin-view');
  const loginMessage = document.querySelector('#login-message');
  const adminMessage = document.querySelector('#admin-message');
  if (!isConfigured || !window.supabase) {
    loginMessage.textContent = 'Configura primero config.js con la URL y la clave anónima de Supabase.';
    return;
  }

  const client = window.supabase.createClient(config.url, config.anonKey);
  let agents = [];
  let properties = [];
  const message = (text, success = false) => {
    adminMessage.textContent = text;
    adminMessage.classList.toggle('success', success);
  };
  const node = (tag, text, className) => {
    const element = document.createElement(tag);
    if (text) element.textContent = text;
    if (className) element.className = className;
    return element;
  };
  const button = (label, className, handler) => {
    const element = node('button', label, className);
    element.type = 'button';
    element.addEventListener('click', handler);
    return element;
  };
  const imagePath = async (file, type) => {
    if (!file) return null;

    if (!file.type.startsWith('image/')) {
        throw new Error('El archivo seleccionado debe ser una imagen.');
    }

    if (file.size > 5 * 1024 * 1024) {
        throw new Error('La imagen no puede superar 5 MB.');
    }

    const extension =
        file.name
            .split('.')
            .pop()
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '') || 'jpg';

    const path = `${type}/${crypto.randomUUID()}.${extension}`;

    message('Subiendo imagen...');

    const upload = await client.storage
        .from('site-images')
        .upload(path, file, {
            cacheControl: '3600',
            upsert: false
        });

    if (upload.error) {
        console.error('Error al subir imagen:', upload.error);
        throw new Error('No se pudo subir la imagen.');
    }

    const publicUrl = client.storage
        .from('site-images')
        .getPublicUrl(path)
        .data
        .publicUrl;

    if (!publicUrl) {
        throw new Error('La imagen se subió, pero no se pudo obtener su URL.');
    }

    message('Imagen subida correctamente.', true);

    return publicUrl;
};

  const resetAgentForm = () => {
    document.querySelector('#agent-form').reset();
    document.querySelector('#agent-id').value = '';
    document.querySelector('#agent-active').checked = true;
    document.querySelector('#agent-form-title').textContent = 'Editar vendedor';
  };
  const resetPropertyForm = () => {
    document.querySelector('#property-form').reset();
    document.querySelector('#property-id').value = '';
    document.querySelector('#property-active').checked = true;
    document.querySelector('#property-form-title').textContent = 'Nuevo proyecto';
    document.querySelector('#property-form').hidden = true;
  };
  const editAgent = (item) => {
    document.querySelector('#agent-id').value = item.id;
    document.querySelector('#agent-name').value = item.name;
    document.querySelector('#agent-phone').value = item.phone;
    document.querySelector('#agent-whatsapp').value = item.whatsapp_number;
    document.querySelector('#agent-order').value = item.display_order;
    document.querySelector('#agent-active').checked = item.is_active;
    document.querySelector('#agent-form-title').textContent = `Editar: ${item.name}`;
    document.querySelector('#agent-name').focus();
  };
  const editProperty = (item) => {
    document.querySelector('#property-form').hidden = false;
    document.querySelector('#property-id').value = item.id;
    document.querySelector('#property-title').value = item.title;
    document.querySelector('#property-location').value = item.location;
    document.querySelector('#property-url').value = item.detail_url;
    document.querySelector('#property-order').value = item.display_order;
    document.querySelector('#property-active').checked = item.is_active;
    document.querySelector('#property-form-title').textContent = `Editar: ${item.title}`;
    document.querySelector('#property-title').focus();
  };
  const renderAgents = () => {
    const list = document.querySelector('#agents-list');
    list.replaceChildren(...agents.map((item) => {
      const card = node('article', '', 'item agent-item');
      const image = node('img'); image.src = item.image_url; image.alt = item.name;
      const details = node('div'); details.append(node('h3', item.name), node('p', `WhatsApp: ${item.phone}`));
      const position = node('span', `#${item.display_order}`);
      const status = node('span', item.is_active ? 'Activo' : 'Inactivo', item.is_active ? 'status' : 'status off');
      const actions = node('div', '', 'item-actions'); actions.append(button('✎', '', () => editAgent(item)));
      card.append(image, details, position, status, actions); return card;
    }));
    document.querySelector('#agents-count').textContent = `${agents.length} vendedor${agents.length === 1 ? '' : 'es'}`;
  };
  const renderProperties = () => {
    const list = document.querySelector('#properties-list');
    list.replaceChildren(...properties.map((item) => {
      const card = node('article', '', 'item property-item');
      const image = node('img'); image.src = item.image_url; image.alt = item.title;
      const details = node('div'); details.append(node('h3', item.title), node('p', `⌖ ${item.location}`));
      const status = node('span', item.is_active ? 'Activo' : 'Inactivo', item.is_active ? 'status' : 'status off');
      const actions = node('div', '', 'item-actions');
      actions.append(button('✎', '', () => editProperty(item)), button('⌫', 'delete', async () => {
        if (!window.confirm(`¿Eliminar “${item.title}”? Esta acción no elimina su imagen del almacenamiento.`)) return;
        const result = await client.from('properties').delete().eq('id', item.id);
        if (result.error) return message(result.error.message);
        message('Proyecto eliminado.', true); await loadContent();
      }));
      card.append(image, details, node('span', `Orden ${item.display_order}`), status, actions); return card;
    }));
    document.querySelector('#properties-count').textContent = `${properties.length} proyecto${properties.length === 1 ? '' : 's'}`;
  };
  const loadContent = async () => {
    const [agentsResult, propertiesResult] = await Promise.all([
      client.from('agents').select('*').order('display_order'),
      client.from('properties').select('*').order('display_order')
    ]);
    if (agentsResult.error || propertiesResult.error) return message((agentsResult.error || propertiesResult.error).message);
    agents = agentsResult.data; properties = propertiesResult.data; renderAgents(); renderProperties();
  };
  const openAdmin = async () => { loginView.hidden = true; adminView.hidden = false; await loadContent(); };
  const verifyAdmin = async () => {
    const session = (await client.auth.getSession()).data.session;
    if (!session) return;
    const profile = await client.from('profiles').select('role').eq('id', session.user.id).maybeSingle();
    if (profile.error || !profile.data || profile.data.role !== 'admin') {
      await client.auth.signOut(); loginMessage.textContent = 'Esta cuenta no tiene permisos de administrador.'; return;
    }
    await openAdmin();
  };

/*
if (result.error) {
    console.error('Error de inicio de sesión:', result.error);
    loginMessage.textContent = result.error.message;
    return;
}*/
  
document.querySelector('#login-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    loginMessage.textContent = '';

    const result = await client.auth.signInWithPassword({
        email: document.querySelector('#email').value,
        password: document.querySelector('#password').value
    });

    if (result.error) {
        loginMessage.textContent =
            'No fue posible iniciar sesión. Verifica tus credenciales.';
        return;
    }

    await verifyAdmin();
});

// RECUPERACIÓN DE CONTRASEÑA
document.querySelector('#forgot-password').addEventListener('click', async (event) => {
    event.preventDefault();

    const email = document.querySelector('#email').value.trim();
    if (!email) {
        loginMessage.textContent =
            'Escribe tu correo electrónico antes de solicitar la recuperación.';
        document.querySelector('#email').focus();
        return;
    }

    loginMessage.textContent = 'Enviando correo de recuperación...';

    const { error } = await client.auth.resetPasswordForEmail(email, {
        redirectTo: 'http://127.0.0.1:5500/restablecer.html'
    });

    if (error) {
        console.error('Error de recuperación:', error);
        loginMessage.textContent =
            'No se pudo enviar el correo. Revisa la configuración de Supabase.';
        return;
    }

    loginMessage.textContent =
        'Si el correo corresponde a una cuenta registrada, recibirás instrucciones para restablecer tu contraseña.';
});

  document.querySelector('#sign-out').addEventListener('click', async () => { await client.auth.signOut(); adminView.hidden = true; loginView.hidden = false; });
  document.querySelector('#agent-reset').addEventListener('click', resetAgentForm);
  document.querySelector('#property-reset').addEventListener('click', resetPropertyForm);
  document.querySelector('#new-property').addEventListener('click', () => { resetPropertyForm(); document.querySelector('#property-form').hidden = false; document.querySelector('#property-title').focus(); });
  document.querySelectorAll('.tab').forEach((tab) => tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach((item) => { item.classList.toggle('is-active', item === tab); item.setAttribute('aria-selected', item === tab ? 'true' : 'false'); });
    document.querySelectorAll('.panel').forEach((panel) => { panel.hidden = panel.id !== tab.dataset.panel; });
  }));
  document.querySelector('#agent-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    const form = document.querySelector('#agent-form');
    const submitButton = form.querySelector('button[type="submit"]');

    // Evita múltiples clics mientras se está guardando
    if (submitButton.disabled) return;

    submitButton.disabled = true;
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Guardando...';

    message('');

    try {
        const id = document.querySelector('#agent-id').value.trim();

        // Para editar, el vendedor debe tener un ID
        if (!id) {
            throw new Error('No se encontró el vendedor que deseas editar.');
        }

        const previous = agents.find(
    (item) => String(item.id) === String(id)
);

        if (!previous) {
            throw new Error('No se encontró el vendedor seleccionado.');
        }

        const position = Number(
            document.querySelector('#agent-order').value
        );

        // Solo se permiten las posiciones 1, 2 y 3
        if (![1, 2, 3].includes(position)) {
            throw new Error('La posición debe ser 1, 2 o 3.');
        }

        // Verificar si otro vendedor ya utiliza esa posición
        const positionAlreadyUsed = agents.some(
    (item) =>
        String(item.id) !== String(id) &&
        Number(item.display_order) === position
);

if (positionAlreadyUsed) {
    throw new Error(
        `La posición ${position} ya está ocupada por otro vendedor.`
    );
}

        // Obtener la imagen seleccionada
        const imageFile = document.querySelector('#agent-image').files[0];

        let imageUrl = previous.image_url;

        if (imageFile) {
    imageUrl = await imagePath(imageFile, 'agents');

    if (!imageUrl) {
        throw new Error('No se pudo subir la imagen.');
    }
}

        if (!imageUrl) {
            throw new Error('El vendedor debe tener una imagen.');
        }

        const payload = {
            name: document.querySelector('#agent-name').value.trim(),
            phone: document.querySelector('#agent-phone').value.trim(),
            whatsapp_number: document.querySelector('#agent-whatsapp').value.trim(),
            display_order: position,
            is_active: document.querySelector('#agent-active').checked,
            image_url: imageUrl
        };

        // Validaciones básicas
        if (!payload.name) {
            throw new Error('Ingresa el nombre del vendedor.');
        }

        if (!payload.phone) {
            throw new Error('Ingresa el teléfono del vendedor.');
        }

        if (!payload.whatsapp_number) {
            throw new Error('Ingresa el número de WhatsApp.');
        }

        // IMPORTANTE:
        // Como estamos EDITANDO, solamente hacemos UPDATE.
        const result = await client
            .from('agents')
            .update(payload)
            .eq('id', id);

            console.log('ID que se está editando:', id);
console.log('Datos que se intentan guardar:', payload);
console.log('Resultado de Supabase:', result);

        if (result.error) {
            // Error típico de posición duplicada por la restricción UNIQUE
            if (result.error.code === '23505') {
                throw new Error(
                    `La posición ${position} ya está ocupada por otro vendedor.`
                );
            }

            throw result.error;
        }

        message('Vendedor actualizado correctamente.', true);

        // Limpiar el formulario después de guardar
        resetAgentForm();

        // Volver a cargar los datos reales desde Supabase
        await loadContent();

    } catch (error) {
        console.error('Error al actualizar vendedor:', error);
        message(error.message || 'No se pudieron guardar los cambios.');
    } finally {
        // Volver a habilitar el botón
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    }
});
  document.querySelector('#property-form').addEventListener('submit', async (event) => {
    event.preventDefault(); message('');
    try {
      const id = document.querySelector('#property-id').value;
      const previous = properties.find((item) => item.id === id);
      const imageUrl = (await imagePath(document.querySelector('#property-image').files[0], 'properties')) || previous?.image_url;
      if (!imageUrl) throw new Error('Selecciona una imagen para el proyecto.');
      const payload = { title: document.querySelector('#property-title').value.trim(), location: document.querySelector('#property-location').value.trim(), detail_url: document.querySelector('#property-url').value.trim(), display_order: Number(document.querySelector('#property-order').value), is_active: document.querySelector('#property-active').checked, image_url: imageUrl };
      const result = id ? await client.from('properties').update(payload).eq('id', id) : await client.from('properties').insert(payload);
      if (result.error) throw result.error; message('Proyecto guardado.', true); resetPropertyForm(); await loadContent();
    } catch (error) { message(error.message); }
  });
  verifyAdmin();


/*
const { data, error } = await client.auth.resetPasswordForEmail(
    email,
    {
        redirectTo: 'http://127.0.0.1:5500/restablecer.html'
    }
);

if (error) {
    console.error('Error al solicitar recuperación:', error.message);
}

*/

}());
