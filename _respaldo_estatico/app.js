(function () {
  'use strict';

  const config = window.SUPABASE_CONFIG;
  const configured = config && config.url.startsWith('https://') && !config.anonKey.startsWith('PON_AQUI');
  if (!configured || !window.supabase) return;

  const client = window.supabase.createClient(config.url, config.anonKey);
  const agentsContainer = document.querySelector('#featured-agents');
  const propertiesContainer = document.querySelector('#featured-properties');

  const createElement = (tag, className, text) => {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (text) element.textContent = text;
    return element;
  };

  const renderAgents = (agents) => {
    if (!agents.length) return;
    agentsContainer.replaceChildren(...agents.map((agent) => {
      const card = createElement('article', 'card agent-card');
      const image = createElement('img');
      image.src = agent.image_url;
      image.alt = `${agent.name}, asesor inmobiliario`;
      const name = createElement('h3', '', agent.name);
      const link = createElement('a', '', `WhatsApp: ${agent.phone}`);
      link.href = `https://wa.me/${agent.whatsapp_number}`;
      link.target = '_blank';
      link.rel = 'noopener noreferrer';
      card.append(image, name, link);
      return card;
    }));
  };

  const renderProperties = (properties) => {
    if (!properties.length) return;
    propertiesContainer.replaceChildren(...properties.map((property) => {
      const card = createElement('article', 'card property-card');
      const image = createElement('img');
      image.src = property.image_url;
      image.alt = property.title;
      const details = createElement('div');
      const title = createElement('h3', '', property.title);
      const location = createElement('p', '', property.location);
      const link = createElement('a', '', 'Más información →');
      link.href = property.detail_url;
      link.target = '_blank';
      link.rel = 'noopener noreferrer';
      details.append(title, location, link);
      card.append(image, details);
      return card;
    }));
  };

  Promise.all([
    client.from('agents').select('name, phone, whatsapp_number, image_url, display_order').eq('is_active', true).order('display_order').limit(3),
    client.from('properties').select('title, location, detail_url, image_url, display_order').eq('is_active', true).order('display_order')
  ]).then(([agentsResult, propertiesResult]) => {
    if (!agentsResult.error) renderAgents(agentsResult.data || []);
    if (!propertiesResult.error) renderProperties(propertiesResult.data || []);
  }).catch(() => {
    // Se conserva el contenido estático si el servicio no está disponible.
  });
}());
