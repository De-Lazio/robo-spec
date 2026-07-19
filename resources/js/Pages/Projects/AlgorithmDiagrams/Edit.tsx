import ComponentPicker from '@/Components/algorithmDiagrams/ComponentPicker'
import DiagramNode from '@/Components/algorithmDiagrams/DiagramNode'
import EditableEdge from '@/Components/algorithmDiagrams/EditableEdge'
import SettingsModal from '@/Components/algorithmDiagrams/SettingsModal'
import Toolbar from '@/Components/algorithmDiagrams/Toolbar'
import AppLayout from '@/Layouts/AppLayout'
import type { AlgorithmDiagram, AssignableComponent, DiagramFormalism, DiagramNode as DiagramNodeShape, DiagramNodeType, DiagramPoint, DiagramVariable } from '@/types/algorithmDiagrams'
import { ALL_NODE_TYPES, NODE_TYPES_BY_FORMALISM } from '@/types/algorithmDiagrams'
import type { ResourceCategory } from '@/types/projects'
import { Head, router } from '@inertiajs/react'
import type { FormDataConvertible } from '@inertiajs/core'
import {
    addEdge,
    Background,
    Controls,
    MarkerType,
    Panel,
    ReactFlow,
    ReactFlowProvider,
    useEdgesState,
    useNodesState,
    useReactFlow,
    type Connection,
    type Edge,
    type EdgeTypes,
    type Node,
    type NodeMouseHandler,
    type NodeTypes,
    type OnConnect,
} from '@xyflow/react'
import '@xyflow/react/dist/style.css'
import { toBlob } from 'html-to-image'
import { AlertTriangle, Plus, Trash2, X } from 'lucide-react'
import { useCallback, useMemo, useRef, useState, type MouseEvent as ReactMouseEvent } from 'react'

interface EditProps {
    project: { id: string; name: string }
    diagram: AlgorithmDiagram
    availableComponents: AssignableComponent[]
    canManage: boolean
}

function computeWarnings(nodes: Node[], edges: Edge[], formalism: DiagramFormalism): string[] {
    const warnings: string[] = []
    const rootType = formalism === 'grafcet' ? 'initial_step' : 'start'
    const hasRoot = nodes.some((n) => n.type === rootType)

    if (!hasRoot) warnings.push(formalism === 'grafcet' ? 'Aucune Étape initiale.' : 'Aucun nœud Début.')
    if (formalism === 'algorigramme' && !nodes.some((n) => n.type === 'end')) warnings.push('Aucun nœud Fin.')

    const targeted = new Set(edges.map((e) => e.target))
    const orphans = nodes.filter((n) => n.type !== rootType && n.type !== 'comment' && !targeted.has(n.id))
    orphans.forEach((n) => warnings.push(`« ${(n.data as { label?: string })?.label || n.id} » n'est jamais atteint.`))

    if (formalism === 'grafcet') {
        const andDivergences = nodes.filter((n) => n.type === 'and_divergence').length
        const andConvergences = nodes.filter((n) => n.type === 'and_convergence').length
        if (andDivergences !== andConvergences) {
            warnings.push('Le nombre de divergences ET et de convergences ET ne correspond pas.')
        }
    }

    return warnings
}

export default function Edit(props: EditProps) {
    return (
        <ReactFlowProvider>
            <DiagramCanvas {...props} />
        </ReactFlowProvider>
    )
}

