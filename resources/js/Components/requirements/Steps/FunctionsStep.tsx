import DynamicTable from '@/Components/requirements/DynamicTable'
import Field from '@/Components/requirements/Field'
import type { RequirementsData } from '@/types/requirements'

interface StepProps {
    data: RequirementsData['step5']
    onChange: (data: RequirementsData['step5']) => void
}

export default function FunctionsStep({ data, onChange }: StepProps) {
    const rows = data.functions.length ? data.functions : [
        { id: 'F1', name: '' }, { id: 'F2', name: '' }, { id: 'F3', name: '' }, { id: 'F4', name: '' },
    ]

    return (
        <div className="rf-form">
            <div className="rf-hint-banner rf-hint-banner--warning">
                <strong>F1, F2…</strong> = fonctions principales que le robot doit absolument réaliser pour accomplir sa mission.
            </div>
            <Field label="Fonctions principales" required hint="Listez toutes les fonctions essentielles du robot (minimum 4)">
                <DynamicTable
                    rows={rows}
                    cols={[
                        { key: 'id', label: 'N°', width: 60, editable: false },
                        { key: 'name', label: 'Fonction', placeholder: 'Ex: Détecter les obstacles' },
                    ]}
                    onChange={(functions) => onChange({ ...data, functions: functions as RequirementsData['step5']['functions'] })}
                    addLabel="Ajouter une fonction"
                    makeRow={(current) => ({ id: `F${current.length + 1}`, name: '' })}
                />
            </Field>
        </div>
    )
}
