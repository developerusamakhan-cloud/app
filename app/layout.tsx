import type { Metadata } from 'next'
import './globals.css'

export const metadata: Metadata = {
  title: 'Vyntic — Client Portal',
  description: 'Manage clients, invoices, and leads',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className="h-full">
      <body className="h-full">{children}</body>
    </html>
  )
}
