import type { RequirementsData } from '@/types/requirements'
import type { ChangeEvent } from 'react'

interface StepProps {
    data: RequirementsData['step9']
    onChange: (data: RequirementsData['step9']) => void
}

const BLOCKS = [
    { key: 'missionLabel', label: 'Mission (entrée)', icon: '🎯', color: '#2563eb', desc: "L'objectif à atteindre" },
    { key: 'perceptionLabel', label: 'Perception (capteurs)', icon: '👁️', color: '#7c3aed', desc: 'Acquisition de données' },
    { key: 'decisionLabel', label: 'Décision (microcontrôleur)', icon: '🧠', color: '#059669', desc: 'Traitement et algorithmes' },
    { key: 'actionLabel', label: 'Action (actionneurs)', icon: '⚙️', color: '#d97706', desc: 'Exécution physique' },
    { key: 'feedbackLabel', label: 'Retour (feedback)', icon: '📡', color: '#dc2626', desc: 'Supervision et monitoring' },
] as const

export default function FunctionalDiagramStep({ data, onChange }: StepProps) {
    const set = (key: keyof RequirementsData['step9']) => (e: ChangeEvent<HTMLInputElement>) =>
        onChange({ ...data, [key]: e.target.value })

    return (
        <div className="rf-subsection-stack">
            <div className="rf-flow-diagram">
                <div className="rf-flow-diagram__title">Aperçu du schéma fonctionnel</div>
                {BLOCKS.map((block, i) => (
                    <div key={block.key} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                        <div className="rf-flow-node" style={{ borderColor: block.color + '40', background: block.color + '10' }}>
                            <div className="rf-flow-node__icon">{block.icon}</div>
                            <div className="rf-flow-node__label" style={{ color: block.color }}>{block.label}</div>
                            <div className="rf-flow-node__desc">{data[block.key] || block.desc}</div>
                        </div>
                        {i < BLOCKS.length - 1 && <div className="rf-flow-connector" />}
                    </div>
                ))}
            </div>

            <div className="rf-form">
                <div className="rf-section-label">Personnalisez chaque bloc</div>
                {BLOCKS.map((block) => (
                    <div key={block.key} style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 10 }}>
                        <div style={{ width: 28, height: 28, borderRadius: 7, background: block.color + '15', display: 'grid', placeItems: 'center', fontSize: 14, flexShrink: 0 }}>{block.icon}</div>
                        <input className="rf-input" placeholder={block.desc} value={data[block.key]} onChange={set(block.key)} />
                    </div>
                ))}
            </div>
        </div>
    )
}
