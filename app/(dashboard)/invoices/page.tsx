import { createClient } from '@/lib/supabase/server'
import Header from '@/components/Header'
import Link from 'next/link'
import { Plus } from 'lucide-react'
import UpdateInvoiceStatus from './UpdateInvoiceStatus'

export default async function InvoicesPage() {
  const supabase = await createClient()
  const { data: invoices } = await supabase
    .from('invoices')
    .select('*, clients(name)')
    .order('created_at', { ascending: false })

  const totals = {
    paid: (invoices ?? []).filter(i => i.status === 'paid').reduce((s, i) => s + i.amount, 0),
    pending: (invoices ?? []).filter(i => i.status === 'pending').reduce((s, i) => s + i.amount, 0),
  }

  return (
    <div>
      <Header title="Invoices" />
      <div className="p-6 space-y-6">
        <div className="grid grid-cols-3 gap-4">
          {[
            { label: 'Total Paid', value: totals.paid, color: 'text-green-600', bg: 'bg-green-50 border-green-100' },
            { label: 'Pending', value: totals.pending, color: 'text-amber-600', bg: 'bg-amber-50 border-amber-100' },
            { label: 'Total Invoices', value: invoices?.length ?? 0, color: 'text-slate-800', bg: 'bg-white border-slate-200', raw: true },
          ].map(({ label, value, color, bg, raw }) => (
            <div key={label} className={`${bg} rounded-xl border p-4 shadow-sm`}>
              <p className="text-xs text-slate-500 mb-1">{label}</p>
              <p className={`text-2xl font-bold ${color}`}>
                {raw ? value : `$${Number(value).toLocaleString()}`}
              </p>
            </div>
          ))}
        </div>

        <div className="flex justify-end">
          <Link
            href="/invoices/new"
            className="flex items-center gap-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors"
          >
            <Plus size={16} /> New Invoice
          </Link>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-slate-50 border-b border-slate-200">
              <tr>
                {['Invoice #', 'Client', 'Amount', 'Due Date', 'Status', 'Actions'].map(h => (
                  <th key={h} className="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {!invoices?.length && (
                <tr>
                  <td colSpan={6} className="px-5 py-10 text-center text-slate-400">
                    No invoices yet
                  </td>
                </tr>
              )}
              {invoices?.map(inv => (
                <tr key={inv.id} className="hover:bg-slate-50 transition-colors">
                  <td className="px-5 py-3.5 font-medium text-slate-700">#{inv.invoice_number}</td>
                  <td className="px-5 py-3.5 text-slate-600">{(inv.clients as { name: string } | null)?.name ?? '—'}</td>
                  <td className="px-5 py-3.5 font-semibold text-slate-800">${inv.amount?.toLocaleString()}</td>
                  <td className="px-5 py-3.5 text-slate-500">
                    {inv.due_date ? new Date(inv.due_date).toLocaleDateString() : '—'}
                  </td>
                  <td className="px-5 py-3.5">
                    <span className={`text-xs px-2.5 py-1 rounded-full font-medium ${
                      inv.status === 'paid' ? 'bg-green-100 text-green-700' :
                      inv.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                      'bg-red-100 text-red-700'
                    }`}>
                      {inv.status}
                    </span>
                  </td>
                  <td className="px-5 py-3.5">
                    <UpdateInvoiceStatus invoiceId={inv.id} currentStatus={inv.status} />
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
