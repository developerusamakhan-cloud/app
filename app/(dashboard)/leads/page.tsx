import { createClient } from '@/lib/supabase/server'
import Header from '@/components/Header'
import { Mail, Phone, MessageSquare, Copy } from 'lucide-react'
import LeadActions from './LeadActions'
import CopyEndpoint from './CopyEndpoint'

export default async function LeadsPage() {
  const supabase = await createClient()
  const { data: leads } = await supabase
    .from('leads')
    .select('*')
    .order('created_at', { ascending: false })

  const counts = {
    new: leads?.filter(l => l.status === 'new').length ?? 0,
    contacted: leads?.filter(l => l.status === 'contacted').length ?? 0,
    converted: leads?.filter(l => l.status === 'converted').length ?? 0,
  }

  return (
    <div>
      <Header title="Leads" />
      <div className="p-6 space-y-6">
        <div className="grid grid-cols-3 gap-4">
          {[
            { label: 'New Leads', value: counts.new, color: 'bg-blue-50 border-blue-100 text-blue-700' },
            { label: 'Contacted', value: counts.contacted, color: 'bg-amber-50 border-amber-100 text-amber-700' },
            { label: 'Converted', value: counts.converted, color: 'bg-green-50 border-green-100 text-green-700' },
          ].map(({ label, value, color }) => (
            <div key={label} className={`${color} border rounded-xl p-4 shadow-sm`}>
              <p className="text-xs opacity-70 mb-1">{label}</p>
              <p className="text-2xl font-bold">{value}</p>
            </div>
          ))}
        </div>

        <div className="bg-violet-50 border border-violet-200 rounded-xl p-4">
          <div className="flex items-center justify-between mb-2">
            <p className="text-sm font-semibold text-violet-800">Lead Capture API Endpoint</p>
            <CopyEndpoint />
          </div>
          <code className="text-xs text-violet-700 font-mono">
            POST https://app.vyntic.studio/api/leads
          </code>
          <p className="text-xs text-violet-600 mt-1">
            Add this to any website form to automatically capture leads here.
          </p>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-slate-50 border-b border-slate-200">
              <tr>
                {['Name', 'Email', 'Service', 'Message', 'Status', 'Date', 'Actions'].map(h => (
                  <th key={h} className="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {!leads?.length && (
                <tr>
                  <td colSpan={7} className="px-5 py-10 text-center text-slate-400">
                    No leads yet. Add the API endpoint to your website.
                  </td>
                </tr>
              )}
              {leads?.map(lead => (
                <tr key={lead.id} className="hover:bg-slate-50 transition-colors">
                  <td className="px-5 py-3.5">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 bg-violet-100 rounded-full flex items-center justify-center text-violet-700 text-xs font-bold flex-shrink-0">
                        {lead.name?.charAt(0).toUpperCase()}
                      </div>
                      <span className="font-medium text-slate-700">{lead.name}</span>
                    </div>
                  </td>
                  <td className="px-5 py-3.5">
                    <div className="flex items-center gap-1.5 text-slate-600">
                      <Mail size={13} className="text-slate-400" />
                      <a href={`mailto:${lead.email}`} className="hover:text-violet-600">{lead.email}</a>
                    </div>
                    {lead.phone && (
                      <div className="flex items-center gap-1.5 text-slate-400 text-xs mt-0.5">
                        <Phone size={11} />
                        {lead.phone}
                      </div>
                    )}
                  </td>
                  <td className="px-5 py-3.5">
                    <span className="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full font-medium">
                      {lead.service ?? 'General'}
                    </span>
                  </td>
                  <td className="px-5 py-3.5 max-w-[160px]">
                    {lead.message && (
                      <div className="flex items-start gap-1.5 text-slate-500 text-xs">
                        <MessageSquare size={12} className="mt-0.5 flex-shrink-0" />
                        <span className="truncate">{lead.message}</span>
                      </div>
                    )}
                  </td>
                  <td className="px-5 py-3.5">
                    <span className={`text-xs px-2.5 py-1 rounded-full font-medium ${
                      lead.status === 'new' ? 'bg-blue-100 text-blue-700' :
                      lead.status === 'contacted' ? 'bg-amber-100 text-amber-700' :
                      lead.status === 'converted' ? 'bg-green-100 text-green-700' :
                      'bg-slate-100 text-slate-500'
                    }`}>
                      {lead.status}
                    </span>
                  </td>
                  <td className="px-5 py-3.5 text-slate-500 text-xs">
                    {new Date(lead.created_at).toLocaleDateString()}
                  </td>
                  <td className="px-5 py-3.5">
                    <LeadActions leadId={lead.id} currentStatus={lead.status} />
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
