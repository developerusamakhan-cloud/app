'use client'
import { Bell } from 'lucide-react'

export default function Header({ title }: { title: string }) {
  return (
    <header className="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-10">
      <h1 className="text-xl font-semibold text-slate-800">{title}</h1>
      <div className="flex items-center gap-3">
        <button className="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors relative">
          <Bell size={18} />
        </button>
        <div className="w-8 h-8 bg-violet-100 rounded-full flex items-center justify-center">
          <span className="text-violet-700 text-sm font-bold">A</span>
        </div>
      </div>
    </header>
  )
}
