import type { ReactNode } from 'react'

interface FieldProps {
    label: string
    required?: boolean
    hint?: string
    children: ReactNode
}

export default function Field({ label, required, hint, children }: FieldProps) {
    return (
        <div className="rf-field">
            <span className="rf-label">{label}{required && <span className="rf-required">*</span>}</span>
            {children}
            {hint && <small className="rf-field-hint">{hint}</small>}
        </div>
    )
}
