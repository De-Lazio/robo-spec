import InputError from '@/Components/InputError'
import AppLayout from '@/Layouts/AppLayout'
import { organizationRoleLabels } from '@/types/organizations'
import type { OrganizationInvitation, OrganizationMember, OrganizationRole } from '@/types/organizations'
import { Head, router, useForm } from '@inertiajs/react'
import { Crown, Mail, Trash2, UserPlus } from 'lucide-react'
import type { FormEvent } from 'react'

interface MembersProps {
    organization: { id: string; name: string }
    members: OrganizationMember[]
    invitations: OrganizationInvitation[]
    roles: OrganizationRole[]
    canManage: boolean
}

export default function Members({ organization, members, invitations, roles, canManage }: MembersProps) {
    const form = useForm({ email: '', role: roles[0] ?? 'member' })

    const submitInvitation = (event: FormEvent) => {
        event.preventDefault()
        form.post(`/organizations/${organization.id}/members/invitations`, { onSuccess: () => form.reset('email') })
    }

    const changeRole = (member: OrganizationMember, role: string) => {
        router.put(`/organizations/${organization.id}/members/${member.id}`, { role })
    }

    const removeMember = (member: OrganizationMember) => {
        if (window.confirm(`Retirer ${member.user.name} de l’organisation ?`)) {
            router.delete(`/organizations/${organization.id}/members/${member.id}`)
        }
    }

    return (
        <AppLayout breadcrumbs={[{ label: 'Organisations', href: '/organizations' }, { label: organization.name, href: `/organizations/${organization.id}` }, { label: 'Équipe' }]}>
            <Head title={`Équipe · ${organization.name}`} />
            <section className="rf-page-intro"><p className="rf-eyebrow">Organisation</p><h1>Équipe</h1><p>Gérez les membres et les invitations de <strong>{organization.name}</strong>.</p></section>

            {canManage && (
                <div className="rf-panel">
                    <h2>Inviter un membre</h2>
                    <form className="rf-form" onSubmit={submitInvitation}>
                        <div className="rf-form-grid">
                            <label>Email<input type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required /><InputError message={form.errors.email} /></label>
                            <label>Rôle<select value={form.data.role} onChange={(e) => form.setData('role', e.target.value as OrganizationRole)}>{roles.map((role) => <option key={role} value={role}>{organizationRoleLabels[role]}</option>)}</select><InputError message={form.errors.role} /></label>
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
                                    <span className="rf-badge rf-badge--completed"><Crown size={12} /> {organizationRoleLabels[member.role]}</span>
                                ) : canManage ? (
                                    <>
                                        <select className="rf-select" value={member.role} onChange={(e) => changeRole(member, e.target.value)}>
                                            {roles.map((role) => <option key={role} value={role}>{organizationRoleLabels[role]}</option>)}
                                        </select>
                                        <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={() => removeMember(member)} aria-label="Retirer ce membre"><Trash2 size={14} /></button>
                                    </>
                                ) : (
                                    <span className="rf-badge rf-badge--in_progress">{organizationRoleLabels[member.role]}</span>
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
                                <span className="rf-badge rf-badge--draft">{organizationRoleLabels[invitation.role]}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </AppLayout>
    )
}
