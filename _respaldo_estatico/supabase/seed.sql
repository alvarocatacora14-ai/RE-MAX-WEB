insert into public.agents (name, phone, whatsapp_number, image_url, display_order, is_active) values
  ('Jessica Sardon', '969 322 977', '51969322977', 'Ima/Top1.jpeg', 1, true),
  ('Vanessa Ramirez', '958 953 697', '51958953697', 'Ima/Top2.jpeg', 2, true),
  ('Julissa Valdivia', '934 712 169', '51934712169', 'Ima/Top3.jpeg', 3, true)
on conflict (display_order) do nothing;

insert into public.properties (title, location, detail_url, image_url, display_order, is_active) values
  ('Proyecto Zeus', 'Cayma, Arequipa', 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-cerro-colorado-arequipa-arequipa-1153660/', 'Ima/Zeus1.jpg', 1, true),
  ('Proyecto Costa Dorada', 'Islay, Arequipa', 'https://www.remax.pe/web/search/property/propiedad-terreno-en-venta-mollendo-islay-arequipa-1156893/', 'Ima/Costa1.jpg', 2, true),
  ('Edificio Samay', 'Cayma, Arequipa', 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-cayma-arequipa-arequipa-1168464/', 'Ima/EdSamay1.jpg', 3, true),
  ('Proyecto Gardenia', 'Cayma, Arequipa', 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-cayma-arequipa-arequipa-1164684/', 'Ima/Gardenia1.jpg', 4, true),
  ('Proyecto Chacarilla', 'San Borja, Lima', 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-san-borja-lima-lima-1149713/', 'Ima/ProyectoChacarilla1.jpg', 5, true),
  ('Departamento Av. Pardo', 'Miraflores, Lima', 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-miraflores-lima-lima-1163921/', 'Ima/DptoLima1.jpg', 6, true),
  ('Casa Urb. Garibaldi', 'Ilo, Moquegua', 'https://www.remax.pe/web/search/property/propiedad-casa-en-venta-ilo-ilo-moquegua-1165067/', 'Ima/CasaIlo.jpg', 7, true)
on conflict (display_order) do nothing;