function DiagramCanvas({ project, diagram, availableComponents, canManage }: EditProps) {
    const [name, setName] = useState(diagram.name)
    const [nodes, setNodes, onNodesChange] = useNodesState<Node>(diagram.data.nodes as unknown as Node[])
    const [edges, setEdges, onEdgesChange] = useEdgesState<Edge>(
        (diagram.data.edges ?? []).map((e) => ({
            id: e.id,
            source: e.source,
            target: e.target,
            label: e.label ?? undefined,
            data: { style: e.style ?? 'default', points: e.points ?? [] },
        })) as unknown as Edge[],
    )
    const [variables, setVariables] = useState<DiagramVariable[]>(diagram.data.variables)
    const [microcontrollerId, setMicrocontrollerId] = useState<number | null>(diagram.microcontroller_component_id)
    const [energySourceId, setEnergySourceId] = useState<number | null>(diagram.energy_source_component_id)
    const [pendingType, setPendingType] = useState<DiagramNodeType | null>(null)
    const [selectedNodeId, setSelectedNodeId] = useState<string | null>(null)
    const [selectedEdgeId, setSelectedEdgeId] = useState<string | null>(null)
    const [history, setHistory] = useState<Array<{ nodes: Node[]; edges: Edge[] }>>([])
    const [future, setFuture] = useState<Array<{ nodes: Node[]; edges: Edge[] }>>([])
    const [saving, setSaving] = useState(false)
    const [exporting, setExporting] = useState(false)
    const [exportCategory, setExportCategory] = useState<ResourceCategory>('software')
    const [newVariableName, setNewVariableName] = useState('')
    const [settingsOpen, setSettingsOpen] = useState(false)
    const [edgeStrokeWidth, setEdgeStrokeWidth] = useState<number>(diagram.data.edgeStrokeWidth ?? 2)

    const wrapperRef = useRef<HTMLDivElement>(null)
    const { screenToFlowPosition } = useReactFlow()

    const nodeTypes: NodeTypes = useMemo(
        () =>
            Object.fromEntries(
                ALL_NODE_TYPES.map((type) => [
                    type,
                    ({ data, selected }: { type: DiagramNodeType; data: Record<string, unknown>; selected?: boolean }) => (
                        <DiagramNode type={type} data={data as never} selected={selected} availableComponents={availableComponents} />
                    ),
                ]),
            ),
        [availableComponents],
    )

    const edgeTypes: EdgeTypes = useMemo(() => ({ default: EditableEdge }), [])

    const pushHistory = useCallback(() => {
        setHistory((h) => [...h, { nodes, edges }])
        setFuture([])
    }, [nodes, edges])

    const onConnect: OnConnect = useCallback(
        (connection: Connection) => {
            pushHistory()
            setEdges((eds) => addEdge({ ...connection, id: crypto.randomUUID(), data: { style: 'default' } }, eds))
        },
        [pushHistory, setEdges],
    )

    const onPaneClick = useCallback(
        (event: ReactMouseEvent) => {
            setSelectedNodeId(null)
            setSelectedEdgeId(null)

            if (!pendingType || !canManage) return

            pushHistory()
            const position = screenToFlowPosition({ x: event.clientX, y: event.clientY })
            const newNode: Node = { id: crypto.randomUUID(), type: pendingType, position, data: { label: '' } }
            setNodes((nds) => [...nds, newNode])
            setPendingType(null)
        },
        [pendingType, canManage, screenToFlowPosition, pushHistory, setNodes],
    )

    const onNodeClick: NodeMouseHandler = useCallback((_event, node) => {
        setSelectedNodeId(node.id)
        setSelectedEdgeId(null)
    }, [])

    const onEdgeClick = useCallback((_event: ReactMouseEvent, edge: Edge) => {
        setSelectedEdgeId(edge.id)
        setSelectedNodeId(null)
    }, [])

    const undo = () => {
        if (history.length === 0) return
        const prev = history[history.length - 1]
        setFuture((f) => [{ nodes, edges }, ...f])
        setHistory((h) => h.slice(0, -1))
        setNodes(prev.nodes)
        setEdges(prev.edges)
    }

    const redo = () => {
        if (future.length === 0) return
        const next = future[0]
        setHistory((h) => [...h, { nodes, edges }])
        setFuture((f) => f.slice(1))
        setNodes(next.nodes)
        setEdges(next.edges)
    }

    const selectedNode = useMemo(() => nodes.find((n) => n.id === selectedNodeId) ?? null, [nodes, selectedNodeId])
    const selectedEdge = useMemo(() => edges.find((e) => e.id === selectedEdgeId) ?? null, [edges, selectedEdgeId])

    const updateSelectedNodeData = (patch: Record<string, unknown>) => {
        setNodes((nds) => nds.map((n) => (n.id === selectedNodeId ? { ...n, data: { ...n.data, ...patch } } : n)))
    }

    const deleteSelectedNode = () => {
        if (!selectedNodeId) return
        pushHistory()
        setNodes((nds) => nds.filter((n) => n.id !== selectedNodeId))
        setEdges((eds) => eds.filter((e) => e.source !== selectedNodeId && e.target !== selectedNodeId))
        setSelectedNodeId(null)
    }

    const toggleEdgeLoop = () => {
        if (!selectedEdgeId) return
        setEdges((eds) => eds.map((e) => (e.id === selectedEdgeId ? { ...e, data: { ...e.data, style: e.data?.style === 'loop' ? 'default' : 'loop' } } : e)))
    }

    const addEdgePoint = () => {
        if (!selectedEdgeId) return
        pushHistory()
        setEdges((eds) =>
            eds.map((e) => {
                if (e.id !== selectedEdgeId) return e
                // Points are relative to the source connection point — start near it and offset each additional point.
                const existing: DiagramPoint[] = (e.data as { points?: DiagramPoint[] } | undefined)?.points ?? []
                const last = existing[existing.length - 1] ?? { x: 0, y: 0 }
                return { ...e, data: { ...e.data, points: [...existing, { x: last.x + 40, y: last.y + 40 }] } }
            }),
        )
    }

    const removeEdgePoint = (index: number) => {
        if (!selectedEdgeId) return
        pushHistory()
        setEdges((eds) =>
            eds.map((e) => {
                if (e.id !== selectedEdgeId) return e
                const existing: DiagramPoint[] = (e.data as { points?: DiagramPoint[] } | undefined)?.points ?? []
                return { ...e, data: { ...e.data, points: existing.filter((_, i) => i !== index) } }
            }),
        )
    }

    const deleteSelectedEdge = () => {
        if (!selectedEdgeId) return
        pushHistory()
        setEdges((eds) => eds.filter((e) => e.id !== selectedEdgeId))
        setSelectedEdgeId(null)
    }

    const addVariable = () => {
        const trimmed = newVariableName.trim()
        if (!trimmed) return
        setVariables((vars) => [...vars, { id: crypto.randomUUID(), name: trimmed }])
        setNewVariableName('')
    }

    const removeVariable = (id: string) => {
        setVariables((vars) => vars.filter((v) => v.id !== id))
    }

    const displayEdges = useMemo(
        () =>
            edges.map((e) => ({
                ...e,
                style: { strokeWidth: edgeStrokeWidth, ...(e.data?.style === 'loop' ? { strokeDasharray: '6 4' } : {}) },
                markerEnd: { type: MarkerType.ArrowClosed, color: '#64748b', width: 18, height: 18 },
            })),
        [edges, edgeStrokeWidth],
    )

    const warnings = useMemo(() => computeWarnings(nodes, edges, diagram.formalism), [nodes, edges, diagram.formalism])

    const save = () => {
        setSaving(true)
        const payload = {
            name,
            data: {
                nodes: nodes.map((n) => ({ id: n.id, type: n.type, position: n.position, data: n.data })),
                edges: edges.map((e) => {
                    const edgeData = e.data as { style?: string; points?: DiagramPoint[] } | undefined
                    return { id: e.id, source: e.source, target: e.target, label: e.label ?? null, style: edgeData?.style ?? 'default', points: edgeData?.points ?? [] }
                }),
                variables,
                edgeStrokeWidth,
            },
            microcontroller_component_id: microcontrollerId,
            energy_source_component_id: energySourceId,
        }
        router.put(
            `/projects/${project.id}/algorithm-diagrams/${diagram.id}`,
            payload as unknown as Record<string, FormDataConvertible>,
            { preserveScroll: true, onFinish: () => setSaving(false) },
        )
    }

    const exportPng = async () => {
        if (!wrapperRef.current) return
        setExporting(true)

        try {
            const blob = await toBlob(wrapperRef.current, { backgroundColor: '#ffffff' })
            if (!blob) return

            const file = new File([blob], `${diagram.name}.png`, { type: 'image/png' })
            router.post(
                `/projects/${project.id}/algorithm-diagrams/${diagram.id}/export`,
                { file, category: exportCategory },
                { forceFormData: true, onFinish: () => setExporting(false) },
            )
        } catch {
            setExporting(false)
        }
    }

    const microcontrollerOptions = availableComponents.filter((c) => c.category.type === 'microcontroller')
    const energySourceOptions = availableComponents.filter((c) => c.category.type === 'energy_source')

    return (
        <AppLayout breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'Algorigrammes', href: `/projects/${project.id}/algorithm-diagrams` }, { label: diagram.name }]}>
            <Head title={`${diagram.name} · ${project.name}`} />

            <div className="rf-diagram-workspace">
                <Toolbar
                    nodeTypes={NODE_TYPES_BY_FORMALISM[diagram.formalism]}
                    pendingType={pendingType}
                    onSelectType={setPendingType}
                    onUndo={undo}
                    onRedo={redo}
                    canUndo={history.length > 0}
                    canRedo={future.length > 0}
                    onSave={save}
                    saving={saving}
                    onExport={exportPng}
                    exporting={exporting}
                    onOpenSettings={() => setSettingsOpen(true)}
                    canManage={canManage}
                />

                <SettingsModal
                    show={settingsOpen}
                    onClose={() => setSettingsOpen(false)}
                    name={name}
                    onNameChange={setName}
                    microcontrollerId={microcontrollerId}
                    onMicrocontrollerChange={setMicrocontrollerId}
                    microcontrollerOptions={microcontrollerOptions}
                    energySourceId={energySourceId}
                    onEnergySourceChange={setEnergySourceId}
                    energySourceOptions={energySourceOptions}
                    variables={variables}
                    onAddVariable={addVariable}
                    onRemoveVariable={removeVariable}
                    newVariableName={newVariableName}
                    onNewVariableNameChange={setNewVariableName}
                    exportCategory={exportCategory}
                    onExportCategoryChange={setExportCategory}
                    edgeStrokeWidth={edgeStrokeWidth}
                    onEdgeStrokeWidthChange={setEdgeStrokeWidth}
                    canManage={canManage}
                />

                <div className="rf-diagram-body">
                    <div ref={wrapperRef} className="rf-diagram-canvas">
                        <ReactFlow
                            nodes={nodes}
                            edges={displayEdges}
                            onNodesChange={canManage ? onNodesChange : undefined}
                            onEdgesChange={canManage ? onEdgesChange : undefined}
                            onConnect={canManage ? onConnect : undefined}
                            onPaneClick={onPaneClick}
                            onNodeClick={onNodeClick}
                            onEdgeClick={onEdgeClick}
                            nodeTypes={nodeTypes}
                            edgeTypes={edgeTypes}
                            nodesDraggable={canManage}
                            nodesConnectable={canManage}
                            elementsSelectable
                            fitView
                        >
                            <Background />
                            <Controls />
                            {warnings.length > 0 && (
                                <Panel position="top-right">
                                    <div className="rf-hint-banner rf-hint-banner--warning" style={{ maxWidth: 260, margin: 0 }}>
                                        <strong><AlertTriangle size={13} style={{ verticalAlign: 'middle', marginRight: 6 }} />Points à vérifier :</strong>
                                        <ul style={{ margin: '6px 0 0', paddingLeft: 18 }}>
                                            {warnings.map((w) => <li key={w}>{w}</li>)}
                                        </ul>
                                    </div>
                                </Panel>
                            )}
                        </ReactFlow>
                    </div>

                    {(selectedNode || selectedEdge) && canManage && (
                        <aside className="rf-panel rf-diagram-inspector">
                            {selectedNode && (
                                <NodeInspector
                                    node={selectedNode as unknown as DiagramNodeShape}
                                    availableComponents={availableComponents}
                                    variables={variables}
                                    onChange={updateSelectedNodeData}
                                    onDelete={deleteSelectedNode}
                                    onClose={() => setSelectedNodeId(null)}
                                />
                            )}
                            {selectedEdge && (
                                <EdgeInspector
                                    edge={selectedEdge}
                                    onLabelChange={(label) => setEdges((eds) => eds.map((e) => (e.id === selectedEdgeId ? { ...e, label } : e)))}
                                    onToggleLoop={toggleEdgeLoop}
                                    onAddPoint={addEdgePoint}
                                    onRemovePoint={removeEdgePoint}
                                    onDelete={deleteSelectedEdge}
                                    onClose={() => setSelectedEdgeId(null)}
                                />
                            )}
                        </aside>
                    )}
                </div>
            </div>
        </AppLayout>
    )
}

