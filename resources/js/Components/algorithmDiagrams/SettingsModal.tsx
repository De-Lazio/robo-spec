import Modal from '@/Components/Modal'
import type { AssignableComponent, DiagramVariable } from '@/types/algorithmDiagrams'
import type { ResourceCategory } from '@/types/projects'
import { categoryLabels } from '@/types/projects'
import { Plus, X } from 'lucide-react'

interface SettingsModalProps {
    show: boolean
    onClose: () => void
    name: string
    onNameChange: (value: string) => void
    microcontrollerId: number | null
    onMicrocontrollerChange: (id: number | null) => void
    microcontrollerOptions: AssignableComponent[]
    energySourceId: number | null
    onEnergySourceChange: (id: number | null) => void
    energySourceOptions: AssignableComponent[]
    variables: DiagramVariable[]
    onAddVariable: () => void
    onRemoveVariable: (id: string) => void
    newVariableName: string
    onNewVariableNameChange: (value: string) => void
    exportCategory: ResourceCategory
    onExportCategoryChange: (value: ResourceCategory) => void
    edgeStrokeWidth: number
    onEdgeStrokeWidthChange: (value: number) => void
    canManage: boolean
}

export default function SettingsModal({
    show,
    onClose,
    name,
    onNameChange,
    microcontrollerId,
    onMicrocontrollerChange,
    microcontrollerOptions,
    energySourceId,
    onEnergySourceChange,
    energySourceOptions,
    variables,
    onAddVariable,
    onRemoveVariable,
    newVariableName,
    onNewVariableNameChange,
    exportCategory,
    onExportCategoryChange,
    edgeStrokeWidth,
    onEdgeStrokeWidthChange,
    canManage,
}: SettingsModalProps) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <div className="rf-form" style={{ padding: 24 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <h2 style={{ margin: 0 }}>Paramètres du diagramme</h2>
                    <button type="button" onClick={onClose} style={{ border: 0, background: 'none', cursor: 'pointer' }} aria-label="Fermer"><X size={18} /></button>
                </div>

                <label>Nom<input className="rf-input" value={name} onChange={(e) => onNameChange(e.target.value)} disabled={!canManage} /></label>

                <div className="rf-form-grid">
                    <label>
                        Microcontrôleur
                        <select className="rf-select" value={microcontrollerId ?? ''} onChange={(e) => onMicrocontrollerChange(e.target.value ? Number(e.target.value) : null)} disabled={!canManage}>
                            <option value="">—</option>
                            {microcontrollerOptions.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                    </label>
                    <label>
                        Source d'énergie
                        <select className="rf-select" value={energySourceId ?? ''} onChange={(e) => onEnergySourceChange(e.target.value ? Number(e.target.value) : null)} disabled={!canManage}>
                            <option value="">—</option>
                            {energySourceOptions.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                    </label>
                </div>

                <div>
                    <div className="rf-label">Variables</div>
                    <div className="rf-tags" style={{ marginBottom: canManage ? 8 : 0 }}>
                        {variables.map((v) => (
                            <span key={v.id}>{v.name}{canManage && <button type="button" onClick={() => onRemoveVariable(v.id)} style={{ border: 0, background: 'none', marginLeft: 4, cursor: 'pointer' }}><X size={10} /></button>}</span>
                        ))}
                        {variables.length === 0 && <span className="rf-field-hint">Aucune variable.</span>}
                    </div>
                    {canManage && (
                        <div style={{ display: 'flex', gap: 6 }}>
                            <input className="rf-input" value={newVariableName} onChange={(e) => onNewVariableNameChange(e.target.value)} placeholder="nom_variable" />
                            <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={onAddVariable}><Plus size={13} />Ajouter</button>
                        </div>
                    )}
                </div>

                <div className="rf-form-grid">
                    <label>
                        Catégorie d'export
                        <select className="rf-select" value={exportCategory} onChange={(e) => onExportCategoryChange(e.target.value as ResourceCategory)}>
                            {Object.entries(categoryLabels).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                        </select>
                    </label>
                    <label>
                        Épaisseur des liaisons
                        <input
                            className="rf-input"
                            type="number"
                            min={1}
                            max={8}
                            value={edgeStrokeWidth}
                            onChange={(e) => onEdgeStrokeWidthChange(Math.min(8, Math.max(1, Number(e.target.value) || 1)))}
                            disabled={!canManage}
                        />
                    </label>
                </div>
            </div>
        </Modal>
    )
}
