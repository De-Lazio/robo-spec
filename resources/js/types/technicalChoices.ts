import type { ComponentType } from '@/types/components'

export interface TechnicalChoiceComponentCategory {
    id: number
    name: string
    type: ComponentType
}

export interface AvailableComponent {
    id: string
    name: string
    manufacturer: string | null
    owner_project_id: string | null
    image_url: string | null
    category: TechnicalChoiceComponentCategory
}

export interface ProjectComponentChoice {
    id: number
    quantity: number
    rationale: string | null
    linked_function_ids: string[]
    component: {
        id: string
        name: string
        manufacturer: string | null
        price_cents: number | null
        currency: string | null
        is_active: boolean
        owner_project_id: string | null
        image_url: string | null
        category: TechnicalChoiceComponentCategory
    }
}

export interface CdcFunction {
    id: string
    name: string
}
