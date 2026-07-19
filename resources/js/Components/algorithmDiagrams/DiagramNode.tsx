import type { AssignableComponent, DiagramNodeType } from '@/types/algorithmDiagrams'
import { Handle, Position } from '@xyflow/react'
import { MessageSquare } from 'lucide-react'
import type { CSSProperties } from 'react'

interface DiagramNodeProps {
    type: DiagramNodeType
    data: {
        label: string
        componentIds?: number[]
        duration?: string
        variableId?: string | null
        expression?: string
        comment?: string | null
    }
    selected?: boolean
    availableComponents: AssignableComponent[]
}

type BoxNodeType = Exclude<DiagramNodeType, 'decision' | 'transition' | 'or_divergence' | 'or_convergence' | 'and_divergence' | 'and_convergence'>

const SHAPE_STYLE: Record<BoxNodeType, CSSProperties> = {
    start: { borderRadius: 999, background: '#ecfdf5', borderColor: '#059669' },
    end: { borderRadius: 999, background: '#fef2f2', borderColor: '#dc2626' },
    process: { borderRadius: 8, background: '#fff' },
    io: { clipPath: 'polygon(14% 0%, 100% 0%, 86% 100%, 0% 100%)', background: '#eff6ff', paddingLeft: 24, paddingRight: 24 },
    wait: { borderRadius: 8, background: '#f5f3ff' },
    calc: { borderRadius: 8, background: '#f0fdfa' },
    communication: { borderRadius: 8, background: '#fdf4ff' },
    comment: { borderRadius: 6, background: '#fffbeb', borderStyle: 'dashed' },
    step: { borderRadius: 4, background: '#eef2ff' },
    initial_step: { borderRadius: 4, background: '#eef2ff', borderStyle: 'double', borderWidth: 6 },
}

const DECISION_BG = '#fde68a'
const DECISION_BORDER = '#d97706'
const BAR_COLOR = '#334155'

const DIVERGENCE_TYPES: DiagramNodeType[] = ['or_divergence', 'or_convergence', 'and_divergence', 'and_convergence']

function componentNames(ids: number[] | undefined, availableComponents: AssignableComponent[]): string | undefined {
    if (!ids?.length) return undefined
    const names = ids.map((id) => availableComponents.find((c) => c.id === id)?.name).filter((name): name is string => Boolean(name))
    if (names.length === 0) return undefined
    const shown = names.slice(0, 2).join(', ')
    return names.length > 2 ? `${shown} +${names.length - 2}` : shown
}

export default function DiagramNode({ type, data, selected, availableComponents }: DiagramNodeProps) {
    const hasHandles = type !== 'comment'
    const subtitle =
        type === 'wait' || type === 'transition' ? data.duration
            : type === 'calc' ? data.expression
                : ['process', 'decision', 'io', 'communication', 'step', 'initial_step', 'transition'].includes(type) ? componentNames(data.componentIds, availableComponents)
                    : undefined

    const commentBadge = data.comment && (
        <span title={data.comment} style={{ position: 'absolute', top: -6, right: -6, background: '#fff', borderRadius: '50%', border: '1px solid var(--rf-border)', width: 18, height: 18, display: 'grid', placeItems: 'center', zIndex: 2 }}>
            <MessageSquare size={10} color="var(--rf-primary)" />
        </span>
    )

    if (type === 'decision') {
        return (
            <div style={{ position: 'relative', width: 140, height: 100, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                {hasHandles && <Handle type="target" position={Position.Top} />}
                {commentBadge}
                <div
                    style={{
                        position: 'absolute',
                        width: '64%',
                        height: '64%',
                        background: DECISION_BG,
                        border: `1.5px solid ${selected ? 'var(--rf-primary)' : DECISION_BORDER}`,
                        boxShadow: selected ? '0 0 0 3px rgba(37,99,235,.15)' : '0 1px 2px rgba(15,23,42,.06)',
                        transform: 'rotate(45deg)',
                    }}
                />
                <div style={{ position: 'relative', textAlign: 'center', padding: '0 12px', fontSize: 12.5 }}>
                    <strong style={{ display: 'block' }}>{data.label || '(sans nom)'}</strong>
                    {subtitle && <span style={{ display: 'block', color: 'var(--rf-text-secondary)', fontSize: 11, marginTop: 2 }}>{subtitle}</span>}
                </div>
                {hasHandles && <Handle type="source" position={Position.Bottom} />}
            </div>
        )
    }

    if (type === 'transition') {
        return (
            <div style={{ position: 'relative', width: 34, height: 5 }}>
                <Handle type="target" position={Position.Top} />
                {commentBadge}
                <div style={{ width: '100%', height: '100%', background: selected ? 'var(--rf-primary)' : BAR_COLOR, borderRadius: 1 }} />
                <div style={{ position: 'absolute', left: '100%', top: '50%', transform: 'translateY(-50%)', marginLeft: 10, fontSize: 12.5, textAlign: 'left', whiteSpace: 'nowrap' }}>
                    <strong style={{ display: 'block' }}>{data.label || '(réceptivité)'}</strong>
                    {subtitle && <span style={{ display: 'block', color: 'var(--rf-text-muted)', fontSize: 11, marginTop: 2 }}>{subtitle}</span>}
                </div>
                <Handle type="source" position={Position.Bottom} />
            </div>
        )
    }

    if (DIVERGENCE_TYPES.includes(type)) {
        const isDouble = type === 'and_divergence' || type === 'and_convergence'
        const barColor = selected ? 'var(--rf-primary)' : BAR_COLOR

        return (
            <div style={{ position: 'relative', width: 110, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 3, padding: '6px 0' }}>
                <Handle type="target" position={Position.Top} />
                {commentBadge}
                <div style={{ width: '100%', height: 4, background: barColor, borderRadius: 1 }} />
                {isDouble && <div style={{ width: '100%', height: 4, background: barColor, borderRadius: 1 }} />}
                {data.label && <span style={{ fontSize: 11, color: 'var(--rf-text-muted)' }}>{data.label}</span>}
                <Handle type="source" position={Position.Bottom} />
            </div>
        )
    }

    return (
        <div
            style={{
                border: `1.5px solid ${selected ? 'var(--rf-primary)' : 'var(--rf-border)'}`,
                padding: '10px 16px',
                minWidth: 120,
                textAlign: 'center',
                position: 'relative',
                fontSize: 12.5,
                boxShadow: selected ? '0 0 0 3px rgba(37,99,235,.15)' : '0 1px 2px rgba(15,23,42,.06)',
                ...SHAPE_STYLE[type as BoxNodeType],
            }}
        >
            {hasHandles && <Handle type="target" position={Position.Top} />}
            {commentBadge}
            <strong style={{ display: 'block' }}>{data.label || '(sans nom)'}</strong>
            {subtitle && <span style={{ display: 'block', color: 'var(--rf-text-muted)', fontSize: 11, marginTop: 2 }}>{subtitle}</span>}
            {hasHandles && <Handle type="source" position={Position.Bottom} />}
        </div>
    )
}
