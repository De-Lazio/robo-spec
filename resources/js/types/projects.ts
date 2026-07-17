export type ProjectStatus = 'draft' | 'in_progress' | 'testing' | 'completed' | 'archived'
export type RobotType = 'mobile' | 'arm' | 'drone' | 'fixed' | 'humanoid' | 'other'

export interface Project {
    id: string
    name: string
    slug: string
    description: string | null
    robot_type: RobotType
    domain: string | null
    status: ProjectStatus
    progress: number
    archived_at: string | null
    created_at: string | null
    updated_at: string | null
    tags: string[]
    members_count: number
    activities_count: number
    owner: { name: string; email: string } | null
}

export interface Paginated<T> {
    data: T[]
    links: Array<{ url: string | null; label: string; active: boolean }>
}
