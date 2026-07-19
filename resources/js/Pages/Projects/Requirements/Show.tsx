import AppLayout from '@/Layouts/AppLayout'
import { STEP_NAMES } from '@/types/requirements'
import type { RequirementsData, RequirementsDocumentSummary } from '@/types/requirements'
import { Head, Link } from '@inertiajs/react'
import { ClipboardList, Pencil } from 'lucide-react'
import type { ReactNode } from 'react'

interface ShowProps {
    project: { id: string; name: string }
    requirementsDocument: RequirementsDocumentSummary | null
}

export default function Show({ project, requirementsDocument }: ShowProps) {
    return (
        <AppLayout
            breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'Cahier des charges' }]}
            actions={<Link href={`/projects/${project.id}/cdc/edit`} className="rf-button rf-button--primary rf-button--small"><Pencil size={15} />Modifier</Link>}
        >
            <Head title={`CDC · ${project.name}`} />
            <section className="rf-page-intro">
                <p className="rf-eyebrow">Projet</p>
                <h1>Cahier des charges</h1>
                <p>Spécification technique de <strong>{project.name}</strong>.</p>
            </section>

            {!requirementsDocument ? (
                <div className="rf-empty">
                    <ClipboardList size={28} />
                    <h2>Aucune version publiée</h2>
                    <p>Ce projet n’a pas encore de cahier des charges publié. Modifiez le brouillon puis publiez-le pour qu’il apparaisse ici.</p>
                    <Link className="rf-button rf-button--primary" href={`/projects/${project.id}/cdc/edit`}><Pencil size={15} />Modifier le brouillon</Link>
                </div>
            ) : (
                <RequirementsReadView data={requirementsDocument.data} version={requirementsDocument.version} publishedAt={requirementsDocument.publishedAt} />
            )}
        </AppLayout>
    )
}

function RequirementsReadView({ data, version, publishedAt }: { data: RequirementsData; version: number; publishedAt?: string | null }) {
    return (
        <div className="rf-stack">
            <div className="rf-panel">
                <span className="rf-badge rf-badge--completed">Version {version}</span>
                {publishedAt && <p style={{ marginTop: 8, color: 'var(--rf-text-muted)', fontSize: '12.5px' }}>Publié le {new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' }).format(new Date(publishedAt))}</p>}
            </div>

            <ReadSection title={STEP_NAMES[0]}>
                <ReadField label="Nom du projet" value={data.step1.projectName} />
                <ReadField label="Équipe" value={data.step1.team} />
                <ReadField label="Date" value={data.step1.date} />
                <ReadField label="Type de projet" value={data.step1.projectType} />
                <ReadField label="Encadrant" value={data.step1.supervisor} />
            </ReadSection>

            <ReadSection title={STEP_NAMES[1]}>
                <ReadField label="Contexte" value={data.step2.context} />
                <ReadField label="Problème" value={data.step2.problem} />
                <ReadField label="Pourquoi un robot ?" value={data.step2.whyRobot} />
            </ReadSection>

            <ReadSection title={STEP_NAMES[2]}>
                <ReadList items={data.step3.missions} />
            </ReadSection>

            <ReadSection title={STEP_NAMES[3]}>
                <ReadTags items={data.step4.users} />
                {data.step4.otherUsers && <ReadField label="Autres utilisateurs" value={data.step4.otherUsers} />}
            </ReadSection>

            <ReadSection title={STEP_NAMES[4]}>
                <ReadList items={data.step5.functions.map((f) => `${f.id} — ${f.name}`)} />
            </ReadSection>

            <ReadSection title={STEP_NAMES[5]}>
                <ReadField label="Taille max." value={data.step6.maxSize} />
                <ReadField label="Poids max." value={data.step6.maxWeight} />
                <ReadField label="Autonomie min." value={data.step6.minAutonomy} />
                <ReadField label="Vitesse min." value={data.step6.minSpeed} />
                <ReadField label="Budget max." value={data.step6.maxBudget} />
                <ReadField label="Coût estimé" value={data.step6.estimatedCost} />
                <ReadTags items={data.step6.safetyConstraints} />
                <ReadField label="Température" value={data.step6.temperature} />
                <ReadField label="Conditions d'utilisation" value={data.step6.usageConditions} />
            </ReadSection>

            <ReadSection title={STEP_NAMES[6]}>
                <ReadList items={data.step7.criteria.map((c) => `${c.name} : ${c.value}`)} />
            </ReadSection>

            <ReadSection title={STEP_NAMES[7]}>
                <ReadField label="Résultat attendu" value={data.step8.expectedResult} />
                <ReadField label="Critères de succès" value={data.step8.successCriteria} />
                <ReadField label="Méthode de test" value={data.step8.testMethod} />
            </ReadSection>
        </div>
    )
}

function ReadSection({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div className="rf-panel">
            <h2>{title}</h2>
            <div style={{ display: 'grid', gap: 10, marginTop: 10 }}>{children}</div>
        </div>
    )
}

function ReadField({ label, value }: { label: string; value?: string | null }) {
    if (!value) return null
    return (
        <div>
            <div className="rf-label" style={{ marginBottom: 3 }}>{label}</div>
            <div style={{ fontSize: '13px', color: 'var(--rf-text-secondary)', whiteSpace: 'pre-wrap' }}>{value}</div>
        </div>
    )
}

function ReadList({ items }: { items: string[] }) {
    const filtered = items.filter(Boolean)
    if (!filtered.length) return <div className="rf-field-hint">Aucune donnée renseignée.</div>
    return (
        <ul style={{ margin: 0, paddingLeft: 18, display: 'grid', gap: 4 }}>
            {filtered.map((item) => <li key={item} style={{ fontSize: '13px', color: 'var(--rf-text-secondary)' }}>{item}</li>)}
        </ul>
    )
}

function ReadTags({ items }: { items: string[] }) {
    if (!items.length) return <div className="rf-field-hint">Aucune donnée renseignée.</div>
    return (
        <div className="rf-tags">
            {items.map((item) => <span key={item}>{item}</span>)}
        </div>
    )
}
