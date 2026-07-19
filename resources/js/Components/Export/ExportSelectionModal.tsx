import Modal from '@/Components/Modal'
import { FileDown, X } from 'lucide-react'
import { useState } from 'react'

interface ExportSelectionModalProps {
    show: boolean
    onClose: () => void
    projectId: string
}

interface SectionOption {
    key: string
    label: string
}

const SECTIONS: SectionOption[] = [
    { key: 'generalInfo', label: 'Informations générales' },
    { key: 'requirements', label: 'Cahier des charges (CDC)' },
    { key: 'team', label: 'Équipe' },
    { key: 'technicalChoices', label: 'Choix techniques' },
    { key: 'algorithmDiagrams', label: 'Algorigramme(s)' },
    { key: 'tasks', label: 'Tâches' },
    { key: 'resources', label: 'Ressources (liste)' },
]

function csrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

export default function ExportSelectionModal({ show, onClose, projectId }: ExportSelectionModalProps) {
    const [selected, setSelected] = useState<Record<string, boolean>>(() =>
        Object.fromEntries(SECTIONS.map((section) => [section.key, true])),
    )

    const toggle = (key: string) => setSelected((current) => ({ ...current, [key]: !current[key] }))

    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <form method="POST" action={`/projects/${projectId}/export`} target="_blank" style={{ padding: 24 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 }}>
                    <h2 style={{ margin: 0 }}>Exporter en PDF</h2>
                    <button type="button" onClick={onClose} style={{ border: 0, background: 'none', cursor: 'pointer' }} aria-label="Fermer"><X size={18} /></button>
                </div>
                <p className="rf-field-hint" style={{ marginBottom: 16 }}>Sélectionnez les éléments à inclure dans l'export.</p>

                <input type="hidden" name="_token" value={csrfToken()} />

                <div style={{ display: 'grid', gap: 10 }}>
                    {SECTIONS.map((section) => (
                        <label key={section.key} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13.5, fontWeight: 600, color: 'var(--rf-text-secondary)' }}>
                            <input type="hidden" name={`sections[${section.key}]`} value="0" />
                            <input
                                type="checkbox"
                                className="rf-checkbox"
                                name={`sections[${section.key}]`}
                                value="1"
                                checked={selected[section.key]}
                                onChange={() => toggle(section.key)}
                            />
                            {section.label}
                        </label>
                    ))}
                </div>

                <button type="submit" className="rf-button rf-button--primary" style={{ marginTop: 20 }} onClick={() => setTimeout(onClose, 100)}>
                    <FileDown size={15} />Exporter
                </button>
            </form>
        </Modal>
    )
}
