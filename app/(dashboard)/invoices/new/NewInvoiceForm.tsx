'use client'
import { useState } from 'react'
import { createClient } from '@/lib/supabase/client'
import { useRouter } from 'next/navigation'
import { Loader2, Plus, Trash2, ArrowLeft } from 'lucide-react'
import Link from 'next/link'

type LineItem = { description: string; qty: number; rate: number }
type Client = { id: string; name: string }

export default function NewInvoiceForm({ clients, preselectedClient }: { clients: Client[]; preselectedClient?: string }) {
  const router = useRouter()
  const [loading, setLoading] = useState(false)
  const [clientId, setClientId] = useState(preselectedClient ?? '')
  const [dueDate, setDueDate] = useState('')
  const [notes, setNotes] = useState('')
  const [items, setItems] = useState<LineItem[]>([{ description: '', qty: 1, rate: 0 }])

  function addItem() { setItems(prev => [...prev, { description: '', qty: 1, rate: 0 }]) }
  function removeItem(i: number) { setItems(prev => prev.filter((_, idx) => idx !== i)) }
  function updateItem(i: number, field: keyof LineItem, value: string | number) {
    setItems(prev => prev.map((item, idx) => idx === i ? { ...item, [field]: value } : item))
  }

  const total = items.reduce((sum, item) => sum + item.qty * item.rate, 0)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!clientId) return
    setLoading(true)
    const supabase = createClient()
    const invoiceNumber = `INV-${Date.now().toString().slice(-6)}`
    await supabase.from('invoices').insert([{
      client_id: clientId,
      invoice_number: invoiceNumber,
      amount: total,
      due_date: dueDate || null,
      notes,
      items,
      status: 'pending',
    }])
    setLoading(false)
    router.push('/invoices')
    router.refresh()
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-5">
      <Link href="/invoices" className="flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm">
        <ArrowLeft size={16} /> Back to Invoices
      </Link>

      <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
        <h2 className="font-semibold text-slate-800">Invoice Details</h2>

        <div className="grid grid-cols-2 gap-4">
          <div className="col-span-2">
            <label className="block text-xs text-slate-500 mb-1.5">Client *</label>
            <select
              required
              value={clientId}
              onChange={e => setClientId(e.target.value)}
              className="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none"
            >
              <option value="">Select a client...</option>
              {clients.map(c => (
                <option key={c.id} value={c.id}>{c.name}</option>
              ))}
            </select>
          </div>
          <div>
            <label className="block text-xs text-slate-500 mb-1.5">Due Date</label>
            <input
              type="date"
              value={dueDate}
              onChange={e => setDueDate(e.target.value)}
              className="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none"
            />
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
        <h2 className="font-semibold text-slate-800">Line Items</h2>

        <div className="space-y-3">
          {items.map((item, i) => (
            <div key={i} className="grid grid-cols-12 gap-2 items-center">
              <input
                required
                placeholder="Description"
                value={item.description}
                onChange={e => updateItem(i, 'description', e.target.value)}
                className="col-span-6 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-violet-500 focus:outline-none"
              />
              <input
                type="number"
                min="1"
                value={item.qty}
                onChange={e => updateItem(i, 'qty', Number(e.target.value))}
                placeholder="Qty"
                className="col-span-2 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-violet-500 focus:outline-none"
              />
              <input
                type="number"
                min="0"
                step="0.01"
                value={item.rate}
                onChange={e => updateItem(i, 'rate', Number(e.target.value))}
                placeholder="Rate"
                className="col-span-3 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-violet-500 focus:outline-none"
              />
              <button
                type="button"
                onClick={() => removeItem(i)}
                disabled={items.length === 1}
                className="col-span-1 text-slate-300 hover:text-red-500 disabled:opacity-30 flex items-center justify-center"
              >
                <Trash2 size={15} />
              </button>
            </div>
          ))}
        </div>

        <button
          type="button"
          onClick={addItem}
          className="flex items-center gap-2 text-sm text-violet-600 hover:text-violet-700 font-medium"
        >
          <Plus size={15} /> Add Line Item
        </button>

        <div className="border-t border-slate-100 pt-4 text-right">
          <span className="text-slate-500 text-sm mr-4">Total</span>
          <span className="text-2xl font-bold text-slate-800">${total.toLocaleString()}</span>
        </div>
      </div>

      <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <label className="block text-xs text-slate-500 mb-1.5">Notes</label>
        <textarea
          value={notes}
          onChange={e => setNotes(e.target.value)}
          rows={3}
          placeholder="Payment terms, thank you message, etc."
          className="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none resize-none"
        />
      </div>

      <button
        type="submit"
        disabled={loading}
        className="w-full bg-violet-600 hover:bg-violet-500 text-white py-3 rounded-lg text-sm font-medium transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
      >
        {loading && <Loader2 size={16} className="animate-spin" />}
        Create Invoice
      </button>
    </form>
  )
}
