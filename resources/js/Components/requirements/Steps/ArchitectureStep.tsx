import DynamicTable from '@/Components/requirements/DynamicTable'
import Field from '@/Components/requirements/Field'
import type { RequirementsData } from '@/types/requirements'
import type { CSSProperties } from 'react'

interface StepProps {
    data: RequirementsData['step6']
    onChange: (data: RequirementsData['step6']) => void
    controlUnits: string[]
    energySources: string[]
}

export default function ArchitectureStep({ data, onChange, controlUnits, energySources }: StepProps) {
    const capteurs = data.capteurs.length ? data.capteurs : [{ name: '', role: '' }]
    const actionneurs = data.actionneurs.length ? data.actionneurs : [{ name: '', role: '' }]

    return (
        <div className="rf-subsection-stack">
            <div>
                <div className="rf-section-label">Capteurs</div>
                <Field label="Liste des capteurs" required hint="Précisez le nom et le rôle de chaque capteur">
                    <DynamicTable
                        rows={capteurs}
                        cols={[
                            { key: 'name', label: 'Capteur', placeholder: 'Ex: LIDAR TF-Luna', width: 220 },
                            { key: 'role', label: 'Rôle', placeholder: "Ex: Détection d'obstacles à 360°" },
                        ]}
                        onChange={(rows) => onChange({ ...data, capteurs: rows as RequirementsData['step6']['capteurs'] })}
                        addLabel="Ajouter un capteur"
                    />
                </Field>
            </div>

            <div>
                <div className="rf-section-label">Unité de contrôle</div>
                <div className="rf-choice-grid" style={{ '--rf-choice-cols': 3 } as CSSProperties}>
                    {controlUnits.map((unit) => {
                        const checked = data.controlUnit === unit
                        return (
                            <label key={unit} className={`rf-choice-card ${checked ? 'is-checked' : ''}`}>
                                <span className="rf-choice-mark rf-choice-mark--round" />
                                <input type="radio" name="controlUnit" className="rf-choice-input" checked={checked} onChange={() => onChange({ ...data, controlUnit: unit })} />
                                {unit}
                            </label>
                        )
                    })}
                </div>
                {data.controlUnit === 'Autre' && (
                    <input className="rf-input" style={{ marginTop: 10 }} placeholder="Précisez l'unité de contrôle…" value={data.otherControlUnit} onChange={(e) => onChange({ ...data, otherControlUnit: e.target.value })} />
                )}
            </div>

            <div>
                <div className="rf-section-label">Actionneurs</div>
                <DynamicTable
                    rows={actionneurs}
                    cols={[
                        { key: 'name', label: 'Actionneur', placeholder: 'Ex: Moteur DC 12V', width: 220 },
                        { key: 'role', label: 'Rôle', placeholder: 'Ex: Propulsion du robot' },
                    ]}
                    onChange={(rows) => onChange({ ...data, actionneurs: rows as RequirementsData['step6']['actionneurs'] })}
                    addLabel="Ajouter un actionneur"
                />
            </div>

            <div>
                <div className="rf-section-label">Source d'énergie</div>
                <div className="rf-choice-grid" style={{ '--rf-choice-cols': 2 } as CSSProperties}>
                    {energySources.map((source) => {
                        const checked = data.energySource === source
                        return (
                            <label key={source} className={`rf-choice-card ${checked ? 'is-checked' : ''}`}>
                                <span className="rf-choice-mark rf-choice-mark--round" />
                                <input type="radio" name="energySource" className="rf-choice-input" checked={checked} onChange={() => onChange({ ...data, energySource: source })} />
                                {source}
                            </label>
                        )
                    })}
                </div>
            </div>
        </div>
    )
}
