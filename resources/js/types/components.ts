export type ComponentType = 'microcontroller' | 'sensor' | 'actuator' | 'pre_actuator' | 'energy_source' | 'effector'

export interface ComponentCategory {
    id: number
    type: ComponentType
    name: string
    slug: string
    components_count: number
}

export interface ComponentCategoryOption {
    id: number
    type: ComponentType
    name: string
}

export interface LibraryComponent {
    id: string
    name: string
    manufacturer: string | null
    reference: string | null
    description: string | null
    specs: Record<string, string>
    price_cents: number | null
    currency: string | null
    supplier_url: string | null
    is_active: boolean
    datasheet_url: string | null
    category: { id: number; name: string; type: ComponentType } | null
}

export const componentTypeLabels: Record<ComponentType, string> = {
    microcontroller: 'Microcontrôleur',
    sensor: 'Capteur',
    actuator: 'Actionneur',
    pre_actuator: 'Pré-actionneur',
    energy_source: 'Source d’énergie',
    effector: 'Effecteur',
}
