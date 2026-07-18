import { Plus, Trash2 } from 'lucide-react'

export interface DynamicTableColumn {
    key: string
    label: string
    placeholder?: string
    width?: number
    type?: 'text' | 'number' | 'date' | 'select'
    options?: string[]
    editable?: boolean
}

interface DynamicTableProps {
    rows: Array<Record<string, string>>
    cols: DynamicTableColumn[]
    onChange: (rows: Array<Record<string, string>>) => void
    addLabel?: string
    makeRow?: (rows: Array<Record<string, string>>) => Record<string, string>
}

export default function DynamicTable({ rows, cols, onChange, addLabel = 'Ajouter une ligne', makeRow }: DynamicTableProps) {
    const updateCell = (ri: number, key: string, value: string) => {
        const next = [...rows]
        next[ri] = { ...next[ri], [key]: value }
        onChange(next)
    }

    const addRow = () => {
        const empty = makeRow ? makeRow(rows) : Object.fromEntries(cols.map((c) => [c.key, '']))
        onChange([...rows, empty])
    }

    return (
        <div>
            <table className="rf-dyn-table">
                <thead>
                    <tr>
                        {cols.map((c) => (
                            <th key={c.key} style={{ width: c.width }}>{c.label}</th>
                        ))}
                        <th style={{ width: 40 }} />
                    </tr>
                </thead>
                <tbody>
                    {rows.map((row, ri) => (
                        <tr key={ri}>
                            {cols.map((c) => (
                                <td key={c.key}>
                                    {c.editable === false ? (
                                        <span className="rf-dyn-static">{row[c.key]}</span>
                                    ) : c.type === 'select' ? (
                                        <select className="rf-select" value={row[c.key] || ''} onChange={(e) => updateCell(ri, c.key, e.target.value)}>
                                            {(c.options ?? []).map((o) => <option key={o} value={o}>{o}</option>)}
                                        </select>
                                    ) : (
                                        <input
                                            className="rf-input"
                                            type={c.type ?? 'text'}
                                            placeholder={c.placeholder}
                                            value={row[c.key] || ''}
                                            onChange={(e) => updateCell(ri, c.key, e.target.value)}
                                        />
                                    )}
                                </td>
                            ))}
                            <td>
                                <button type="button" className="rf-dyn-remove" onClick={() => onChange(rows.filter((_, j) => j !== ri))} disabled={rows.length <= 1} aria-label="Supprimer cette ligne">
                                    <Trash2 size={13} />
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
            <button type="button" className="rf-dyn-add" onClick={addRow}>
                <Plus size={13} /> {addLabel}
            </button>
        </div>
    )
}
