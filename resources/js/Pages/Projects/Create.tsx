import ProjectForm from '@/Components/projects/ProjectForm'
import AppLayout from '@/Layouts/AppLayout'
import type { RobotType } from '@/types/projects'
import { Head, router } from '@inertiajs/react'

interface CreateProps {
    robotTypes: RobotType[]
    organizations: Array<{ id: string; name: string }>
}

export default function Create({ robotTypes, organizations }: CreateProps) {
    return <AppLayout breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: 'Nouveau projet' }]}><Head title="Nouveau projet" /><section className="rf-form-page"><div className="rf-page-intro"><p className="rf-eyebrow">Nouveau projet</p><h1>Créer un projet robotique</h1><p>Commencez avec les informations essentielles. Vous pourrez compléter le cahier des charges ensuite.</p></div><div className="rf-panel"><ProjectForm robotTypes={robotTypes} organizations={organizations} submitLabel="Créer le projet" onSubmit={(form) => form.post('/projects')} /></div></section></AppLayout>
}
