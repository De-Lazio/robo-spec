import type { DiagramNodeType } from '@/types/algorithmDiagrams'
import { NODE_TYPE_LABELS } from '@/types/algorithmDiagrams'
import { Download, Redo2, Save, Settings, Undo2 } from 'lucide-react'

interface ToolbarProps {
    nodeTypes: DiagramNodeType[]
    pendingType: DiagramNodeType | null
    onSelectType: (type: DiagramNodeType | null) => void
    onUndo: () => void
    onRedo: () => void
    canUndo: boolean
    canRedo: boolean
    onSave: () => void
    saving: boolean
    onExport: () => void
    exporting: boolean
    onOpenSettings: () => void
    canManage: boolean
}

export default function Toolbar({ nodeTypes, pendingType, onSelectType, onUndo, onRedo, canUndo, canRedo, onSave, saving, onExport, exporting, onOpenSettings, canManage }: ToolbarProps) {
    return (
        <div className="rf-toolbar-row">
            <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={onOpenSettings} aria-label="Paramètres du diagramme"><Settings size={14} />Paramètres</button>
            <span className="rf-toolbar-sep" />
            {canManage && (
                <>
                    {nodeTypes.map((type) => (
                        <button
                            key={type}
                            type="button"
                            className={`rf-filter-chip ${pendingType === type ? 'is-active' : ''}`}
                            onClick={() => onSelectType(pendingType === type ? null : type)}
                        >
                            {NODE_TYPE_LABELS[type]}
                        </button>
                    ))}
                    {pendingType && <span className="rf-field-hint" style={{ whiteSpace: 'nowrap' }}>Cliquez sur le canevas pour poser le nœud.</span>}
                    <span className="rf-toolbar-sep" />
                    <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={onUndo} disabled={!canUndo} aria-label="Annuler"><Undo2 size={14} /></button>
                    <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={onRedo} disabled={!canRedo} aria-label="Rétablir"><Redo2 size={14} /></button>
                </>
            )}
            <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={onExport} disabled={exporting}>
                <Download size={14} />{exporting ? 'Export…' : 'Exporter en PNG'}
            </button>
            {canManage && (
                <button type="button" className="rf-button rf-button--primary rf-button--small" onClick={onSave} disabled={saving}>
                    <Save size={14} />{saving ? 'Enregistrement…' : 'Enregistrer'}
                </button>
            )}
        </div>
    )
}
