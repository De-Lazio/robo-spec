import Field from '@/Components/requirements/Field'
import type { RequirementsData } from '@/types/requirements'
import type { ChangeEvent } from 'react'

const PROJECT_TYPES = ['Robot Mobile Autonome', 'Bras Robotisé', 'Drone / UAV', 'Robot Humanoïde', 'Robot Fixe', 'Système Embarqué', 'Autre']

interface StepProps {
    data: RequirementsData['step1']
    onChange: (data: RequirementsData['step1']) => void
}

export default function GeneralInformationStep({ data, onChange }: StepProps) {
    const set = (key: keyof RequirementsData['step1']) => (e: ChangeEvent<HTMLInputElement | HTMLSelectElement>) =>
        onChange({ ...data, [key]: e.target.value })

    return (
        <div className="rf-form">
            <Field label="Nom du projet" required hint="Ex: Robot Transporteur AGV, Bras Robotisé 6-DOF">
                <input className="rf-input" placeholder="Saisissez le nom du projet…" value={data.projectName} onChange={set('projectName')} />
            </Field>
            <div className="rf-form-grid">
                <Field label="Équipe / Groupe" required>
                    <input className="rf-input" placeholder="Ex: Groupe Mécatronique - 3ème année" value={data.team} onChange={set('team')} />
                </Field>
                <Field label="Date de création" required>
                    <input className="rf-input" type="date" value={data.date} onChange={set('date')} />
                </Field>
            </div>
            <div className="rf-form-grid">
                <Field label="Type de projet">
                    <select className="rf-input" value={data.projectType} onChange={set('projectType')}>
                        <option value="">Choisir…</option>
                        {PROJECT_TYPES.map((type) => <option key={type} value={type}>{type}</option>)}
                    </select>
                </Field>
                <Field label="Encadrant / Superviseur">
                    <input className="rf-input" placeholder="Ex: Prof. Kaci Meziane" value={data.supervisor} onChange={set('supervisor')} />
                </Field>
            </div>
        </div>
    )
}
