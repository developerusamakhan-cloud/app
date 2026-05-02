import { createClient } from '@/lib/supabase/server'
import Header from '@/components/Header'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { Mail, Phone, Building2, FileText, ArrowLeft } from 'lucide-react'

export default async function ClientDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const supabase = await createClient()

  const { data: client } = await supabase.from('clients').select('*').eq('id', id).single()
  if (!client) notFound()

  const { data: invoices } = await supabase
    .from('invoices')
    .select('*')
    .eq('client_id', id)
    .order('created_at', { ascending: false })

  const totalPaid = (invoices ?? [])
    .filter(inv => inv.status === 'paid')
    .reduce((sum, inv) => sum + (inv.amount ?? 0), 0)

  return (
    <div>
      <Header title={client.name} />
      <div className="p-6 space-y-6 max-w-4xl">
        <Link href="/clients" className="flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm">
          <ArrowLeft size={16} /> Back to Clients
        </Link>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="md:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div className="flex items-center gap-4 mb-5">
              <div className="w-14 h-14 bg-violet-100 rounded-xl flex items-center justify-center text-violet-700 font-bold text-xl">
                {client.name?.charAt(0).toUpperCase()}
              </div>
              <div>
                <h2 className="text-xl font-semibold text-slate-800">{client.name}</h2>
                {client.company && <p className="text-slate-500 text-sm">{client.company}</p>}
              </div>
            </div>

            <div className="space-y-3">
              {client.email && (
                <div className="flex items-center gap-3 text-sm text-slate-600">
                  <Mail size={16} className="text-slate-400" />
                  <a href={`mailto:${client.email}`} className="hover:text-violet-600">{client.email}</a>
                </div>
              )}
              {client.phone && (
                <div className="flex items-center gap-3 text-sm text-slate-600">
                  <Phone size={16} className="text-slate-400" />
                  {client.phone}
                </div>
              )}
              {client.company && (
                <div className="flex items-center gap-3 text-sm text-slate-600">
                  <Building2 size={16} className="text-slate-400" />
                  {client.company}
                </div>
              )}
            </div>

            {client.notes && (
              <div className="mt-4 pt-4 border-t border-slate-100">
                <p className="text-xs text-slate-400 mb-1">Notes</p>
                <p className="text-sm text-slate-600">{client.notes}</p>
              </div>
            )}
          </div>

          <div className="space-y-4">
            <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
              <p className="text-xs text-slate-400 mb-1">Total Paid</p>
              <p className="text-2xl font-bold text-green-600">${totalPaid.toLocaleString()}</p>
            </div>
            <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
              <p className="text-xs text-slate-400 mb-1">Invoices</p>
              <p className="text-2xl font-bold text-slate-800">{invoices?.length ?? 0}</p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 shadow-sm">
          <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 className="font-semibold text-slate-800 flex items-center gap-2">
              <FileText size={16} /> Invoices
            </h3>
            <Link href={`/invoices/new?client=${id}`} className="text-sm text-violet-600 hover:underline font-medium">
              + New Invoice
            </Link>
          </div>
          <div className="divide-y divide-slate-50">
            {!invoices?.length && (
              <p className="px-5 py-8 text-center text-slate-400 text-sm">No invoices yet</p>
            )}
            {invoices?.map(inv => (
              <div key={inv.id} className="px-5 py-3 flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-slate-700">#{inv.invoice_number}</p>
                  <p className="text-xs text-slate-400">{new Date(inv.created_at).toLocaleDateString()}</p>
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
      </div>
    </div>
  )
}
