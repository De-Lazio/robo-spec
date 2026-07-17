import { useState } from 'react'
import {
  Bot, Home, FolderOpen, Users, Settings, LogOut, Bell, Search,
  ChevronRight, Plus, X, ChevronDown, Menu,
} from 'lucide-react'
import type { Page, Project, User } from '../../types'

interface AppLayoutProps {
  children: React.ReactNode
  currentPage: Page
  selectedProject: Project | null
  user: User
  onNavigate: (page: Page) => void
  onSelectProject: (project: Project | null) => void
  onLogout: () => void
  projects: Project[]
  headerTitle?: string
  headerActions?: React.ReactNode
  breadcrumbs?: Array<{ label: string; onClick?: () => void }>
}

function Avatar({ name, size = 32 }: { name: string; size?: number }) {
  const initials = name.split(' ').map((n) => n[0]).join('').slice(0, 2).toUpperCase()
  const colors = ['#2563eb', '#7c3aed', '#0891b2', '#059669', '#d97706']
  const color = colors[name.charCodeAt(0) % colors.length]
  return (
    <div style={{
      width: size, height: size, borderRadius: '50%', background: color,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontSize: size * 0.35, fontWeight: 700, color: 'white', flexShrink: 0,
      fontFamily: 'Plus Jakarta Sans, sans-serif',
    }}>
      {initials}
    </div>
  )
}

const statusColors: Record<string, string> = {
  'draft': 'badge-gray',
  'in-progress': 'badge-blue',
  'testing': 'badge-yellow',
  'completed': 'badge-green',
  'archived': 'badge-gray',
}
const statusLabels: Record<string, string> = {
  'draft': 'Brouillon',
  'in-progress': 'En cours',
  'testing': 'Tests',
  'completed': 'Terminé',
  'archived': 'Archivé',
}

function Sidebar({
  currentPage, selectedProject, user, projects, onNavigate, onSelectProject, onLogout,
}: {
  currentPage: Page
  selectedProject: Project | null
  user: User
  projects: Project[]
  onNavigate: (page: Page) => void
  onSelectProject: (project: Project | null) => void
  onLogout: () => void
}) {
  const [projectsOpen, setProjectsOpen] = useState(true)
  const [showUserMenu, setShowUserMenu] = useState(false)

  return (
    <aside style={{
      width: 'var(--sidebar-width)',
      minWidth: 'var(--sidebar-width)',
      height: '100vh',
      background: 'white',
      borderRight: '1.5px solid var(--border)',
      display: 'flex',
      flexDirection: 'column',
      position: 'sticky',
      top: 0,
      overflow: 'hidden',
    }}>
      {/* Logo */}
      <div style={{ padding: '20px 16px 16px', borderBottom: '1.5px solid var(--border)' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          <div style={{ width: 34, height: 34, background: 'var(--primary)', borderRadius: 9, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Bot size={18} color="white" />
          </div>
          <div>
            <div style={{ fontSize: 14.5, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>RoboForge</div>
            <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: -1 }}>Gestion de projets robotiques</div>
          </div>
        </div>
      </div>

      {/* Main nav */}
      <nav style={{ padding: '12px 10px', borderBottom: '1.5px solid var(--border)' }}>
        <button
          className={`sidebar-link${currentPage === 'dashboard' ? ' active' : ''}`}
          style={{ width: '100%', border: 'none', background: 'none', textAlign: 'left' }}
          onClick={() => { onNavigate('dashboard'); onSelectProject(null) }}
        >
          <Home size={16} /> Tableau de bord
        </button>
        <button
          className="sidebar-link"
          style={{ width: '100%', border: 'none', background: 'none', textAlign: 'left' }}
        >
          <Users size={16} /> Équipe globale
        </button>
        <button
          className="sidebar-link"
          style={{ width: '100%', border: 'none', background: 'none', textAlign: 'left' }}
        >
          <Settings size={16} /> Paramètres
        </button>
      </nav>

      {/* Projects */}
      <div style={{ flex: 1, overflowY: 'auto', padding: '12px 10px' }}>
        <button
          onClick={() => setProjectsOpen(!projectsOpen)}
          style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%', border: 'none', background: 'none', padding: '4px 10px', cursor: 'pointer', marginBottom: 4 }}
        >
          <span style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.07em' }}>Mes Projets</span>
          <ChevronDown size={13} color="var(--text-muted)" style={{ transform: projectsOpen ? 'rotate(0deg)' : 'rotate(-90deg)', transition: 'transform 0.2s' }} />
        </button>

        {projectsOpen && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
            {projects.map((p) => (
              <button
                key={p.id}
                onClick={() => { onSelectProject(p); onNavigate('project-detail') }}
                className={`sidebar-link${selectedProject?.id === p.id && currentPage === 'project-detail' ? ' active' : ''}`}
                style={{ width: '100%', border: 'none', background: 'none', textAlign: 'left', flexDirection: 'column', alignItems: 'flex-start', gap: 3, paddingTop: 8, paddingBottom: 8 }}
              >
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, width: '100%' }}>
                  <div style={{ width: 7, height: 7, borderRadius: '50%', flexShrink: 0, background: p.status === 'completed' ? 'var(--success)' : p.status === 'in-progress' ? 'var(--primary)' : p.status === 'testing' ? 'var(--warning)' : 'var(--text-muted)' }} />
                  <span style={{ fontSize: 13, flex: 1, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontWeight: selectedProject?.id === p.id && currentPage === 'project-detail' ? 600 : 500 }}>{p.name}</span>
                </div>
                <div style={{ paddingLeft: 15 }}>
                  <div className="progress-bar" style={{ width: 140, height: 3 }}>
                    <div className="progress-fill" style={{ width: `${p.progress}%`, background: p.status === 'completed' ? 'var(--success)' : undefined }} />
                  </div>
                </div>
              </button>
            ))}

            <button
              className="sidebar-link"
              style={{ width: '100%', border: 'none', background: 'none', textAlign: 'left', color: 'var(--primary)', marginTop: 4 }}
              onClick={() => { onSelectProject(null); onNavigate('dashboard') }}
            >
              <Plus size={14} /> Nouveau projet
            </button>
          </div>
        )}
      </div>

      {/* User section */}
      <div style={{ padding: '12px 10px', borderTop: '1.5px solid var(--border)', position: 'relative' }}>
        {showUserMenu && (
          <div style={{ position: 'absolute', bottom: 70, left: 10, right: 10, background: 'white', border: '1.5px solid var(--border)', borderRadius: 10, boxShadow: 'var(--shadow-lg)', padding: 6, zIndex: 20 }}>
            <button className="sidebar-link" style={{ width: '100%', border: 'none', background: 'none', textAlign: 'left' }}>
              <Settings size={15} /> Mon profil
            </button>
            <button className="sidebar-link" style={{ width: '100%', border: 'none', background: 'none', textAlign: 'left', color: 'var(--danger)' }} onClick={onLogout}>
              <LogOut size={15} /> Déconnexion
            </button>
          </div>
        )}
        <button
          onClick={() => setShowUserMenu(!showUserMenu)}
          style={{ display: 'flex', alignItems: 'center', gap: 9, width: '100%', padding: '8px 10px', borderRadius: 8, border: 'none', background: showUserMenu ? 'var(--surface-2)' : 'none', cursor: 'pointer', transition: 'background 0.15s' }}
          onMouseEnter={(e) => { if (!showUserMenu) (e.currentTarget as HTMLElement).style.background = 'var(--surface-2)' }}
          onMouseLeave={(e) => { if (!showUserMenu) (e.currentTarget as HTMLElement).style.background = 'none' }}
        >
          <Avatar name={user.name} size={30} />
          <div style={{ flex: 1, textAlign: 'left' }}>
            <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)' }}>{user.name}</div>
            <div style={{ fontSize: 11.5, color: 'var(--text-muted)' }}>{user.email}</div>
          </div>
          <ChevronDown size={13} color="var(--text-muted)" />
        </button>
      </div>
    </aside>
  )
}

