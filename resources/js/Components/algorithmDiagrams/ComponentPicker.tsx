import type { AssignableComponent, DiagramNodeType } from '@/types/algorithmDiagrams'
import { NODE_ALLOWED_COMPONENT_TYPES } from '@/types/algorithmDiagrams'

interface ComponentPickerProps {
    nodeType: DiagramNodeType
    availableComponents: AssignableComponent[]
    selectedIds: number[]
    onChange: (ids: number[]) => void
}

export default function ComponentPicker({ nodeType, availableComponents, selectedIds, onChange }: ComponentPickerProps) {
    const allowedTypes = NODE_ALLOWED_COMPONENT_TYPES[nodeType]

    if (allowedTypes !== null && allowedTypes.length === 0) {
        return <p className="rf-field-hint">Ce type de nœud ne peut pas être lié à un composant.</p>
    }

    const options = allowedTypes === null ? availableComponents : availableComponents.filter((c) => allowedTypes.includes(c.category.type))

    const toggle = (id: number) => {
        onChange(selectedIds.includes(id) ? selectedIds.filter((s) => s !== id) : [...selectedIds, id])
    }

    if (options.length === 0) {
        return <p className="rf-field-hint">Aucun composant du type autorisé dans les Choix techniques du projet.</p>
    }

    return (
        <div className="rf-tags">
            {options.map((component) => (
                <span
                    key={component.id}
                    onClick={() => toggle(component.id)}
                    style={{ cursor: 'pointer', background: selectedIds.includes(component.id) ? 'var(--rf-primary)' : undefined, color: selectedIds.includes(component.id) ? '#fff' : undefined }}
                >
                    {component.name}
                </span>
            ))}
        </div>
    )
}
