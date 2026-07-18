import Field from '@/Components/requirements/Field'
import type { RequirementsData } from '@/types/requirements'
import type { ChangeEvent } from 'react'

interface StepProps {
    data: RequirementsData['step12']
    onChange: (data: RequirementsData['step12']) => void
}

export default function ExpectedResultStep({ data, onChange }: StepProps) {
    const set = (key: keyof RequirementsData['step12']) => (e: ChangeEvent<HTMLTextAreaElement>) =>
        onChange({ ...data, [key]: e.target.value })

    return (
        <div className="rf-form">
            <div className="rf-hint-banner rf-hint-banner--success">
                <strong>Exemple :</strong> « Le robot doit détecter un obstacle à moins de 20 cm et changer de direction en moins de 2 secondes. »
            </div>
            <Field label="Résultat attendu" required hint="Décrivez précisément ce qui permettra de considérer le projet comme réussi">
                <textarea className="rf-input" rows={4} placeholder="Décrivez en détail le résultat attendu avec des valeurs mesurables…" value={data.expectedResult} onChange={set('expectedResult')} />
            </Field>
            <Field label="Critères de succès" required hint="Quelles conditions doivent être remplies pour valider le projet ?">
                <textarea className="rf-input" rows={3} placeholder="Ex: 5 trajets consécutifs réussis sans intervention humaine, batterie ≥ 4h…" value={data.successCriteria} onChange={set('successCriteria')} />
            </Field>
            <Field label="Méthode de test et validation">
                <textarea className="rf-input" rows={3} placeholder="Ex: Tests en conditions réelles dans le hall technique, avec 3 évaluateurs…" value={data.testMethod} onChange={set('testMethod')} />
            </Field>
        </div>
    )
}
