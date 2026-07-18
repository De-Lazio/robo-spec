import type { RequirementsData } from '@/types/requirements'
import { Check, Plus, Trash2 } from 'lucide-react'

const STATUS_LABELS: Record<string, string> = { pending: 'À faire', 'in-progress': 'En cours', done: 'Terminé' }

const DEFAULT_PLANNING = [
    { stage: 'Analyse du besoin et spécifications', date: '', status: 'pending' },
    { stage: 'Conception mécanique (CAO)', date: '', status: 'pending' },
    { stage: 'Schéma électronique', date: '', status: 'pending' },
    { stage: 'Assemblage du châssis', date: '', status: 'pending' },
    { stage: 'Programmation', date: '', status: 'pending' },
    { stage: 'Tests et validation', date: '', status: 'pending' },
]

interface StepProps {
    data: RequirementsData['step11']
    onChange: (data: RequirementsData['step11']) => void
    planningStatuses: string[]
}

export default function PlanningStep({ data, onChange, planningStatuses }: StepProps) {
    const planning = data.planning.length ? data.planning : DEFAULT_PLANNING

    const updateRow = (index: number, patch: Partial<RequirementsData['step11']['planning'][number]>) => {
        const next = [...planning]
        next[index] = { ...next[index], ...patch }
        onChange({ ...data, planning: next })
    }

    return (
        <div>
            {planning.map((row, i) => (
                <div className="rf-plan-row" key={i}>
                    <div className={`rf-plan-badge ${row.status === 'done' ? 'is-done' : ''}`}>
                        {row.status === 'done' ? <Check size={14} strokeWidth={3} /> : i + 1}
                    </div>
                    <input className="rf-input" style={{ flex: 1 }} placeholder="Nom de l'étape…" value={row.stage} onChange={(e) => updateRow(i, { stage: e.target.value })} />
                    <input className="rf-input" type="date" style={{ width: 160 }} value={row.date} onChange={(e) => updateRow(i, { date: e.target.value })} />
                    <select className="rf-select" style={{ width: 130 }} value={row.status} onChange={(e) => updateRow(i, { status: e.target.value })}>
                        {planningStatuses.map((status) => <option key={status} value={status}>{STATUS_LABELS[status] ?? status}</option>)}
                    </select>
                    <button type="button" className="rf-dyn-remove" onClick={() => onChange({ ...data, planning: planning.filter((_, j) => j !== i) })} disabled={planning.length <= 1} aria-label="Supprimer cette étape">
                        <Trash2 size={13} />
                    </button>
                </div>
            ))}
            <button type="button" className="rf-dyn-add" onClick={() => onChange({ ...data, planning: [...planning, { stage: '', date: '', status: 'pending' }] })}>
                <Plus size={13} /> Ajouter une étape
            </button>
        </div>
    )
}
