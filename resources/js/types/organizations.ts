export type OrganizationRole = 'owner' | 'admin' | 'member'

export interface Organization {
    id: string
    name: string
    slug: string
    description: string | null
    created_at: string | null
    owner: { name: string; email: string } | null
    members_count: number
    projects_count: number
}

export interface OrganizationMember {
    id: number
    role: OrganizationRole
    is_owner: boolean
    joined_at: string | null
    user: { name: string; email: string }
}

export interface OrganizationInvitation {
    id: number
    email: string
    role: OrganizationRole
    expires_at: string
    created_at: string
}

export const organizationRoleLabels: Record<OrganizationRole, string> = {
    owner: 'Propriétaire',
    admin: 'Administrateur',
    member: 'Membre',
}
