import { createClient } from '@/lib/supabase/server'
import Header from '@/components/Header'
import Link from 'next/link'
import { Plus, Mail, Phone, ExternalLink } from 'lucide-react'
import AddClientModal from './AddClientModal'

export default async function ClientsPage() {
  const supabase = await createClient()
  const { data: clients } = await supabase
    .from('clients')
    .select('*')
    .order('created_at', { ascending: false })

  return (
    <div>
      <Header title="Clients" />
      <div className="p-6">
        <div className="flex items-center justify-between mb-6">
          <p className="text-slate-500 text-sm">{clients?.length ?? 0} total clients</p>
          <AddClientModal />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          {!clients?.length && (
            <div className="col-span-3 bg-white rounded-xl border border-dashed border-slate-200 p-12 text-center">
              <p className="text-slate-400 text-sm">No clients yet. Add your first client.</p>
            </div>
          )}
          {clients?.map(client => (
            <div key={client.id} className="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition-shadow">
              <div className="flex items-start justify-between mb-3">
                <div className="w-10 h-10 bg-violet-100 rounded-lg flex items-center justify-center text-violet-700 font-bold text-sm">
                  {client.name?.charAt(0).toUpperCase()}
                </div>
                <Link
                  href={`/clients/${client.id}`}
                  className="text-slate-400 hover:text-violet-600 transition-colors"
                >
                  <ExternalLink size={16} />
                </Link>
              </div>

              <h3 className="font-semibold text-slate-800 mb-1">{client.name}</h3>
              {client.company && <p className="text-xs text-slate-400 mb-3">{client.company}</p>}

              <div className="space-y-1.5">
                {client.email && (
                  <div className="flex items-center gap-2 text-xs text-slate-500">
                    <Mail size={13} />
                    {client.email}
                  </div>
                )}
                {client.phone && (
                  <div className="flex items-center gap-2 text-xs text-slate-500">
                    <Phone size={13} />
                    {client.phone}
                  </div>
                )}
              </div>

              <div className="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${
                  client.status === 'active'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-slate-100 text-slate-500'
                }`}>
                  {client.status ?? 'active'}
                </span>
                <Link href={`/clients/${client.id}`} className="text-xs text-violet-600 hover:underline font-medium">
                  View details →
                </Link>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
