import InputError from '@/Components/InputError'
import AppLayout from '@/Layouts/AppLayout'
import { roleLabels } from '@/types/projects'
import type { ProjectInvitation, ProjectMember, ProjectMemberRole } from '@/types/projects'
import { Head, router, useForm } from '@inertiajs/react'
import { Crown, Mail, Trash2, UserPlus } from 'lucide-react'
import type { FormEvent } from 'react'

interface MembersProps {
    project: { id: string; name: string }
    members: ProjectMember[]
    invitations: ProjectInvitation[]
    roles: ProjectMemberRole[]
    canManage: boolean
}

export default function Members({ project, members, invitations, roles, canManage }: MembersProps) {
    const form = useForm({ email: '', role: roles[0] ?? 'contributor' })

    const submitInvitation = (event: FormEvent) => {
        event.preventDefault()
        form.post(`/projects/${project.id}/members/invitations`, { onSuccess: () => form.reset('email') })
    }

    const changeRole = (member: ProjectMember, role: string) => {
        router.put(`/projects/${project.id}/members/${member.id}`, { role })
    }

    const removeMember = (member: ProjectMember) => {
        if (window.confirm(`Retirer ${member.user.name} du projet ?`)) {
            router.delete(`/projects/${project.id}/members/${member.id}`)
        }
    }

    return (
        <AppLayout breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'Équipe' }]}>
            <Head title={`Équipe · ${project.name}`} />
            <section className="rf-page-intro"><p className="rf-eyebrow">Projet</p><h1>Équipe</h1><p>Gérez les membres et les invitations de <strong>{project.name}</strong>.</p></section>

            {canManage && (
                <div className="rf-panel">
                    <h2>Inviter un membre</h2>
                    <form className="rf-form" onSubmit={submitInvitation}>
                        <div className="rf-form-grid">
                            <label>Email<input type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required /><InputError message={form.errors.email} /></label>
                            <label>Rôle<select value={form.data.role} onChange={(e) => form.setData('role', e.target.value as ProjectMemberRole)}>{roles.map((role) => <option key={role} value={role}>{roleLabels[role]}</option>)}</select><InputError message={form.errors.role} /></label>
                        </div>
                        <button type="submit" className="rf-button rf-button--primary" disabled={form.processing}><UserPlus size={15} />{form.processing ? 'Envoi…' : 'Envoyer l’invitation'}</button>
                    </form>
                </div>
            )}

            <div className="rf-panel">
                <h2>Membres ({members.length})</h2>
                <div className="rf-member-list">
                    {members.map((member) => (
                        <div className="rf-member-row" key={member.id}>
                            <div className="rf-member-identity">
                                <div className="rf-avatar">{member.user.name.slice(0, 2).toUpperCase()}</div>
                                <div><strong>{member.user.name}</strong><span>{member.user.email}</span></div>
                            </div>
                            <div className="rf-member-actions">
                                {member.is_owner ? (
                                    <span className="rf-badge rf-badge--completed"><Crown size={12} /> {roleLabels[member.role]}</span>
                                ) : canManage ? (
                                    <>
                                        <select className="rf-select" value={member.role} onChange={(e) => changeRole(member, e.target.value)}>
                                            {roles.map((role) => <option key={role} value={role}>{roleLabels[role]}</option>)}
                                        </select>
                                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => removeMember(member)} aria-label="Retirer ce membre"><Trash2 size={14} /></button>
                                    </>
                                ) : (
                                    <span className="rf-badge rf-badge--in_progress">{roleLabels[member.role]}</span>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {invitations.length > 0 && (
                <div className="rf-panel">
                    <h2>Invitations en attente ({invitations.length})</h2>
                    <div className="rf-member-list">
                        {invitations.map((invitation) => (
                            <div className="rf-member-row" key={invitation.id}>
                                <div className="rf-member-identity">
                                    <div className="rf-avatar"><Mail size={14} /></div>
                                    <div><strong>{invitation.email}</strong><span>Expire le {new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(invitation.expires_at))}</span></div>
                                </div>
                                <span className="rf-badge rf-badge--draft">{roleLabels[invitation.role]}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </AppLayout>
    )
}
