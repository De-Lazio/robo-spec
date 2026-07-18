import { Plus, Trash2 } from 'lucide-react'

interface DynamicListProps {
    items: string[]
    onChange: (items: string[]) => void
    placeholder?: string
    addLabel?: string
}

export default function DynamicList({ items, onChange, placeholder = 'Saisir…', addLabel = 'Ajouter une ligne' }: DynamicListProps) {
    return (
        <div className="rf-dyn-list">
            {items.map((item, i) => (
                <div className="rf-dyn-row" key={i}>
                    <input
                        className="rf-input"
                        placeholder={placeholder}
                        value={item}
                        onChange={(e) => {
                            const next = [...items]
                            next[i] = e.target.value
                            onChange(next)
                        }}
                    />
                    <button type="button" className="rf-dyn-remove" onClick={() => onChange(items.filter((_, j) => j !== i))} disabled={items.length <= 1} aria-label="Supprimer cette ligne">
                        <Trash2 size={14} />
                    </button>
                </div>
            ))}
            <button type="button" className="rf-dyn-add" onClick={() => onChange([...items, ''])}>
                <Plus size={13} /> {addLabel}
            </button>
        </div>
    )
}
