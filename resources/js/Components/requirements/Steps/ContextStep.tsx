import Field from '@/Components/requirements/Field'
import type { RequirementsData } from '@/types/requirements'
import type { ChangeEvent } from 'react'

interface StepProps {
    data: RequirementsData['step2']
    onChange: (data: RequirementsData['step2']) => void
}

export default function ContextStep({ data, onChange }: StepProps) {
    const set = (key: keyof RequirementsData['step2']) => (e: ChangeEvent<HTMLTextAreaElement>) =>
        onChange({ ...data, [key]: e.target.value })

    return (
        <div className="rf-form">
            <div className="rf-hint-banner">
                <strong>Guide :</strong> décrivez le problème concret que votre robot va résoudre. Soyez précis sur le contexte d’utilisation.
            </div>
            <Field label="Contexte général" required hint="Décrivez le secteur et le cadre dans lequel s'inscrit le projet">
                <textarea className="rf-input" rows={4} placeholder="Ex: Dans un entrepôt de distribution, le transport manuel des colis est lent et source d'erreurs fréquentes…" value={data.context} onChange={set('context')} />
            </Field>
            <Field label="Problème à résoudre" required hint="Formulez la problématique principale de manière précise">
                <textarea className="rf-input" rows={3} placeholder="Ex: Comment automatiser le transport de colis entre les zones de stockage et d'expédition ?" value={data.problem} onChange={set('problem')} />
            </Field>
            <Field label="Pourquoi un robot ?" required hint="Justifiez le choix d'une solution robotique par rapport à d'autres approches">
                <textarea className="rf-input" rows={3} placeholder="Ex: Un robot permettra de réduire le temps de traitement de 60% tout en éliminant les risques d'accidents…" value={data.whyRobot} onChange={set('whyRobot')} />
            </Field>
        </div>
    )
}
