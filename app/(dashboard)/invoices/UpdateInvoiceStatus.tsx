'use client'
import { useRouter } from 'next/navigation'
import { createClient } from '@/lib/supabase/client'

export default function UpdateInvoiceStatus({ invoiceId, currentStatus }: { invoiceId: string; currentStatus: string }) {
  const router = useRouter()

  async function update(status: string) {
    const supabase = createClient()
    await supabase.from('invoices').update({ status }).eq('id', invoiceId)
    router.refresh()
  }

  return (
    <select
      value={currentStatus}
      onChange={e => update(e.target.value)}
      className="text-xs border border-slate-200 rounded-lg px-2 py-1.5 text-slate-600 focus:border-violet-500 focus:outline-none bg-white"
    >
      <option value="pending">Pending</option>
      <option value="paid">Paid</option>
      <option value="overdue">Overdue</option>
      <option value="cancelled">Cancelled</option>
    </select>
  )
}
