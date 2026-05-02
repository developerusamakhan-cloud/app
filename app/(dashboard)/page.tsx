import { createClient } from '@/lib/supabase/server'
import Header from '@/components/Header'
import { Users, FileText, TrendingUp, DollarSign } from 'lucide-react'

async function getStats(supabase: Awaited<ReturnType<typeof createClient>>) {
  const [clients, invoices, leads] = await Promise.all([
    supabase.from('clients').select('id', { count: 'exact', head: true }),
    supabase.from('invoices').select('amount, status'),
    supabase.from('leads').select('id', { count: 'exact', head: true }),
  ])

  const totalRevenue = (invoices.data ?? [])
    .filter(inv => inv.status === 'paid')
    .reduce((sum, inv) => sum + (inv.amount ?? 0), 0)

  const pendingInvoices = (invoices.data ?? []).filter(inv => inv.status === 'pending').length

  return {
    clients: clients.count ?? 0,
    totalRevenue,
    pendingInvoices,
    leads: leads.count ?? 0,
  }
}

export default async function DashboardPage() {
  const supabase = await createClient()
  const stats = await getStats(supabase)

  const cards = [
    { label: 'Total Clients', value: stats.clients, icon: Users, color: 'bg-blue-50 text-blue-600', border: 'border-blue-100' },
    { label: 'Total Revenue', value: `$${stats.totalRevenue.toLocaleString()}`, icon: DollarSign, color: 'bg-green-50 text-green-600', border: 'border-green-100' },
    { label: 'Pending Invoices', value: stats.pendingInvoices, icon: FileText, color: 'bg-amber-50 text-amber-600', border: 'border-amber-100' },
    { label: 'New Leads', value: stats.leads, icon: TrendingUp, color: 'bg-violet-50 text-violet-600', border: 'border-violet-100' },
  ]

  return (
    <div>
      <Header title="Dashboard" />
      <div className="p-6 space-y-6">
        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
          {cards.map(({ label, value, icon: Icon, color, border }) => (
            <div key={label} className={`bg-white rounded-xl p-5 border ${border} shadow-sm`}>
              <div className="flex items-center justify-between mb-3">
                <span className="text-sm text-slate-500">{label}</span>
                <div className={`w-9 h-9 rounded-lg ${color} flex items-center justify-center`}>
                  <Icon size={18} />
                </div>
              </div>
              <p className="text-2xl font-bold text-slate-800">{value}</p>
            </div>
          ))}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <RecentActivity supabase={supabase} />
          <RecentLeads supabase={supabase} />
        </div>
      </div>
    </div>
  )
}

async function RecentActivity({ supabase }: { supabase: Awaited<ReturnType<typeof createClient>> }) {
  const { data: invoices } = await supabase
    .from('invoices')
    .select('id, invoice_number, amount, status, clients(name)')
    .order('created_at', { ascending: false })
    .limit(5)

  return (
    <div className="bg-white rounded-xl border border-slate-200 shadow-sm">
      <div className="px-5 py-4 border-b border-slate-100">
        <h2 className="font-semibold text-slate-800">Recent Invoices</h2>
      </div>
      <div className="divide-y divide-slate-50">
        {!invoices?.length && (
          <p className="px-5 py-8 text-center text-slate-400 text-sm">No invoices yet</p>
        )}
        {invoices?.map(inv => (
          <div key={inv.id} className="px-5 py-3 flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-slate-700">#{inv.invoice_number}</p>
              <p className="text-xs text-slate-400">{(inv.clients as unknown as { name: string } | null)?.name ?? '—'}</p>
            </div>
            <div className="text-right">
              <p className="text-sm font-semibold text-slate-800">${inv.amount?.toLocaleString()}</p>
              <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${
                inv.status === 'paid' ? 'bg-green-100 text-green-700' :
                inv.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                'bg-red-100 text-red-700'
              }`}>
                {inv.status}
              </span>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

async function RecentLeads({ supabase }: { supabase: Awaited<ReturnType<typeof createClient>> }) {
  const { data: leads } = await supabase
    .from('leads')
    .select('id, name, email, service, created_at')
    .order('created_at', { ascending: false })
    .limit(5)

  return (
    <div className="bg-white rounded-xl border border-slate-200 shadow-sm">
      <div className="px-5 py-4 border-b border-slate-100">
        <h2 className="font-semibold text-slate-800">Recent Leads</h2>
      </div>
      <div className="divide-y divide-slate-50">
        {!leads?.length && (
          <p className="px-5 py-8 text-center text-slate-400 text-sm">No leads yet</p>
        )}
        {leads?.map(lead => (
          <div key={lead.id} className="px-5 py-3 flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-slate-700">{lead.name}</p>
              <p className="text-xs text-slate-400">{lead.email}</p>
            </div>
            <span className="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full font-medium">
              {lead.service ?? 'General'}
            </span>
          </div>
        ))}
      </div>
    </div>
  )
}