function NodeInspector({
    node,
    availableComponents,
    variables,
    onChange,
    onDelete,
    onClose,
}: {
    node: DiagramNodeShape
    availableComponents: AssignableComponent[]
    variables: DiagramVariable[]
    onChange: (patch: Record<string, unknown>) => void
    onDelete: () => void
    onClose: () => void
}) {
    return (
        <div className="rf-form">
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <h2 style={{ margin: 0 }}>Nœud</h2>
                <button type="button" onClick={onClose} style={{ border: 0, background: 'none', cursor: 'pointer' }}><X size={16} /></button>
            </div>
            <label>Libellé<input className="rf-input" value={node.data.label ?? ''} onChange={(e) => onChange({ label: e.target.value })} /></label>
            <label>Commentaire (survol)<textarea className="rf-input" rows={2} value={node.data.comment ?? ''} onChange={(e) => onChange({ comment: e.target.value })} /></label>

            {(node.type === 'wait' || node.type === 'transition') && (
                <label>Durée<input className="rf-input" value={node.data.duration ?? ''} onChange={(e) => onChange({ duration: e.target.value })} placeholder="ex. 2s" /></label>
            )}

            {node.type === 'calc' && (
                <>
                    <label>
                        Variable
                        <select className="rf-select" value={node.data.variableId ?? ''} onChange={(e) => onChange({ variableId: e.target.value || null })}>
                            <option value="">—</option>
                            {variables.map((v) => <option key={v.id} value={v.id}>{v.name}</option>)}
                        </select>
                    </label>
                    <label>Expression<input className="rf-input" value={node.data.expression ?? ''} onChange={(e) => onChange({ expression: e.target.value })} placeholder="ex. compteur + 1" /></label>
                </>
            )}

            {['process', 'decision', 'io', 'communication', 'step', 'initial_step', 'transition'].includes(node.type) && (
                <div>
                    <div className="rf-label">Composants liés</div>
                    <ComponentPicker
                        nodeType={node.type}
                        availableComponents={availableComponents}
                        selectedIds={node.data.componentIds ?? []}
                        onChange={(ids) => onChange({ componentIds: ids })}
                    />
                </div>
            )}

            <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={onDelete}><Trash2 size={13} />Supprimer ce nœud</button>
        </div>
    )
}

