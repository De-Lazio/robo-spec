import { Link, usePage } from '@inertiajs/react'
import { Bot, Building2, ChevronRight, Cpu, FolderOpen, Home, Menu, Plus, Settings, UserCircle, X } from 'lucide-react'
import { type ReactNode, useState } from 'react'

interface AppLayoutProps {
    children: ReactNode
    breadcrumbs?: Array<{ label: string; href?: string }>
    actions?: ReactNode
}

interface AuthUser {
    name: string
    email: string
}

export default function AppLayout({ children, breadcrumbs = [], actions }: AppLayoutProps) {
    const [isOpen, setIsOpen] = useState(false)
    const page = usePage<{ auth: { user: AuthUser } }>()
    const currentPath = page.url.split('?')[0]
    const user = page.props.auth.user

    const navigation = [
        { href: '/dashboard', label: 'Tableau de bord', icon: Home },
        { href: '/projects', label: 'Mes projets', icon: FolderOpen },
        { href: '/organizations', label: 'Organisations', icon: Building2 },
        { href: '/components', label: 'Bibliothèque', icon: Cpu },
        { href: '/profile', label: 'Mon profil', icon: UserCircle },
    ]

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
                <nav className="rf-navigation" aria-label="Navigation principale">
                    {navigation.map(({ href, label, icon: Icon }) => (
                        <Link key={href} href={href} className={`rf-nav-link ${currentPath === href || ((href === '/projects' || href === '/organizations' || href === '/components') && currentPath.startsWith(href)) ? 'is-active' : ''}`} onClick={() => setIsOpen(false)}>
                            <Icon size={17} />{label}
                        </Link>
                    ))}
                </nav>
                <div className="rf-sidebar-projects">
                    <span>Projets</span>
                    <Link href="/projects/create"><Plus size={15} /> Nouveau projet</Link>
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
                    <div className="rf-header-actions">{actions}</div>
                </header>
                <main className="rf-content">{children}</main>
            </div>
        </div>
    )
}
