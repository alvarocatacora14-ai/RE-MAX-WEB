# Panel de administración — RE/MAX Zona

## Antes de usarlo

El respaldo original está en `backup-antes-panel-2026-08-26.zip`. El diseño público se conserva: si Supabase todavía no está configurado, el inicio muestra las tarjetas originales escritas en el HTML.

## Configuración en Supabase

1. Crea un proyecto en [Supabase](https://supabase.com/).
2. En **SQL Editor**, ejecuta `supabase/schema.sql` y luego `supabase/seed.sql`.
3. En **Authentication > Users**, crea el usuario administrador con correo y contraseña.
4. Copia el UUID de ese usuario y ejecuta en SQL Editor:

   ```sql
   insert into public.profiles (id, role)
   values ('UUID_DEL_USUARIO', 'admin');
   ```

5. En **Project Settings > API**, copia la URL del proyecto y la clave `anon` o `publishable`. Pégalas en `config.js`.
6. Publica la carpeta completa en un hosting estático. Abre `admin/index.html` para gestionar contenido.

## Seguridad

- `config.js` solo admite la URL y clave pública de Supabase.
- Nunca pegues `service_role`, `secret` ni credenciales de base de datos en ningún archivo del sitio.
- Las políticas RLS permiten lectura pública únicamente de registros activos.
- Las operaciones de escritura y cargas en `site-images` requieren una sesión con el rol `admin`.

## Alcance actual

El panel administra solamente vendedores destacados y proyectos inmobiliarios. Las demás secciones del inicio permanecen estáticas y sin cambios.

## Si se duplicaron proyectos o vendedores

Ejecuta `supabase/fix-property-duplicates.sql` una vez desde SQL Editor. Conserva el registro más antiguo de cada proyecto, normaliza el orden y evita que dos proyectos compartan la misma posición.

Para vendedores, ejecuta `supabase/fix-agent-duplicates.sql`. Conserva una única copia de cada asesor, restablece las posiciones 1, 2 y 3, y bloquea posiciones repetidas.
