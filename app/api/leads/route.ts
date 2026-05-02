import { createClient } from '@/lib/supabase/server'
import { NextRequest, NextResponse } from 'next/server'

export async function POST(req: NextRequest) {
  try {
    const body = await req.json()
    const { name, email, phone, service, message } = body

    if (!name || !email) {
      return NextResponse.json({ error: 'Name and email are required' }, { status: 400 })
    }

    const supabase = await createClient()
    const { error } = await supabase.from('leads').insert([{
      name,
      email,
      phone: phone ?? null,
      service: service ?? null,
      message: message ?? null,
      status: 'new',
    }])

    if (error) throw error

    return NextResponse.json({ success: true }, { status: 201 })
  } catch {
    return NextResponse.json({ error: 'Failed to save lead' }, { status: 500 })
  }
}

export async function GET() {
  return NextResponse.json({ message: 'Send POST request to capture leads' })
}
