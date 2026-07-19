import Field from '@/Components/requirements/Field'
import type { RequirementsData } from '@/types/requirements'
import { Check } from 'lucide-react'
import type { CSSProperties } from 'react'

interface StepProps {
    data: RequirementsData['step4']
    onChange: (data: RequirementsData['step4']) => void
    userOptions: string[]
}

export default function UsersStep({ data, onChange, userOptions }: StepProps) {
    const toggle = (option: string) => {
        const users = data.users.includes(option) ? data.users.filter((u) => u !== option) : [...data.users, option]
        onChange({ ...data, users })
    }

    return (
        <div className="rf-form">
            <Field label="Qui utilisera le robot ?" required hint="Sélectionnez toutes les catégories d'utilisateurs concernées">
                <div className="rf-choice-grid" style={{ '--rf-choice-cols': 3 } as CSSProperties}>
                    {userOptions.map((option) => {
                        const checked = data.users.includes(option)
                        return (
                            <label key={option} className={`rf-choice-card ${checked ? 'is-checked' : ''}`}>
                                <span className="rf-choice-mark">{checked && <Check size={12} strokeWidth={3} />}</span>
                                <input type="checkbox" className="rf-choice-input" checked={checked} onChange={() => toggle(option)} />
                                {option}
                            </label>
                        )
                    })}
                </div>
            </Field>
            <Field label="Autres utilisateurs">
                <input className="rf-input" placeholder="Précisez d'autres types d'utilisateurs…" value={data.otherUsers} onChange={(e) => onChange({ ...data, otherUsers: e.target.value })} />
            </Field>
        </div>
    )
}
