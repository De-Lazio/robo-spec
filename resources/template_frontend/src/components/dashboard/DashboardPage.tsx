import { useState } from 'react'
import { Plus, FolderOpen, BarChart2, CheckCircle2, Clock, Cpu, Zap, Code2, Wrench, Bot, X, Calendar, Users } from 'lucide-react'
import type { Project, ProjectType, ProjectStatus } from '../../types'
import { Avatar, statusColors, statusLabels } from '../layout/AppLayout'

const typeIcons: Record<ProjectType, React.ReactNode> = {
  mobile: <Bot size={18} />,
  arm: <Wrench size={18} />,
  drone: <Zap size={18} />,
  fixed: <Cpu size={18} />,
  humanoid: <Bot size={18} />,
  other: <Code2 size={18} />,
}

const typeLabels: Record<ProjectType, string> = {
  mobile: 'Robot Mobile',
  arm: 'Bras Robotisé',
  drone: 'Drone',
  fixed: 'Robot Fixe',
  humanoid: 'Humanoïde',
  other: 'Autre',
}

function StatCard({ icon, label, value, sub, color = 'var(--primary)' }: { icon: React.ReactNode; label: string; value: string | number; sub?: string; color?: string }) {
  return (
    <div className="card" style={{ padding: '20px 22px', display: 'flex', gap: 16, alignItems: 'flex-start' }}>
      <div style={{ width: 42, height: 42, borderRadius: 10, background: `${color}12`, display: 'flex', alignItems: 'center', justifyContent: 'center', color, flexShrink: 0 }}>
        {icon}
      </div>
      <div>
        <div style={{ fontSize: 24, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.03em', lineHeight: 1 }}>{value}</div>
        <div style={{ fontSize: 13, color: 'var(--text-secondary)', marginTop: 4, fontWeight: 500 }}>{label}</div>
        {sub && <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>{sub}</div>}
      </div>
    </div>
  )
}

function ProjectCard({ project, onClick }: { project: Project; onClick: () => void }) {
  return (
    <div className="card card-hover" style={{ padding: '20px 22px', cursor: 'pointer', display: 'flex', flexDirection: 'column', gap: 14 }} onClick={onClick}>
      <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
        <div style={{
          width: 42, height: 42, borderRadius: 10, flexShrink: 0,
          background: project.status === 'completed' ? 'var(--success-light)' : project.status === 'testing' ? 'var(--warning-light)' : 'var(--primary-light)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          color: project.status === 'completed' ? 'var(--success)' : project.status === 'testing' ? '#d97706' : 'var(--primary)',
        }}>
          {typeIcons[project.type]}
        </div>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
            <h3 style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)', margin: 0, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', maxWidth: 200 }}>{project.name}</h3>
            <span className={`badge ${statusColors[project.status]}`}>{statusLabels[project.status]}</span>
          </div>
          <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>{typeLabels[project.type]} · {project.category}</div>
        </div>
      </div>

      <p style={{ fontSize: 13, color: 'var(--text-secondary)', margin: 0, lineHeight: 1.6, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
        {project.description}
      </p>

      {/* Tags */}
      <div style={{ display: 'flex', gap: 5, flexWrap: 'wrap' }}>
        {project.tags.slice(0, 3).map((tag) => (
          <span key={tag} style={{ fontSize: 11.5, padding: '2px 8px', background: 'var(--surface-2)', borderRadius: 4, color: 'var(--text-secondary)', fontWeight: 500 }}>{tag}</span>
        ))}
        {project.tags.length > 3 && (
          <span style={{ fontSize: 11.5, padding: '2px 8px', background: 'var(--surface-2)', borderRadius: 4, color: 'var(--text-muted)' }}>+{project.tags.length - 3}</span>
        )}
      </div>

      {/* Progress */}
      <div>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
          <span style={{ fontSize: 12, color: 'var(--text-muted)', fontWeight: 500 }}>Avancement</span>
          <span style={{ fontSize: 12, fontWeight: 700, color: project.status === 'completed' ? 'var(--success)' : 'var(--primary)' }}>{project.progress}%</span>
        </div>
        <div className="progress-bar">
          <div className="progress-fill" style={{ width: `${project.progress}%`, background: project.status === 'completed' ? 'var(--success)' : undefined }} />
        </div>
      </div>

      {/* Footer */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', paddingTop: 4, borderTop: '1px solid var(--border)' }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          {project.team.slice(0, 4).map((m, i) => (
            <div key={m.id} style={{ marginLeft: i === 0 ? 0 : -8, border: '2px solid white', borderRadius: '50%' }}>
              <Avatar name={m.name} size={24} />
            </div>
          ))}
          {project.team.length > 4 && (
            <div style={{ marginLeft: -8, width: 24, height: 24, borderRadius: '50%', background: 'var(--surface-2)', border: '2px solid white', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 10, color: 'var(--text-muted)', fontWeight: 600 }}>
              +{project.team.length - 4}
            </div>
          )}
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          <span style={{ fontSize: 12, color: 'var(--text-muted)', display: 'flex', alignItems: 'center', gap: 4 }}>
            <FolderOpen size={12} /> {project.resources.length}
          </span>
          {project.hasCDC && (
            <span style={{ fontSize: 12, color: 'var(--success)', display: 'flex', alignItems: 'center', gap: 3 }}>
              <CheckCircle2 size={12} /> CDC
            </span>
          )}
          <span style={{ fontSize: 12, color: 'var(--text-muted)', display: 'flex', alignItems: 'center', gap: 4 }}>
            <Clock size={12} /> {new Date(project.updatedAt).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })}
          </span>
        </div>
      </div>
    </div>
  )
}