function EdgeInspector({
    edge,
    onLabelChange,
    onToggleLoop,
    onAddPoint,
    onRemovePoint,
    onDelete,
    onClose,
}: {
    edge: Edge
    onLabelChange: (label: string) => void
    onToggleLoop: () => void
    onAddPoint: () => void
    onRemovePoint: (index: number) => void
    onDelete: () => void
    onClose: () => void
}) {
    const edgeData = edge.data as { style?: string; points?: DiagramPoint[] } | undefined
    const points = edgeData?.points ?? []

    return (
        <div className="rf-form">
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <h2 style={{ margin: 0 }}>Liaison</h2>
                <button type="button" onClick={onClose} style={{ border: 0, background: 'none', cursor: 'pointer' }}><X size={16} /></button>
            </div>
            <label>Étiquette (condition)<input className="rf-input" value={(edge.label as string) ?? ''} onChange={(e) => onLabelChange(e.target.value)} placeholder="ex. Oui" /></label>
            <label style={{ display: 'flex', alignItems: 'center', gap: 8, flexDirection: 'row' }}>
                <input type="checkbox" className="rf-checkbox" checked={edgeData?.style === 'loop'} onChange={onToggleLoop} />
                Reprise / saut en arrière (pointillé)
            </label>

            <div>
                <div className="rf-label">Points de passage</div>
                <p className="rf-field-hint" style={{ marginTop: 0, marginBottom: 8 }}>Ajoutez un point puis faites-le glisser sur le canevas pour router la liaison autour des autres nœuds.</p>
                {points.map((_, i) => (
                    <div key={i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', fontSize: 12.5, padding: '4px 0' }}>
                        <span>Point {i + 1}</span>
                        <button type="button" onClick={() => onRemovePoint(i)} style={{ border: 0, background: 'none', cursor: 'pointer' }} aria-label="Supprimer ce point"><X size={12} /></button>
                    </div>
                ))}
                <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={onAddPoint}><Plus size={13} />Ajouter un point</button>
            </div>

            <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={onDelete}><Trash2 size={13} />Supprimer cette liaison</button>
        </div>
    )
}
