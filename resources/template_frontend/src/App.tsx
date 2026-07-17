import { useState } from 'react'
import type { Page, Project, User } from './types'
import { mockUser, mockProjects } from './data/mockData'
import { LoginPage, RegisterPage, ForgotPasswordPage, ResetPasswordPage } from './components/auth/AuthPages'
import { AppLayout } from './components/layout/AppLayout'
import { DashboardPage } from './components/dashboard/DashboardPage'
import { ProjectDetailPage } from './components/project/ProjectDetailPage'
import { CDCWizard } from './components/cdc/CDCWizard'
import type { CDCData } from './types'

export default function App() {
  const [page, setPage] = useState<Page>('login')
  const [user, setUser] = useState<User | null>(null)
  const [projects, setProjects] = useState<Project[]>(mockProjects)
  const [selectedProject, setSelectedProject] = useState<Project | null>(null)

  const navigate = (p: Page) => setPage(p)

  const handleLogin = () => {
    setUser(mockUser)
    setPage('dashboard')
  }

  const handleLogout = () => {
    setUser(null)
    setSelectedProject(null)
    setPage('login')
  }

  const handleCreateProject = (partial: Partial<Project>) => {
    const newProject: Project = {
      id: String(Date.now()),
      name: partial.name || 'Nouveau Projet',
      description: partial.description || '',
      type: partial.type || 'other',
      status: 'draft',
      category: partial.category || '',
      tags: partial.tags || [],
      team: [{ id: 't_new', name: user!.name, email: user!.email, role: 'chef', joinedAt: new Date().toISOString().split('T')[0] }],
      progress: 0,
      createdAt: new Date().toISOString().split('T')[0],
      updatedAt: new Date().toISOString().split('T')[0],
      hasCDC: false,
      resources: [],
    }
    setProjects((prev) => [newProject, ...prev])
  }

  const handleSaveCDC = (cdc: CDCData) => {
    if (!selectedProject) return
    const updated = { ...selectedProject, hasCDC: true, cdcData: cdc, updatedAt: new Date().toISOString().split('T')[0] }
    setProjects((prev) => prev.map((p) => (p.id === updated.id ? updated : p)))
    setSelectedProject(updated)
    setPage('project-detail')
  }

  // Select project from list (sync with projects state for updates)
  const handleSelectProject = (p: Project | null) => {
    if (!p) { setSelectedProject(null); return }
    const fresh = projects.find((x) => x.id === p.id) || p
    setSelectedProject(fresh)
  }

  // Auth pages
  if (!user) {
    const authProps = { page, onNavigate: navigate, onLogin: handleLogin }
    if (page === 'login') return <LoginPage {...authProps} />
    if (page === 'register') return <RegisterPage {...authProps} />
    if (page === 'forgot-password') return <ForgotPasswordPage {...authProps} />
    if (page === 'reset-password') return <ResetPasswordPage {...authProps} />
    return <LoginPage {...authProps} />
  }

  // CDC Wizard (full-screen, no layout)
  if (page === 'cdc-wizard' && selectedProject) {
    return (
      <CDCWizard
        project={selectedProject}
        onSave={handleSaveCDC}
        onBack={() => setPage('project-detail')}
      />
    )
  }

  // Breadcrumbs for layout
  const breadcrumbs =
    page === 'project-detail' && selectedProject
      ? [
          { label: 'Tableau de bord', onClick: () => { navigate('dashboard'); setSelectedProject(null) } },
          { label: selectedProject.name },
        ]
      : [{ label: 'Tableau de bord' }]

  return (
    <AppLayout
      currentPage={page}
      selectedProject={selectedProject}
      user={user}
      projects={projects}
      onNavigate={navigate as any}
      onSelectProject={handleSelectProject}
      onLogout={handleLogout}
      breadcrumbs={breadcrumbs}
    >
      {page === 'dashboard' && (
        <DashboardPage
          projects={projects}
          onSelectProject={(p) => { handleSelectProject(p); navigate('project-detail') }}
          onNavigate={(p) => navigate(p)}
          onCreateProject={handleCreateProject}
        />
      )}
      {page === 'project-detail' && selectedProject && (
        <ProjectDetailPage
          project={projects.find((p) => p.id === selectedProject.id) || selectedProject}
          onOpenCDC={() => navigate('cdc-wizard')}
          onBack={() => { navigate('dashboard'); setSelectedProject(null) }}
        />
      )}
    </AppLayout>
  )
}
