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
    resources_count: number
    tasks_count: number
    tasks_done_count: number
    owner: { name: string; email: string } | null
    organization_id: string | null
    organization: { id: string; name: string } | null
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

export type ResourceCategory = 'mechanical' | 'electronics' | 'software' | 'other'
export type ResourceKind = 'image' | 'document' | 'code' | 'cad' | 'schema' | 'archive' | 'other'

export interface Resource {
    id: string
    name: string
    original_name: string
    category: ResourceCategory
    kind: ResourceKind
    size_bytes: number
    description: string | null
    created_at: string | null
    uploader: { name: string } | null
    can_delete: boolean
    download_url: string
    preview_url: string | null
}

export const categoryLabels: Record<ResourceCategory, string> = {
    mechanical: 'Mécanique',
    electronics: 'Électronique',
    software: 'Informatique',
    other: 'Autre',
}

export const kindLabels: Record<ResourceKind, string> = {
    image: 'Image',
    document: 'Document',
    code: 'Code',
    cad: 'Modèle CAO',
    schema: 'Schéma',
    archive: 'Archive',
    other: 'Autre',
}

export type GithubSyncStatus = 'pending' | 'synced' | 'failed'

export interface GithubCommit {
    sha: string
    message: string
    author: string
    date: string | null
}

export interface GithubRepository {
    owner: string
    repository: string
    url: string
    default_branch: string | null
    visibility: string | null
    sync_status: GithubSyncStatus
    last_synced_at: string | null
    metadata: {
        description: string | null
        stars: number
        forks: number
        open_issues: number
        language: string | null
        branches: string[]
        commits: GithubCommit[]
        error: string | null
    }
}
