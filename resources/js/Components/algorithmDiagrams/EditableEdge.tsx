import type { DiagramPoint } from '@/types/algorithmDiagrams'
import { BaseEdge, EdgeLabelRenderer, useReactFlow, type EdgeProps } from '@xyflow/react'
import { useCallback } from 'react'

export default function EditableEdge({ id, sourceX, sourceY, targetX, targetY, data, style, markerEnd, selected, label }: EdgeProps) {
    const { setEdges, screenToFlowPosition } = useReactFlow()
    const relativePoints = ((data as { points?: DiagramPoint[] } | undefined)?.points ?? [])
    // Points are stored relative to the source connection point, so custom paths follow the source node when it moves.
    const points: DiagramPoint[] = relativePoints.map((p) => ({ x: p.x + sourceX, y: p.y + sourceY }))

    const allPoints: DiagramPoint[] = [{ x: sourceX, y: sourceY }, ...points, { x: targetX, y: targetY }]
    const path = allPoints.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ')

    const midIndex = Math.floor((allPoints.length - 1) / 2)
    const labelX = (allPoints[midIndex].x + allPoints[midIndex + 1].x) / 2
    const labelY = (allPoints[midIndex].y + allPoints[midIndex + 1].y) / 2

    const onPointPointerDown = useCallback(
        (index: number) => (event: React.PointerEvent) => {
            event.stopPropagation()
            const onMove = (moveEvent: PointerEvent) => {
                const pos = screenToFlowPosition({ x: moveEvent.clientX, y: moveEvent.clientY })
                const relative = { x: pos.x - sourceX, y: pos.y - sourceY }
                setEdges((eds) =>
                    eds.map((e) =>
                        e.id === id
                            ? { ...e, data: { ...e.data, points: relativePoints.map((p, i) => (i === index ? relative : p)) } }
                            : e,
                    ),
                )
            }
            const onUp = () => {
                window.removeEventListener('pointermove', onMove)
                window.removeEventListener('pointerup', onUp)
            }
            window.addEventListener('pointermove', onMove)
            window.addEventListener('pointerup', onUp)
        },
        [id, relativePoints, sourceX, sourceY, screenToFlowPosition, setEdges],
    )

    return (
        <>
            <BaseEdge id={id} path={path} style={style} markerEnd={markerEnd as string} />
            {label && (
                <EdgeLabelRenderer>
                    <div
                        style={{
                            position: 'absolute',
                            transform: `translate(-50%, -50%) translate(${labelX}px, ${labelY}px)`,
                            background: '#fff',
                            border: '1px solid var(--rf-border)',
                            borderRadius: 5,
                            padding: '1px 6px',
                            fontSize: 11,
                            pointerEvents: 'none',
                        }}
                    >
                        {label}
                    </div>
                </EdgeLabelRenderer>
            )}
            {selected && (
                <EdgeLabelRenderer>
                    {points.map((p, i) => (
                        <div
                            key={i}
                            onPointerDown={onPointPointerDown(i)}
                            title="Glisser pour déplacer ce point"
                            style={{
                                position: 'absolute',
                                transform: `translate(-50%, -50%) translate(${p.x}px, ${p.y}px)`,
                                width: 11,
                                height: 11,
                                borderRadius: '50%',
                                background: '#fff',
                                border: '2px solid var(--rf-primary)',
                                cursor: 'grab',
                                pointerEvents: 'all',
                            }}
                        />
                    ))}
                </EdgeLabelRenderer>
            )}
        </>
    )
}
