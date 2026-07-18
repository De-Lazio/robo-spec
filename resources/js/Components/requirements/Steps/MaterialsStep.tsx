import DynamicTable from '@/Components/requirements/DynamicTable'
import type { RequirementsData } from '@/types/requirements'

interface StepProps {
    data: RequirementsData['step10']
    onChange: (data: RequirementsData['step10']) => void
}

export default function MaterialsStep({ data, onChange }: StepProps) {
    const materials = data.materials.length ? data.materials : [
        { component: '', quantity: '', reference: '' },
        { component: '', quantity: '', reference: '' },
    ]

    return (
        <div className="rf-form">
            <div className="rf-hint-banner">
                Listez tous les composants matériels nécessaires à la réalisation du robot. Incluez les références si connues.
            </div>
            <DynamicTable
                rows={materials}
                cols={[
                    { key: 'component', label: 'Composant', placeholder: 'Ex: ESP32 DevKit V1' },
                    { key: 'quantity', label: 'Quantité', placeholder: 'Ex: 2', width: 100, type: 'number' },
                    { key: 'reference', label: 'Référence', placeholder: 'Ex: AZ-ESP32-01', width: 150 },
                ]}
                onChange={(rows) => onChange({ ...data, materials: rows as RequirementsData['step10']['materials'] })}
                addLabel="Ajouter un composant"
            />
        </div>
    )
}
