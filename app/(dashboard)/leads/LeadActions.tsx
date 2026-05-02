'use client'
import { useRouter } from 'next/navigation'
import { createClient } from '@/lib/supabase/client'

export default function LeadActions({ leadId, currentStatus }: { leadId: string; currentStatus: string }) {
  const router = useRouter()

  async function update(status: string) {
    const supabase = createClient()
    await supabase.from('leads').update({ status }).eq('id', leadId)
    router.refresh()
  }

  return (
    <select
      value={currentStatus}
      onChange={e => update(e.target.value)}
      className="text-xs border border-slate-200 rounded-lg px-2 py-1.5 text-slate-600 focus:border-violet-500 focus:outline-none bg-white"
    >
      <option value="new">New</option>
      <option value="contacted">Contacted</option>
      <option value="converted">Converted</option>
      <option value="lost">Lost</option>
    </select>
  )
}
