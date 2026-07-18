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

export type ProjectMemberRole = 'owner' | 'manager' | 'mechanical' | 'electronics' | 'software' | 'contributor' | 'viewer'

export interface ProjectMember {
    id: number
    role: ProjectMemberRole
    is_owner: boolean
    joined_at: string | null
    user: { name: string; email: string }
}

export interface ProjectInvitation {
    id: number
    email: string
    role: ProjectMemberRole
    expires_at: string
    created_at: string
}

export const roleLabels: Record<ProjectMemberRole, string> = {
    owner: 'Propriétaire',
    manager: 'Manager',
    mechanical: 'Contributeur mécanique',
    electronics: 'Contributeur électronique',
    software: 'Contributeur logiciel',
    contributor: 'Contributeur',
    viewer: 'Observateur',
}
