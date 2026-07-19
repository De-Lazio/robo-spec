import NotificationBell from '@/Components/Notifications/NotificationBell'
import { Link, usePage } from '@inertiajs/react'
import { ArrowLeft, Bot, Building2, ChevronRight, ClipboardList, Cpu, FolderOpen, GitBranch, Home, Kanban, Menu, Plus, Settings, UserCircle, Users, Workflow, X } from 'lucide-react'
import { type ReactNode, useState } from 'react'

interface AppLayoutProps {
    children: ReactNode
    breadcrumbs?: Array<{ label: string; href?: string }>
    actions?: ReactNode
    project?: { id: string; name: string }
}

interface AuthUser {
    name: string
    email: string
}

const globalNavigation = [
    { href: '/dashboard', label: 'Tableau de bord', icon: Home },
    { href: '/projects', label: 'Mes projets', icon: FolderOpen },
    { href: '/organizations', label: 'Organisations', icon: Building2 },
    { href: '/components', label: 'Bibliothèque', icon: Cpu },
    { href: '/profile', label: 'Mon profil', icon: UserCircle },
]

function projectNavigation(projectId: string) {
    const base = `/projects/${projectId}`

    return [
        { href: base, label: 'Vue d’ensemble', icon: Home, exact: true },
        { href: `${base}/members`, label: 'Équipe', icon: Users, exact: false },
        { href: `${base}/cdc`, label: 'Cahier des charges', icon: ClipboardList, exact: false },
        { href: `${base}/technical-choices`, label: 'Choix techniques', icon: Cpu, exact: false },
        { href: `${base}/tasks`, label: 'Tâches', icon: Kanban, exact: false },
        { href: `${base}/algorithm-diagrams`, label: 'Algorigrammes', icon: Workflow, exact: false },
        { href: `${base}/resources`, label: 'Ressources', icon: FolderOpen, exact: false },
        { href: `${base}/github`, label: 'GitHub', icon: GitBranch, exact: false },
        { href: `${base}/settings`, label: 'Paramètres', icon: Settings, exact: false },
    ]
}

export default function AppLayout({ children, breadcrumbs = [], actions, project }: AppLayoutProps) {
    const [isOpen, setIsOpen] = useState(false)
    const page = usePage<{ auth: { user: AuthUser } }>()
    const currentPath = page.url.split('?')[0]
    const user = page.props.auth.user

    return (
        <div className="rf-shell">
            <button className="rf-mobile-menu" onClick={() => setIsOpen(true)} aria-label="Ouvrir le menu">
                <Menu size={20} />
            </button>
            {isOpen && <button className="rf-sidebar-backdrop" onClick={() => setIsOpen(false)} aria-label="Fermer le menu" />}
            <aside className={`rf-sidebar ${isOpen ? 'is-open' : ''}`}>
                <div className="rf-brand">
                    <div className="rf-brand-mark"><Bot size={19} /></div>
                    <div><strong>RoboForge</strong><span>Projets robotiques</span></div>
                    <button className="rf-sidebar-close" onClick={() => setIsOpen(false)} aria-label="Fermer le menu"><X size={18} /></button>
                </div>
                {project ? (
                    <nav className="rf-navigation" aria-label="Navigation du projet">
                        {projectNavigation(project.id).map(({ href, label, icon: Icon, exact }) => (
                            <Link key={href} href={href} className={`rf-nav-link ${(exact ? currentPath === href : currentPath.startsWith(href)) ? 'is-active' : ''}`} onClick={() => setIsOpen(false)}>
                                <Icon size={17} />{label}
                            </Link>
                        ))}
                    </nav>
                ) : (
                    <nav className="rf-navigation" aria-label="Navigation principale">
                        {globalNavigation.map(({ href, label, icon: Icon }) => (
                            <Link key={href} href={href} className={`rf-nav-link ${currentPath === href || ((href === '/projects' || href === '/organizations' || href === '/components') && currentPath.startsWith(href)) ? 'is-active' : ''}`} onClick={() => setIsOpen(false)}>
                                <Icon size={17} />{label}
                            </Link>
                        ))}
                    </nav>
                )}
                <div className="rf-sidebar-projects">
                    {project ? (
                        <>
                            <span>Projet</span>
                            <strong style={{ fontSize: 13, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{project.name}</strong>
                            <Link href="/projects"><ArrowLeft size={15} /> Tous les projets</Link>
                        </>
                    ) : (
                        <>
                            <span>Projets</span>
                            <Link href="/projects/create"><Plus size={15} /> Nouveau projet</Link>
                        </>
                    )}
                </div>
                <div className="rf-user-card">
                    <div className="rf-avatar">{user.name.slice(0, 2).toUpperCase()}</div>
                    <div><strong>{user.name}</strong><span>{user.email}</span></div>
                    <Link href="/profile" aria-label="Paramètres du profil"><Settings size={16} /></Link>
                </div>
            </aside>
            <div className="rf-main-column">
                <header className="rf-header">
                    <div className="rf-breadcrumbs">
                        <Link href="/dashboard">RoboForge</Link>
                        {breadcrumbs.map((crumb) => <span key={crumb.label}><ChevronRight size={14} />{crumb.href ? <Link href={crumb.href}>{crumb.label}</Link> : <strong>{crumb.label}</strong>}</span>)}
                    </div>
                    <div className="rf-header-actions"><NotificationBell />{actions}</div>
                </header>
                <main className="rf-content">{children}</main>
            </div>
        </div>
    )
}
