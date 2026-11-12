
create extension if not exists pgcrypto;

create table if not exists public.profiles (
  id uuid primary key references auth.users(id) on delete cascade,
  role text not null default 'editor' check (role in ('admin', 'editor')),
  created_at timestamptz not null default now()
);

create or replace function public.is_admin()
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select coalesce((select role = 'admin' from public.profiles where id = auth.uid()), false);
$$;

revoke all on function public.is_admin() from public;
grant execute on function public.is_admin() to authenticated;

create table if not exists public.agents (
  id uuid primary key default gen_random_uuid(),
  name text not null check (char_length(name) between 1 and 100),
  phone text not null check (char_length(phone) between 1 and 30),
  whatsapp_number text not null check (whatsapp_number ~ '^[0-9]{8,20}$'),
  image_url text not null,
  display_order smallint not null unique check (display_order between 1 and 3),
  is_active boolean not null default true,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.properties (
  id uuid primary key default gen_random_uuid(),
  title text not null check (char_length(title) between 1 and 140),
  location text not null check (char_length(location) between 1 and 140),
  detail_url text not null check (detail_url ~ '^https?://'),
  image_url text not null,
  display_order integer not null unique check (display_order > 0),
  is_active boolean not null default true,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists agents_public_order_idx on public.agents (is_active, display_order);
create index if not exists properties_public_order_idx on public.properties (is_active, display_order);

create or replace function public.set_updated_at()
returns trigger
language plpgsql
set search_path = public
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

drop trigger if exists agents_set_updated_at on public.agents;
create trigger agents_set_updated_at before update on public.agents for each row execute function public.set_updated_at();
drop trigger if exists properties_set_updated_at on public.properties;
create trigger properties_set_updated_at before update on public.properties for each row execute function public.set_updated_at();

alter table public.profiles enable row level security;
alter table public.agents enable row level security;
alter table public.properties enable row level security;

create policy "profile owner can read own role" on public.profiles
  for select to authenticated using (id = auth.uid());

create policy "public reads active agents" on public.agents
  for select to anon, authenticated using (is_active or public.is_admin());
create policy "admins manage agents" on public.agents
  for all to authenticated using (public.is_admin()) with check (public.is_admin());

create policy "public reads active properties" on public.properties
  for select to anon, authenticated using (is_active or public.is_admin());
create policy "admins manage properties" on public.properties
  for all to authenticated using (public.is_admin()) with check (public.is_admin());

insert into storage.buckets (id, name, public)
values ('site-images', 'site-images', true)
on conflict (id) do update set public = excluded.public;

create policy "public reads site images" on storage.objects
  for select to public using (bucket_id = 'site-images');
create policy "admins upload site images" on storage.objects
  for insert to authenticated with check (bucket_id = 'site-images' and public.is_admin());
create policy "admins update site images" on storage.objects
  for update to authenticated using (bucket_id = 'site-images' and public.is_admin()) with check (bucket_id = 'site-images' and public.is_admin());
create policy "admins delete site images" on storage.objects
  for delete to authenticated using (bucket_id = 'site-images' and public.is_admin());

