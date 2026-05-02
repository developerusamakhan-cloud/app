import { createClient } from '@/lib/supabase/server'
import Header from '@/components/Header'
import NewInvoiceForm from './NewInvoiceForm'

export default async function NewInvoicePage({ searchParams }: { searchParams: Promise<{ client?: string }> }) {
  const { client: preselectedClient } = await searchParams
  const supabase = await createClient()
  const { data: clients } = await supabase.from('clients').select('id, name').order('name')

  return (
    <div>
      <Header title="New Invoice" />
      <div className="p-6 max-w-2xl">
        <NewInvoiceForm clients={clients ?? []} preselectedClient={preselectedClient} />
      </div>
    </div>
  )
}
