import DynamicList from '@/Components/requirements/DynamicList'
import Field from '@/Components/requirements/Field'
import type { RequirementsData } from '@/types/requirements'

interface StepProps {
    data: RequirementsData['step3']
    onChange: (data: RequirementsData['step3']) => void
}

export default function MissionStep({ data, onChange }: StepProps) {
    return (
        <div className="rf-form">
            <div className="rf-hint-banner">
                <strong>Format :</strong> « Le robot doit être capable de… » — chaque point doit être mesurable et précis.
            </div>
            <Field label="Missions du robot" required hint="Listez toutes les missions que le robot doit accomplir">
                <DynamicList
                    items={data.missions.length ? data.missions : ['']}
                    onChange={(missions) => onChange({ ...data, missions })}
                    placeholder="Ex: Transporter des colis de 0 à 25 kg entre les zones définies"
                    addLabel="Ajouter une mission"
                />
            </Field>
        </div>
    )
}
