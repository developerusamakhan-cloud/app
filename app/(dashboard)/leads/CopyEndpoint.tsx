'use client'
import { useState } from 'react'
import { Copy, Check } from 'lucide-react'

export default function CopyEndpoint() {
  const [copied, setCopied] = useState(false)

  function copy() {
    navigator.clipboard.writeText('https://app.vyntic.studio/api/leads')
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }

  return (
    <button
      onClick={copy}
      className="flex items-center gap-1.5 text-xs text-violet-700 hover:text-violet-900 font-medium"
    >
      {copied ? <Check size={13} /> : <Copy size={13} />}
      {copied ? 'Copied!' : 'Copy URL'}
    </button>
  )
}
