import OrganizationForm from '@/Components/organizations/OrganizationForm'
import AppLayout from '@/Layouts/AppLayout'
import { Head } from '@inertiajs/react'

export default function Create() {
    return <AppLayout breadcrumbs={[{ label: 'Organisations', href: '/organizations' }, { label: 'Nouvelle organisation' }]}>
        <Head title="Nouvelle organisation" />
        <section className="rf-form-page">
            <div className="rf-page-intro"><p className="rf-eyebrow">Nouvelle organisation</p><h1>Créer une organisation</h1><p>Regroupez vos projets et votre équipe permanente au même endroit.</p></div>
            <div className="rf-panel"><OrganizationForm submitLabel="Créer l’organisation" onSubmit={(form) => form.post('/organizations')} /></div>
        </section>
    </AppLayout>
}