function Header({
  breadcrumbs, headerActions, user, onNavigate,
}: {
  breadcrumbs: Array<{ label: string; onClick?: () => void }>
  headerActions?: React.ReactNode
  user: User
  onNavigate: (page: Page) => void
}) {
  const [searchVal, setSearchVal] = useState('')

  return (
    <header style={{
      height: 'var(--header-height)',
      background: 'white',
      borderBottom: '1.5px solid var(--border)',
      display: 'flex',
      alignItems: 'center',
      padding: '0 24px',
      gap: 16,
      position: 'sticky',
      top: 0,
      zIndex: 10,
    }}>
      {/* Breadcrumb */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 6, flex: 1 }}>
        {breadcrumbs.map((b, i) => (
          <span key={i} style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
            {i > 0 && <ChevronRight size={14} color="var(--text-muted)" />}
            <span
              onClick={b.onClick}
              style={{
                fontSize: 13.5,
                fontWeight: i === breadcrumbs.length - 1 ? 700 : 500,
                color: i === breadcrumbs.length - 1 ? 'var(--text-primary)' : 'var(--text-muted)',
                cursor: b.onClick ? 'pointer' : 'default',
                transition: 'color 0.15s',
              }}
              onMouseEnter={(e) => { if (b.onClick) (e.target as HTMLElement).style.color = 'var(--primary)' }}
              onMouseLeave={(e) => { if (b.onClick) (e.target as HTMLElement).style.color = i === breadcrumbs.length - 1 ? 'var(--text-primary)' : 'var(--text-muted)' }}
            >
              {b.label}
            </span>
          </span>
        ))}
      </div>

      {/* Search */}
      <div style={{ position: 'relative', width: 220 }}>
        <Search size={14} style={{ position: 'absolute', left: 11, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
        <input
          className="input-field"
          style={{ paddingLeft: 34, paddingTop: 7, paddingBottom: 7, fontSize: 13 }}
          placeholder="Rechercher..."
          value={searchVal}
          onChange={(e) => setSearchVal(e.target.value)}
        />
      </div>

      {/* Actions */}
      {headerActions}

      {/* Bell */}
      <button className="btn btn-ghost btn-icon" style={{ position: 'relative' }}>
        <Bell size={17} />
        <span style={{ position: 'absolute', top: 6, right: 6, width: 7, height: 7, background: 'var(--primary)', borderRadius: '50%', border: '1.5px solid white' }} />
      </button>

      <Avatar name={user.name} size={30} />
    </header>
  )
}

export function AppLayout({ children, currentPage, selectedProject, user, onNavigate, onSelectProject, onLogout, projects, headerActions, breadcrumbs = [] }: AppLayoutProps) {
  return (
    <div style={{ display: 'flex', height: '100vh', overflow: 'hidden', background: 'var(--bg)' }}>
      <Sidebar
        currentPage={currentPage}
        selectedProject={selectedProject}
        user={user}
        projects={projects}
        onNavigate={onNavigate}
        onSelectProject={onSelectProject}
        onLogout={onLogout}
      />
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column', overflow: 'hidden' }}>
        <Header
          breadcrumbs={[{ label: 'RoboForge' }, ...breadcrumbs]}
          headerActions={headerActions}
          user={user}
          onNavigate={onNavigate}
        />
        <main style={{ flex: 1, overflowY: 'auto', padding: '28px 32px' }}>
          {children}
        </main>
      </div>
    </div>
  )
}

export { Avatar, statusColors, statusLabels }
