import DynamicTable from '@/Components/requirements/DynamicTable'
import type { RequirementsData } from '@/types/requirements'

const DEFAULT_CRITERIA = [
    { name: 'Autonomie', value: '' },
    { name: 'Vitesse', value: '' },
    { name: 'Charge utile', value: '' },
    { name: 'Distance de détection', value: '' },
    { name: 'Précision de positionnement', value: '' },
]

interface StepProps {
    data: RequirementsData['step8']
    onChange: (data: RequirementsData['step8']) => void
}

export default function PerformanceStep({ data, onChange }: StepProps) {
    const criteria = data.criteria.length ? data.criteria : DEFAULT_CRITERIA

    return (
        <div className="rf-form">
            <div className="rf-hint-banner">
                <strong>Conseil :</strong> les critères de performance doivent être quantifiables avec des valeurs mesurables (ex: ≥ 4 heures, 0.5 m/s, ± 2 cm).
            </div>
            <DynamicTable
                rows={criteria}
                cols={[
                    { key: 'name', label: 'Critère', placeholder: 'Ex: Autonomie batterie', width: 240 },
                    { key: 'value', label: 'Valeur attendue', placeholder: 'Ex: ≥ 4 heures' },
                ]}
                onChange={(rows) => onChange({ ...data, criteria: rows as RequirementsData['step8']['criteria'] })}
                addLabel="Ajouter un critère"
            />
        </div>
    )
}
