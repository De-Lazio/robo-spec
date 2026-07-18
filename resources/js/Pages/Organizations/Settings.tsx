import OrganizationForm from '@/Components/organizations/OrganizationForm'
import AppLayout from '@/Layouts/AppLayout'
import type { Organization } from '@/types/organizations'
import { Head, router } from '@inertiajs/react'
import { Trash2 } from 'lucide-react'

interface SettingsProps {
    organization: Organization
    canManage: boolean
    canDelete: boolean
}

export default function Settings({ organization, canManage, canDelete }: SettingsProps) {
    const confirmAndSend = (message: string, action: () => void) => { if (window.confirm(message)) action() }

    return <AppLayout breadcrumbs={[{ label: 'Organisations', href: '/organizations' }, { label: organization.name, href: `/organizations/${organization.id}` }, { label: 'Paramètres' }]}>
        <Head title={`Paramètres · ${organization.name}`} />
        <section className="rf-form-page">
            <div className="rf-page-intro"><p className="rf-eyebrow">Organisation</p><h1>Paramètres</h1><p>Gérez les informations de <strong>{organization.name}</strong>.</p></div>
            {canManage ? (
                <div className="rf-panel"><OrganizationForm organization={organization} submitLabel="Enregistrer les modifications" onSubmit={(form) => form.put(`/organizations/${organization.id}`)} /></div>
            ) : (
                <div className="rf-panel"><p>Vous pouvez consulter cette organisation, mais vous ne disposez pas des droits de modification.</p></div>
            )}
            {canDelete && (
                <div className="rf-danger-panel rf-danger-panel--delete">
                    <div><h2>Zone dangereuse</h2><p>La suppression de l’organisation détache ses projets (ils redeviennent des projets personnels) et retire tous les membres. Cette action est réservée au propriétaire.</p></div>
                    <button className="rf-button rf-button--danger" onClick={() => confirmAndSend(`Supprimer définitivement « ${organization.name} » ?`, () => router.delete(`/organizations/${organization.id}`))}><Trash2 size={15} />Supprimer</button>
                </div>
            )}
        </section>
    </AppLayout>
}