function CreateProjectModal({ onClose, onCreate }: { onClose: () => void; onCreate: (p: Partial<Project>) => void }) {
  const [form, setForm] = useState({ name: '', description: '', type: 'mobile' as ProjectType, category: '', tags: '' })
  const update = (k: string) => (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) =>
    setForm((f) => ({ ...f, [k]: e.target.value }))

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    onCreate({
      name: form.name,
      description: form.description,
      type: form.type,
      category: form.category,
      tags: form.tags.split(',').map((t) => t.trim()).filter(Boolean),
    })
    onClose()
  }

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={(e) => e.stopPropagation()}>
        <div style={{ padding: '24px 28px', borderBottom: '1.5px solid var(--border)', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div>
            <h2 style={{ fontSize: 17, fontWeight: 800, color: 'var(--text-primary)', margin: 0, letterSpacing: '-0.02em' }}>Nouveau projet</h2>
            <p style={{ fontSize: 13, color: 'var(--text-muted)', margin: '4px 0 0' }}>Créez un projet robotique et invitez votre équipe</p>
          </div>
          <button className="btn btn-ghost btn-icon btn-sm" onClick={onClose}><X size={17} /></button>
        </div>

        <form onSubmit={handleSubmit} style={{ padding: '24px 28px', display: 'flex', flexDirection: 'column', gap: 18 }}>
          <div>
            <label className="input-label">Nom du projet <span style={{ color: 'var(--primary)' }}>*</span></label>
            <input className="input-field" placeholder="Ex: Robot Transporteur AGV" value={form.name} onChange={update('name')} required />
          </div>

          <div>
            <label className="input-label">Description</label>
            <textarea className="input-field" style={{ resize: 'vertical', minHeight: 80 }} placeholder="Décrivez brièvement l'objectif du projet..." value={form.description} onChange={update('description')} />
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
            <div>
              <label className="input-label">Type de robot <span style={{ color: 'var(--primary)' }}>*</span></label>
              <select className="input-field" value={form.type} onChange={update('type')}>
                <option value="mobile">Robot Mobile</option>
                <option value="arm">Bras Robotisé</option>
                <option value="drone">Drone</option>
                <option value="fixed">Robot Fixe</option>
                <option value="humanoid">Humanoïde</option>
                <option value="other">Autre</option>
              </select>
            </div>
            <div>
              <label className="input-label">Domaine</label>
              <input className="input-field" placeholder="Ex: Industrie, Agriculture..." value={form.category} onChange={update('category')} />
            </div>
          </div>

          <div>
            <label className="input-label">Technologies / Tags</label>
            <input className="input-field" placeholder="Arduino, ESP32, LIDAR (séparés par des virgules)" value={form.tags} onChange={update('tags')} />
          </div>

          <div style={{ display: 'flex', gap: 10, paddingTop: 8, borderTop: '1.5px solid var(--border)' }}>
            <button type="button" className="btn btn-secondary" style={{ flex: 1 }} onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" style={{ flex: 2 }}>
              <Plus size={15} /> Créer le projet
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

export function DashboardPage({ projects, onSelectProject, onNavigate, onCreateProject }: {
  projects: Project[]
  onSelectProject: (p: Project) => void
  onNavigate: (page: 'dashboard' | 'project-detail') => void
  onCreateProject: (p: Partial<Project>) => void
}) {
  const [showCreate, setShowCreate] = useState(false)
  const [filter, setFilter] = useState<'all' | ProjectStatus>('all')

  const total = projects.length
  const inProgress = projects.filter((p) => p.status === 'in-progress').length
  const completed = projects.filter((p) => p.status === 'completed').length
  const totalResources = projects.reduce((s, p) => s + p.resources.length, 0)

  const filtered = filter === 'all' ? projects : projects.filter((p) => p.status === filter)

  return (
    <div className="page-enter">
      {showCreate && (
        <CreateProjectModal
          onClose={() => setShowCreate(false)}
          onCreate={onCreateProject}
        />
      )}

      {/* Welcome */}
      <div style={{ marginBottom: 28 }}>
        <h1 style={{ fontSize: 22, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.025em', margin: 0 }}>
          Tableau de bord
        </h1>
        <p style={{ fontSize: 14, color: 'var(--text-muted)', marginTop: 4 }}>
          Gérez et suivez l'avancement de tous vos projets robotiques.
        </p>
      </div>

      {/* Stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16, marginBottom: 28 }}>
        <StatCard icon={<FolderOpen size={19} />} label="Projets totaux" value={total} sub="Tous statuts" />
        <StatCard icon={<BarChart2 size={19} />} label="En cours" value={inProgress} sub="Actifs" color="#2563eb" />
        <StatCard icon={<CheckCircle2 size={19} />} label="Terminés" value={completed} sub="Validés" color="var(--success)" />
        <StatCard icon={<FolderOpen size={19} />} label="Ressources" value={totalResources} sub="Fichiers totaux" color="#7c3aed" />
      </div>

      {/* Filter + Create */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 18 }}>
        <div style={{ display: 'flex', gap: 6 }}>
          {(['all', 'in-progress', 'testing', 'completed', 'draft'] as const).map((f) => (
            <button
              key={f}
              onClick={() => setFilter(f)}
              className="btn btn-sm"
              style={{
                background: filter === f ? 'var(--primary)' : 'white',
                color: filter === f ? 'white' : 'var(--text-secondary)',
                borderColor: filter === f ? 'var(--primary)' : 'var(--border)',
              }}
            >
              {f === 'all' ? 'Tous' : statusLabels[f]}
              {f !== 'all' && (
                <span style={{ fontSize: 11, padding: '0px 5px', borderRadius: 3, background: filter === f ? 'rgba(255,255,255,0.2)' : 'var(--surface-2)', fontWeight: 700 }}>
                  {projects.filter((p) => p.status === f).length}
                </span>
              )}
            </button>
          ))}
        </div>
        <button className="btn btn-primary" onClick={() => setShowCreate(true)}>
          <Plus size={15} /> Nouveau projet
        </button>
      </div>

      {/* Projects grid */}
      {filtered.length === 0 ? (
        <div style={{ textAlign: 'center', padding: '60px 24px' }}>
          <div style={{ width: 56, height: 56, background: 'var(--surface-2)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
            <FolderOpen size={24} color="var(--text-muted)" />
          </div>
          <h3 style={{ fontSize: 15, fontWeight: 700, color: 'var(--text-primary)', marginBottom: 8 }}>Aucun projet trouvé</h3>
          <p style={{ fontSize: 13.5, color: 'var(--text-muted)' }}>Créez votre premier projet pour commencer.</p>
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(340px, 1fr))', gap: 16 }}>
          {filtered.map((p) => (
            <ProjectCard key={p.id} project={p} onClick={() => { onSelectProject(p); onNavigate('project-detail') }} />
          ))}
        </div>
      )}
    </div>
  )
}
