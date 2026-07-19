import Field from '@/Components/requirements/Field'
import type { RequirementsData } from '@/types/requirements'
import { Check } from 'lucide-react'
import type { ChangeEvent, CSSProperties } from 'react'

interface StepProps {
    data: RequirementsData['step6']
    onChange: (data: RequirementsData['step6']) => void
    safetyConstraints: string[]
}

export default function ConstraintsStep({ data, onChange, safetyConstraints }: StepProps) {
    const set = (key: keyof RequirementsData['step6']) => (e: ChangeEvent<HTMLInputElement>) =>
        onChange({ ...data, [key]: e.target.value })

    const toggleSafety = (constraint: string) => {
        const next = data.safetyConstraints.includes(constraint)
            ? data.safetyConstraints.filter((c) => c !== constraint)
            : [...data.safetyConstraints, constraint]
        onChange({ ...data, safetyConstraints: next })
    }

    return (
        <div className="rf-subsection-stack">
            <div>
                <div className="rf-section-label">Contraintes techniques</div>
                <div className="rf-form-grid">
                    <Field label="Taille maximale (L × l × H)"><input className="rf-input" placeholder="Ex: 60 × 40 × 35 cm" value={data.maxSize} onChange={set('maxSize')} /></Field>
                    <Field label="Poids maximal"><input className="rf-input" placeholder="Ex: 10 kg" value={data.maxWeight} onChange={set('maxWeight')} /></Field>
                    <Field label="Autonomie minimale"><input className="rf-input" placeholder="Ex: 4 heures" value={data.minAutonomy} onChange={set('minAutonomy')} /></Field>
                    <Field label="Vitesse minimale"><input className="rf-input" placeholder="Ex: 0.5 m/s" value={data.minSpeed} onChange={set('minSpeed')} /></Field>
                </div>
            </div>

            <div>
                <div className="rf-section-label">Contraintes économiques</div>
                <div className="rf-form-grid">
                    <Field label="Budget maximal" hint="Budget total alloué"><input className="rf-input" placeholder="Ex: 35 000 DA" value={data.maxBudget} onChange={set('maxBudget')} /></Field>
                    <Field label="Coût estimé" hint="Estimation actuelle"><input className="rf-input" placeholder="Ex: 28 500 DA" value={data.estimatedCost} onChange={set('estimatedCost')} /></Field>
                </div>
            </div>

            <div>
                <div className="rf-section-label">Contraintes de sécurité</div>
                <div className="rf-choice-grid" style={{ '--rf-choice-cols': 2 } as CSSProperties}>
                    {safetyConstraints.map((constraint) => {
                        const checked = data.safetyConstraints.includes(constraint)
                        return (
                            <label key={constraint} className={`rf-choice-card is-success ${checked ? 'is-checked' : ''}`}>
                                <span className="rf-choice-mark">{checked && <Check size={11} strokeWidth={3} />}</span>
                                <input type="checkbox" className="rf-choice-input" checked={checked} onChange={() => toggleSafety(constraint)} />
                                {constraint}
                            </label>
                        )
                    })}
                </div>
            </div>

            <div>
                <div className="rf-section-label">Contraintes environnementales</div>
                <div className="rf-form-grid">
                    <Field label="Température de fonctionnement"><input className="rf-input" placeholder="Ex: 10°C à 40°C" value={data.temperature} onChange={set('temperature')} /></Field>
                    <Field label="Conditions d'utilisation"><input className="rf-input" placeholder="Ex: Sol plat intérieur, éclairage normal…" value={data.usageConditions} onChange={set('usageConditions')} /></Field>
                </div>
            </div>
        </div>
    )
}
