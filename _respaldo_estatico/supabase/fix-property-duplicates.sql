-- Limpieza segura de proyectos repetidos.
-- Ejecutar una sola vez en Supabase Dashboard > SQL Editor.
-- Conserva el registro más antiguo de cada título y elimina únicamente las copias posteriores.

begin;

with ranked_projects as (
  select
    id,
    row_number() over (
      partition by lower(btrim(title))
      order by created_at asc, id asc
    ) as duplicate_number
  from public.properties
)
delete from public.properties as project
using ranked_projects as ranked
where project.id = ranked.id
  and ranked.duplicate_number > 1;

-- Vuelve a numerar los proyectos manteniendo el orden actual.
with ordered_projects as (
  select
    id,
    row_number() over (order by display_order asc, created_at asc, id asc) as new_display_order
  from public.properties
)
update public.properties as project
set display_order = ordered.new_display_order
from ordered_projects as ordered
where project.id = ordered.id;

-- Evita que dos proyectos tengan la misma posición en adelante.
create unique index if not exists properties_display_order_unique_idx
  on public.properties (display_order);

commit;

-- Verificación: debe devolver un solo proyecto por posición.
select display_order, title, is_active
from public.properties
order by display_order;
