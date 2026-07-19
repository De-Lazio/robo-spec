import InputError from '@/Components/InputError'
import AppLayout from '@/Layouts/AppLayout'
import type { GithubRepository, GithubSyncStatus } from '@/types/projects'
import { Head, router, useForm } from '@inertiajs/react'
import { AlertCircle, ExternalLink, GitBranch, GitCommit, GitFork, Link as LinkIcon, RefreshCw, Star, Trash2 } from 'lucide-react'
import type { FormEvent } from 'react'

interface GitHubProps {
    project: { id: string; name: string }
    repository: GithubRepository | null
    canManage: boolean
}

const statusLabels: Record<GithubSyncStatus, string> = {
    pending: 'Synchronisation…',
    synced: 'Synchronisé',
    failed: 'Échec de synchronisation',
}

export default function GitHub({ project, repository, canManage }: GitHubProps) {
    const form = useForm({ url: '' })

    const submitLink = (event: FormEvent) => {
        event.preventDefault()
        form.post(`/projects/${project.id}/github`)
    }

    const sync = () => router.post(`/projects/${project.id}/github/sync`)

    const unlink = () => {
        if (window.confirm('Délier ce dépôt GitHub du projet ?')) {
            router.delete(`/projects/${project.id}/github`)
        }
    }

    return (
        <AppLayout breadcrumbs={[{ label: 'Mes projets', href: '/projects' }, { label: project.name, href: `/projects/${project.id}` }, { label: 'GitHub' }]}>
            <Head title={`GitHub · ${project.name}`} />
            <section className="rf-page-intro"><p className="rf-eyebrow">Projet</p><h1>GitHub</h1><p>Dépôt source lié à <strong>{project.name}</strong>.</p></section>

            {!repository ? (
                <div className="rf-empty">
                    <GitBranch size={28} />
                    <h2>Lier un dépôt GitHub</h2>
                    <p>Connectez un dépôt GitHub public pour voir ses commits, branches et statistiques directement dans RoboForge.</p>
                    {canManage ? (
                        <form className="rf-form" style={{ maxWidth: 420, margin: '0 auto', textAlign: 'left' }} onSubmit={submitLink}>
                            <label>
                                URL du dépôt
                                <input type="text" className="rf-input" placeholder="https://github.com/organisation/depot" value={form.data.url} onChange={(e) => form.setData('url', e.target.value)} required />
                                <InputError message={form.errors.url} />
                            </label>
                            <button type="submit" className="rf-button rf-button--primary rf-button--block" disabled={form.processing}>
                                <LinkIcon size={15} />{form.processing ? 'Liaison…' : 'Lier le dépôt'}
                            </button>
                        </form>
                    ) : (
                        <p>Seuls le propriétaire ou un manager peuvent lier un dépôt.</p>
                    )}
                </div>
            ) : (
                <div className="rf-detail-grid">
                    <div>
                        <div className="rf-panel" style={{ marginBottom: 16 }}>
                            <div className="rf-repo-header">
                                <div className="rf-repo-identity">
                                    <div className="rf-repo-mark"><GitBranch size={16} /></div>
                                    <div>
                                        <strong style={{ fontSize: 14.5 }}>{repository.owner}/{repository.repository}</strong>
                                        <div style={{ fontSize: 12, color: 'var(--rf-text-muted)' }}>{repository.metadata.language ?? 'Langage inconnu'}</div>
                                    </div>
                                </div>
                                <a href={repository.url} target="_blank" rel="noreferrer" className="rf-button rf-button--secondary rf-button--small"><ExternalLink size={13} /> GitHub</a>
                            </div>
                            <p style={{ fontSize: 13.5, color: 'var(--rf-text-secondary)', margin: 0 }}>{repository.metadata.description ?? 'Aucune description.'}</p>
                            <div className="rf-repo-stats">
                                <span><Star size={14} /> <strong>{repository.metadata.stars}</strong> étoiles</span>
                                <span><GitFork size={14} /> <strong>{repository.metadata.forks}</strong> forks</span>
                                <span><AlertCircle size={14} /> <strong>{repository.metadata.open_issues}</strong> issues ouvertes</span>
                            </div>
                        </div>

                        <div className="rf-panel">
                            <h2>Historique des commits</h2>
                            {repository.metadata.commits.length === 0 && <p style={{ color: 'var(--rf-text-muted)', fontSize: 13 }}>Aucun commit trouvé.</p>}
                            {repository.metadata.commits.map((commit) => (
                                <div className="rf-commit-row" key={commit.sha}>
                                    <GitCommit size={14} color="var(--rf-primary)" style={{ marginTop: 2, flex: 'none' }} />
                                    <div>
                                        <strong>{commit.message}</strong>
                                        <span>
                                            <code>{commit.sha}</code>
                                            <span>{commit.author}</span>
                                            {commit.date && <span>{new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(commit.date))}</span>}
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <aside>
                        <div className="rf-panel" style={{ marginBottom: 16 }}>
                            <h2>Branches ({repository.metadata.branches.length})</h2>
                            {repository.metadata.branches.map((branch) => (
                                <div key={branch} className={`rf-branch-row ${branch === repository.default_branch ? 'is-default' : ''}`}>
                                    <GitBranch size={13} /> {branch}
                                </div>
                            ))}
                        </div>

                        <div className="rf-panel">
                            <h2>Synchronisation</h2>
                            <p style={{ fontSize: 13, marginBottom: 6 }}>
                                <span className={`rf-badge rf-badge--${repository.sync_status}`}>{statusLabels[repository.sync_status]}</span>
                            </p>
                            <p style={{ fontSize: 12, color: 'var(--rf-text-muted)' }}>
                                {repository.last_synced_at ? `Dernière synchro : ${new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(repository.last_synced_at))}` : 'Jamais synchronisé.'}
                            </p>
                            {repository.metadata.error && <p className="rf-error">{repository.metadata.error}</p>}
                            {canManage && (
                                <div style={{ display: 'flex', gap: 8, marginTop: 12 }}>
                                    <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={sync}><RefreshCw size={14} /> Synchroniser</button>
                                    <button type="button" className="rf-button rf-button--secondary rf-button--small" onClick={unlink}><Trash2 size={14} /> Délier</button>
                                </div>
                            )}
                        </div>
                    </aside>
                </div>
            )}
        </AppLayout>
    )
}
