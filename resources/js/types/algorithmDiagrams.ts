import type { ComponentType } from '@/types/components'

export type DiagramFormalism = 'algorigramme' | 'grafcet'

export const FORMALISM_LABELS: Record<DiagramFormalism, string> = {
    algorigramme: 'Algorigramme',
    grafcet: 'GRAFCET',
}

export type DiagramNodeType =
    | 'start' | 'end' | 'process' | 'decision' | 'io' | 'wait' | 'calc' | 'communication' | 'comment'
    | 'initial_step' | 'step' | 'transition' | 'or_divergence' | 'or_convergence' | 'and_divergence' | 'and_convergence'

export const NODE_TYPES_BY_FORMALISM: Record<DiagramFormalism, DiagramNodeType[]> = {
    algorigramme: ['start', 'end', 'process', 'decision', 'io', 'wait', 'calc', 'communication', 'comment'],
    grafcet: ['initial_step', 'step', 'transition', 'or_divergence', 'or_convergence', 'and_divergence', 'and_convergence', 'comment'],
}

/** Every node type across both formalisms, deduplicated — used to register the React Flow node renderer map once. */
export const ALL_NODE_TYPES: DiagramNodeType[] = Array.from(new Set(Object.values(NODE_TYPES_BY_FORMALISM).flat()))

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
    initial_step: 'Étape initiale',
    step: 'Étape',
    transition: 'Transition',
    or_divergence: 'Divergence OU',
    or_convergence: 'Convergence OU',
    and_divergence: 'Divergence ET',
    and_convergence: 'Convergence ET',
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
    initial_step: ['actuator', 'pre_actuator', 'effector'],
    step: ['actuator', 'pre_actuator', 'effector'],
    transition: ['sensor'],
    or_divergence: [],
    or_convergence: [],
    and_divergence: [],
    and_convergence: [],
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
    formalism: DiagramFormalism
    data: AlgorithmDiagramPayload
    microcontroller_component_id: number | null
    energy_source_component_id: number | null
}

export interface AlgorithmDiagramSummary {
    id: string
    name: string
    formalism: DiagramFormalism
    updated_at: string | null
}
