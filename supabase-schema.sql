-- Run this in Supabase SQL Editor

create table clients (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  email text,
  phone text,
  company text,
  notes text,
  status text default 'active',
  created_at timestamptz default now()
);

create table invoices (
  id uuid primary key default gen_random_uuid(),
  client_id uuid references clients(id) on delete cascade,
  invoice_number text not null,
  amount numeric(10,2) default 0,
  status text default 'pending',
  due_date date,
  notes text,
  items jsonb default '[]',
  created_at timestamptz default now()
);

create table leads (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  email text not null,
  phone text,
  service text,
  message text,
  status text default 'new',
  created_at timestamptz default now()
);

-- Enable Row Level Security
alter table clients enable row level security;
alter table invoices enable row level security;
alter table leads enable row level security;

-- Policies: only authenticated users can access clients and invoices
create policy "auth_clients" on clients for all to authenticated using (true) with check (true);
create policy "auth_invoices" on invoices for all to authenticated using (true) with check (true);
create policy "auth_leads" on leads for all to authenticated using (true) with check (true);

-- Leads can be inserted publicly (from website forms)
create policy "public_insert_leads" on leads for insert to anon with check (true);
