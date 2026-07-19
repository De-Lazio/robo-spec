import type { ComponentType } from '@/types/components'

export type DiagramNodeType = 'start' | 'end' | 'process' | 'decision' | 'io' | 'wait' | 'calc' | 'communication' | 'comment'

export const NODE_TYPES: DiagramNodeType[] = ['start', 'end', 'process', 'decision', 'io', 'wait', 'calc', 'communication', 'comment']

export const NODE_TYPE_LABELS: Record<DiagramNodeType, string> = {
    start: 'Début',
    end: 'Fin',
    process: 'Traitement',
    decision: 'Condition',
    io: 'Entrée / Sortie',
    wait: 'Attente',
    calc: 'Calcul / Affectation',
    communication: 'Communication',
    comment: 'Commentaire',
}

/** Mirrors the backend's NodeComponentRules — null means unrestricted, [] means no components allowed. */
export const NODE_ALLOWED_COMPONENT_TYPES: Record<DiagramNodeType, ComponentType[] | null> = {
    start: [],
    end: [],
    comment: [],
    wait: [],
    calc: [],
    process: ['actuator', 'pre_actuator', 'effector'],
    decision: ['sensor'],
    io: ['sensor', 'actuator', 'pre_actuator', 'effector'],
    communication: null,
}

export interface DiagramCategory {
    id: number
    name: string
    type: ComponentType
}

export interface AssignableComponent {
    id: number
    name: string
    manufacturer: string | null
    category: DiagramCategory
}

export interface DiagramNodeData {
    label: string
    componentIds?: number[]
    duration?: string
    variableId?: string | null
    expression?: string
    comment?: string | null
}

export interface DiagramNode {
    id: string
    type: DiagramNodeType
    position: { x: number; y: number }
    data: DiagramNodeData
}

export interface DiagramPoint {
    x: number
    y: number
}

export interface DiagramEdge {
    id: string
    source: string
    target: string
    label?: string | null
    style?: 'default' | 'loop'
    points?: DiagramPoint[]
}

export interface DiagramVariable {
    id: string
    name: string
}

export interface AlgorithmDiagramPayload {
    nodes: DiagramNode[]
    edges: DiagramEdge[]
    variables: DiagramVariable[]
    edgeStrokeWidth?: number
}

export interface AlgorithmDiagram {
    id: string
    name: string
    formalism: 'algorigramme'
    data: AlgorithmDiagramPayload
    microcontroller_component_id: number | null
    energy_source_component_id: number | null
}

export interface AlgorithmDiagramSummary {
    id: string
    name: string
    formalism: 'algorigramme'
    updated_at: string | null
}
