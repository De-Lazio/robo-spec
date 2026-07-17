import { useState } from 'react'
import {
  FileText, Layers, GitBranch, Users, Settings, Eye,
  Plus, Upload, File, FileImage, FileCode, Star, GitFork,
  AlertCircle, GitCommit, ExternalLink, Link, CheckCircle2,
  Clock, Cpu, Code2, FileArchive, Trash2, Download, Search,
  X, Mail, ChevronDown, BarChart2, Zap, Wrench, Bot,
} from 'lucide-react'
import type { Project, ProjectTab, Resource, ResourceCategory, TeamMember } from '../../types'
import { Avatar, statusColors, statusLabels } from '../layout/AppLayout'

const typeLabels: Record<string, string> = { mobile: 'Robot Mobile', arm: 'Bras Robotisé', drone: 'Drone', fixed: 'Robot Fixe', humanoid: 'Humanoïde', other: 'Autre' }
const typeIcons: Record<string, React.ReactNode> = { mobile: <Bot size={16} />, arm: <Wrench size={16} />, drone: <Zap size={16} />, fixed: <Cpu size={16} />, humanoid: <Bot size={16} />, other: <Code2 size={16} /> }

function formatSize(bytes: number) {
  if (bytes < 1024) return `${bytes} o`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} Ko`
  return `${(bytes / 1024 / 1024).toFixed(1)} Mo`
}

function resourceIcon(type: Resource['type']) {
  const icons: Record<string, { icon: React.ReactNode; color: string; bg: string }> = {
    image: { icon: <FileImage size={16} />, color: '#7c3aed', bg: '#f5f3ff' },
    pdf: { icon: <File size={16} />, color: '#dc2626', bg: '#fef2f2' },
    code: { icon: <FileCode size={16} />, color: '#0891b2', bg: '#ecfeff' },
    cad: { icon: <Cpu size={16} />, color: '#d97706', bg: '#fffbeb' },
    schema: { icon: <Zap size={16} />, color: '#059669', bg: '#ecfdf5' },
    doc: { icon: <FileText size={16} />, color: '#2563eb', bg: '#eff6ff' },
    archive: { icon: <FileArchive size={16} />, color: '#6b7280', bg: '#f9fafb' },
    other: { icon: <File size={16} />, color: '#6b7280', bg: '#f9fafb' },
  }
  return icons[type] || icons.other
}

const roleLabels: Record<string, string> = { chef: 'Chef de projet', mécanique: 'Mécanique', électronique: 'Électronique', informatique: 'Informatique', autre: 'Autre' }
const roleColors: Record<string, string> = { chef: 'badge-blue', mécanique: 'badge-yellow', électronique: 'badge-green', informatique: 'badge-gray', autre: 'badge-gray' }

// ─── Overview Tab ───────────────────────────────────────────────────────────
function OverviewTab({ project }: { project: Project }) {
  const cdcSections = project.cdcData ? [
    { label: 'Informations Générales', done: !!project.cdcData.step1.projectName },
    { label: 'Contexte & Problématique', done: !!project.cdcData.step2.context },
    { label: 'Mission du Robot', done: project.cdcData.step3.missions.length > 0 },
    { label: 'Utilisateurs Cibles', done: project.cdcData.step4.users.length > 0 },
    { label: 'Fonctions Principales', done: project.cdcData.step5.functions.length > 0 },
    { label: 'Architecture', done: project.cdcData.step6.capteurs.length > 0 },
    { label: 'Contraintes', done: !!project.cdcData.step7.maxBudget },
    { label: 'Performances', done: project.cdcData.step8.criteria.length > 0 },
    { label: 'Schéma Fonctionnel', done: !!project.cdcData.step9.missionLabel },
    { label: 'Matériel', done: project.cdcData.step10.materials.length > 0 },
    { label: 'Planning', done: project.cdcData.step11.planning.length > 0 },
    { label: 'Résultat Attendu', done: !!project.cdcData.step12.expectedResult },
  ] : []
  const cdcDone = cdcSections.filter((s) => s.done).length

  return (
    <div style={{ display: 'grid', gridTemplateColumns: '1fr 320px', gap: 20 }}>
      {/* Left */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
        {/* Project overview card */}
        <div className="card" style={{ padding: '20px 24px' }}>
          <h3 style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)', margin: '0 0 12px', display: 'flex', alignItems: 'center', gap: 7 }}>
            <BarChart2 size={15} color="var(--primary)" /> Vue d'ensemble
          </h3>
          <p style={{ fontSize: 13.5, color: 'var(--text-secondary)', lineHeight: 1.7, margin: 0 }}>{project.description}</p>

          <div style={{ marginTop: 18 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
              <span style={{ fontSize: 13, color: 'var(--text-muted)', fontWeight: 500 }}>Avancement global</span>
              <span style={{ fontSize: 13, fontWeight: 700, color: 'var(--primary)' }}>{project.progress}%</span>
            </div>
            <div className="progress-bar" style={{ height: 8, borderRadius: 8 }}>
              <div className="progress-fill" style={{ width: `${project.progress}%`, height: '100%', borderRadius: 8 }} />
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 12, marginTop: 20 }}>
            {[
              { label: 'Ressources', value: project.resources.length, color: '#7c3aed' },
              { label: 'Membres', value: project.team.length, color: 'var(--primary)' },
              { label: 'CDC', value: project.hasCDC ? `${cdcDone}/12` : 'Non créé', color: project.hasCDC ? 'var(--success)' : 'var(--text-muted)' },
            ].map((s) => (
              <div key={s.label} style={{ textAlign: 'center', padding: '12px 8px', background: 'var(--surface-2)', borderRadius: 8 }}>
                <div style={{ fontSize: 20, fontWeight: 800, color: s.color, letterSpacing: '-0.02em' }}>{s.value}</div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2, fontWeight: 500 }}>{s.label}</div>
              </div>
            ))}
          </div>
        </div>

        {/* GitHub snippet */}
        {project.githubRepo && (
          <div className="card" style={{ padding: '20px 24px' }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
              <h3 style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)', margin: 0, display: 'flex', alignItems: 'center', gap: 7 }}>
                <GitBranch size={15} color="var(--primary)" /> Dépôt GitHub
              </h3>
              <a href={project.githubRepo.url} target="_blank" rel="noreferrer" className="btn btn-ghost btn-sm" style={{ fontSize: 12 }}>
                <ExternalLink size={13} /> Ouvrir
              </a>
            </div>
            <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 12.5, color: 'var(--text-secondary)', marginBottom: 12 }}>
              {project.githubRepo.owner}/{project.githubRepo.name}
            </div>
            <div style={{ display: 'flex', gap: 16 }}>
              {[
                { icon: <Star size={13} />, value: project.githubRepo.stars, label: 'Étoiles' },
                { icon: <GitFork size={13} />, value: project.githubRepo.forks, label: 'Forks' },
                { icon: <AlertCircle size={13} />, value: project.githubRepo.openIssues, label: 'Issues' },
              ].map((s) => (
                <div key={s.label} style={{ display: 'flex', alignItems: 'center', gap: 5, fontSize: 13, color: 'var(--text-secondary)' }}>
                  {s.icon} {s.value} <span style={{ color: 'var(--text-muted)', fontSize: 12 }}>{s.label}</span>
                </div>
              ))}
            </div>
            <div style={{ marginTop: 14, borderTop: '1px solid var(--border)', paddingTop: 12 }}>
              <div style={{ fontSize: 12, color: 'var(--text-muted)', marginBottom: 8, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>Derniers commits</div>
              {project.githubRepo.commits.slice(0, 3).map((c) => (
                <div key={c.hash} style={{ display: 'flex', alignItems: 'flex-start', gap: 9, padding: '6px 0', borderBottom: '1px solid var(--border)' }}>
                  <GitCommit size={13} color="var(--text-muted)" style={{ marginTop: 2, flexShrink: 0 }} />
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ fontSize: 12.5, color: 'var(--text-primary)', fontWeight: 500, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{c.message}</div>
                    <div style={{ fontSize: 11.5, color: 'var(--text-muted)', marginTop: 2 }}>
                      <span className="mono" style={{ color: 'var(--primary)' }}>{c.hash}</span>
                      {' · '}{c.author}{' · '}{new Date(c.date).toLocaleDateString('fr-FR')}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Right */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
        {/* Info */}
        <div className="card" style={{ padding: '18px 20px' }}>
          <h3 style={{ fontWeight: 700, margin: '0 0 12px', textTransform: 'uppercase', letterSpacing: '0.04em', fontSize: 11.5, color: 'var(--text-muted)' }}>Informations</h3>
          {[
            { label: 'Statut', value: <span className={`badge ${statusColors[project.status]}`}>{statusLabels[project.status]}</span> },
            { label: 'Type', value: <span style={{ display: 'flex', alignItems: 'center', gap: 5, fontSize: 13 }}>{typeIcons[project.type]} {typeLabels[project.type]}</span> },
            { label: 'Domaine', value: project.category },
            { label: 'Créé le', value: new Date(project.createdAt).toLocaleDateString('fr-FR') },
            { label: 'Mis à jour', value: new Date(project.updatedAt).toLocaleDateString('fr-FR') },
          ].map((item) => (
            <div key={item.label} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '7px 0', borderBottom: '1px solid var(--border)' }}>
              <span style={{ fontSize: 12.5, color: 'var(--text-muted)', fontWeight: 500 }}>{item.label}</span>
              <span style={{ fontSize: 13, color: 'var(--text-primary)', fontWeight: 500 }}>{item.value}</span>
            </div>
          ))}
        </div>

        {/* Tags */}
        <div className="card" style={{ padding: '18px 20px' }}>
          <h3 style={{ fontSize: 11.5, fontWeight: 700, color: 'var(--text-muted)', margin: '0 0 10px', textTransform: 'uppercase', letterSpacing: '0.04em' }}>Technologies</h3>
          <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
            {project.tags.map((tag) => (
              <span key={tag} className="badge badge-blue" style={{ fontSize: 12 }}>{tag}</span>
            ))}
          </div>
        </div>

        {/* Team */}
        <div className="card" style={{ padding: '18px 20px' }}>
          <h3 style={{ fontSize: 11.5, fontWeight: 700, color: 'var(--text-muted)', margin: '0 0 12px', textTransform: 'uppercase', letterSpacing: '0.04em' }}>Équipe</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {project.team.map((m) => (
              <div key={m.id} style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                <Avatar name={m.name} size={28} />
                <div style={{ flex: 1 }}>
                  <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)' }}>{m.name}</div>
                  <div style={{ fontSize: 11.5, color: 'var(--text-muted)' }}>{roleLabels[m.role]}</div>
                </div>
                <span className={`badge ${roleColors[m.role]}`} style={{ fontSize: 11 }}>{m.role === 'chef' ? '★' : roleLabels[m.role].split(' ')[0]}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}

// ─── CDC Tab ─────────────────────────────────────────────────────────────────
function CDCTab({ project, onOpenWizard }: { project: Project; onOpenWizard: () => void }) {
  if (!project.hasCDC || !project.cdcData) {
    return (
      <div style={{ textAlign: 'center', padding: '60px 24px' }}>
        <div style={{ width: 64, height: 64, background: 'var(--primary-light)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 18px' }}>
          <FileText size={28} color="var(--primary)" />
        </div>
        <h3 style={{ fontSize: 17, fontWeight: 800, color: 'var(--text-primary)', marginBottom: 8 }}>Cahier des charges non créé</h3>
        <p style={{ fontSize: 14, color: 'var(--text-muted)', marginBottom: 24, maxWidth: 400, margin: '0 auto 24px', lineHeight: 1.7 }}>
          Le cahier des charges vous guide à travers 12 sections essentielles pour documenter votre projet robotique.
        </p>
        <button className="btn btn-primary btn-lg" onClick={onOpenWizard}>
          <Plus size={16} /> Créer le cahier des charges
        </button>
      </div>
    )
  }

  const cdc = project.cdcData
  const sections = [
    { num: 1, title: 'Informations Générales', preview: cdc.step1.projectName, done: !!cdc.step1.projectName },
    { num: 2, title: 'Contexte & Problématique', preview: cdc.step2.context?.slice(0, 80) + '...', done: !!cdc.step2.context },
    { num: 3, title: 'Mission du Robot', preview: `${cdc.step3.missions.length} mission(s) définie(s)`, done: cdc.step3.missions.length > 0 },
    { num: 4, title: 'Utilisateurs Cibles', preview: cdc.step4.users.join(', '), done: cdc.step4.users.length > 0 },
    { num: 5, title: 'Fonctions Principales', preview: `${cdc.step5.functions.length} fonction(s)`, done: cdc.step5.functions.length > 0 },
    { num: 6, title: 'Architecture du Robot', preview: `${cdc.step6.capteurs.length} capteur(s) · ${cdc.step6.controlUnit}`, done: cdc.step6.capteurs.length > 0 },
    { num: 7, title: 'Contraintes du Projet', preview: `Budget: ${cdc.step7.maxBudget}`, done: !!cdc.step7.maxBudget },
    { num: 8, title: 'Critères de Performance', preview: `${cdc.step8.criteria.length} critère(s)`, done: cdc.step8.criteria.length > 0 },
    { num: 9, title: 'Schéma Fonctionnel', preview: cdc.step9.missionLabel, done: !!cdc.step9.missionLabel },
    { num: 10, title: 'Matériel Nécessaire', preview: `${cdc.step10.materials.length} composant(s)`, done: cdc.step10.materials.length > 0 },
    { num: 11, title: 'Planning du Projet', preview: `${cdc.step11.planning.length} étape(s)`, done: cdc.step11.planning.length > 0 },
    { num: 12, title: 'Résultat Attendu', preview: cdc.step12.expectedResult?.slice(0, 80) + '...', done: !!cdc.step12.expectedResult },
  ]
  const done = sections.filter((s) => s.done).length

  return (
    <div>
      {/* Header */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 20 }}>
        <div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <h2 style={{ fontSize: 16, fontWeight: 800, color: 'var(--text-primary)', margin: 0 }}>Cahier des Charges</h2>
            <span className="badge badge-green" style={{ fontSize: 12 }}>{done}/12 sections</span>
          </div>
          <div style={{ marginTop: 8 }}>
            <div className="progress-bar" style={{ width: 240, height: 6 }}>
              <div className="progress-fill" style={{ width: `${(done / 12) * 100}%` }} />
            </div>
          </div>
        </div>
        <button className="btn btn-primary" onClick={onOpenWizard}>
          <FileText size={15} /> Modifier le CDC
        </button>
      </div>

      {/* Sections grid */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: 12 }}>
        {sections.map((s) => (
          <div key={s.num} className="card" style={{ padding: '16px 18px', display: 'flex', gap: 12, alignItems: 'flex-start', cursor: 'pointer', transition: 'border-color 0.15s' }}
            onMouseEnter={(e) => (e.currentTarget as HTMLElement).style.borderColor = '#bfdbfe'}
            onMouseLeave={(e) => (e.currentTarget as HTMLElement).style.borderColor = 'var(--border)'}
          >
            <div style={{ width: 28, height: 28, borderRadius: 7, background: s.done ? 'var(--success-light)' : 'var(--surface-2)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
              {s.done ? <CheckCircle2 size={14} color="var(--success)" /> : <span style={{ fontSize: 12, fontWeight: 700, color: 'var(--text-muted)' }}>{s.num}</span>}
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--text-primary)', marginBottom: 3 }}>{s.title}</div>
              <div style={{ fontSize: 12, color: 'var(--text-muted)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{s.preview || 'Non renseigné'}</div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

// ─── Resources Tab ───────────────────────────────────────────────────────────
function ResourcesTab({ project }: { project: Project }) {
  const [catFilter, setCatFilter] = useState<'all' | ResourceCategory>('all')
  const [search, setSearch] = useState('')

  const resources = project.resources.filter((r) => {
    const matchCat = catFilter === 'all' || r.category === catFilter
    const matchSearch = r.name.toLowerCase().includes(search.toLowerCase())
    return matchCat && matchSearch
  })

  const categories: Array<'all' | ResourceCategory> = ['all', 'mécanique', 'électronique', 'informatique', 'autre']
  const catLabels: Record<string, string> = { all: 'Tous', mécanique: 'Mécanique', électronique: 'Électronique', informatique: 'Informatique', autre: 'Autres' }
  const catCounts: Record<string, number> = { all: project.resources.length, mécanique: 0, électronique: 0, informatique: 0, autre: 0 }
  project.resources.forEach((r) => catCounts[r.category]++)

  return (
    <div>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 18 }}>
        <div style={{ display: 'flex', gap: 6 }}>
          {categories.map((c) => (
            <button
              key={c}
              onClick={() => setCatFilter(c)}
              className="btn btn-sm"
              style={{ background: catFilter === c ? 'var(--primary)' : 'white', color: catFilter === c ? 'white' : 'var(--text-secondary)', borderColor: catFilter === c ? 'var(--primary)' : 'var(--border)' }}
            >
              {catLabels[c]}
              <span style={{ fontSize: 11, padding: '0 4px', borderRadius: 3, background: catFilter === c ? 'rgba(255,255,255,0.2)' : 'var(--surface-2)', fontWeight: 700 }}>
                {catCounts[c]}
              </span>
            </button>
          ))}
        </div>
        <div style={{ display: 'flex', gap: 8 }}>
          <div style={{ position: 'relative' }}>
            <Search size={13} style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
            <input className="input-field" style={{ paddingLeft: 32, paddingTop: 7, paddingBottom: 7, fontSize: 13, width: 200 }} placeholder="Rechercher..." value={search} onChange={(e) => setSearch(e.target.value)} />
          </div>
          <button className="btn btn-primary">
            <Upload size={14} /> Ajouter
          </button>
        </div>
      </div>

      {/* Upload zone */}
      <div style={{ border: '2px dashed var(--border)', borderRadius: 10, padding: '24px', textAlign: 'center', marginBottom: 18, background: 'var(--surface-2)', cursor: 'pointer', transition: 'border-color 0.15s, background 0.15s' }}
        onMouseEnter={(e) => { (e.currentTarget as HTMLElement).style.borderColor = 'var(--primary)'; (e.currentTarget as HTMLElement).style.background = 'var(--primary-light)' }}
        onMouseLeave={(e) => { (e.currentTarget as HTMLElement).style.borderColor = 'var(--border)'; (e.currentTarget as HTMLElement).style.background = 'var(--surface-2)' }}
      >
        <Upload size={20} color="var(--text-muted)" style={{ marginBottom: 8 }} />
        <div style={{ fontSize: 13.5, fontWeight: 600, color: 'var(--text-secondary)' }}>Glissez vos fichiers ici</div>
        <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 3 }}>ou cliquez pour parcourir · STL, DXF, PDF, INO, PY, PNG, JPG...</div>
      </div>

      {/* Table */}
      <div className="card" style={{ overflow: 'hidden' }}>
        <table className="data-table">
          <thead>
            <tr>
              <th>Fichier</th>
              <th>Catégorie</th>
              <th>Taille</th>
              <th>Ajouté par</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {resources.map((r) => {
              const ri = resourceIcon(r.type)
              return (
                <tr key={r.id}>
                  <td>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                      <div style={{ width: 32, height: 32, borderRadius: 7, background: ri.bg, display: 'flex', alignItems: 'center', justifyContent: 'center', color: ri.color, flexShrink: 0 }}>
                        {ri.icon}
                      </div>
                      <div>
                        <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', fontFamily: 'JetBrains Mono, monospace' }}>{r.name}</div>
                        {r.description && <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>{r.description}</div>}
                      </div>
                    </div>
                  </td>
                  <td>
                    <span className={`badge ${r.category === 'mécanique' ? 'badge-yellow' : r.category === 'électronique' ? 'badge-green' : r.category === 'informatique' ? 'badge-blue' : 'badge-gray'}`} style={{ fontSize: 11.5 }}>
                      {r.category}
                    </span>
                  </td>
                  <td><span style={{ fontSize: 13, color: 'var(--text-secondary)', fontFamily: 'JetBrains Mono, monospace' }}>{formatSize(r.size)}</span></td>
                  <td><div style={{ display: 'flex', alignItems: 'center', gap: 6 }}><Avatar name={r.uploadedBy} size={22} /><span style={{ fontSize: 13, color: 'var(--text-secondary)' }}>{r.uploadedBy.split(' ')[0]}</span></div></td>
                  <td><span style={{ fontSize: 12.5, color: 'var(--text-muted)' }}>{new Date(r.uploadedAt).toLocaleDateString('fr-FR')}</span></td>
                  <td>
                    <div style={{ display: 'flex', gap: 4, justifyContent: 'flex-end' }}>
                      <button className="btn btn-ghost btn-icon btn-sm"><Download size={14} /></button>
                      <button className="btn btn-ghost btn-icon btn-sm" style={{ color: 'var(--danger)' }}><Trash2 size={14} /></button>
                    </div>
                  </td>
                </tr>
              )
            })}
          </tbody>
        </table>
        {resources.length === 0 && (
          <div style={{ textAlign: 'center', padding: '32px', color: 'var(--text-muted)', fontSize: 13 }}>Aucune ressource trouvée</div>
        )}
      </div>
    </div>
  )
}

// ─── GitHub Tab ───────────────────────────────────────────────────────────────
function GitHubTab({ project }: { project: Project }) {
  const [repoUrl, setRepoUrl] = useState('')

  if (!project.githubRepo) {
    return (
      <div style={{ textAlign: 'center', padding: '60px 24px', maxWidth: 480, margin: '0 auto' }}>
        <div style={{ width: 64, height: 64, background: '#f1f5f9', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 18px' }}>
          <GitBranch size={28} color="var(--text-muted)" />
        </div>
        <h3 style={{ fontSize: 17, fontWeight: 800, color: 'var(--text-primary)', marginBottom: 8 }}>Lier un dépôt GitHub</h3>
        <p style={{ fontSize: 14, color: 'var(--text-muted)', marginBottom: 24, lineHeight: 1.7 }}>
          Connectez votre dépôt GitHub pour voir les commits, branches et issues directement dans RoboForge.
        </p>
        <div style={{ display: 'flex', gap: 8 }}>
          <input className="input-field" placeholder="https://github.com/votre-org/votre-repo" value={repoUrl} onChange={(e) => setRepoUrl(e.target.value)} style={{ flex: 1 }} />
          <button className="btn btn-primary"><Link size={15} /> Lier</button>
        </div>
      </div>
    )
  }

  const repo = project.githubRepo

  return (
    <div style={{ display: 'grid', gridTemplateColumns: '1fr 280px', gap: 18 }}>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
        {/* Repo card */}
        <div className="card" style={{ padding: '20px 24px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
              <div style={{ width: 34, height: 34, background: '#0f172a', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <GitBranch size={16} color="white" />
              </div>
              <div>
                <div style={{ fontSize: 14.5, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'JetBrains Mono, monospace' }}>{repo.owner}/{repo.name}</div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 1 }}>{repo.language}</div>
              </div>
            </div>
            <a href={repo.url} target="_blank" rel="noreferrer" className="btn btn-secondary btn-sm"><ExternalLink size={13} /> GitHub</a>
          </div>
          <p style={{ fontSize: 13.5, color: 'var(--text-secondary)', margin: 0, lineHeight: 1.6 }}>{repo.description}</p>
          <div style={{ display: 'flex', gap: 20, marginTop: 16 }}>
            {[
              { icon: <Star size={14} />, value: repo.stars, label: 'étoiles' },
              { icon: <GitFork size={14} />, value: repo.forks, label: 'forks' },
              { icon: <AlertCircle size={14} />, value: repo.openIssues, label: 'issues ouvertes' },
            ].map((s) => (
              <div key={s.label} style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 13.5, color: 'var(--text-secondary)' }}>
                {s.icon} <strong style={{ color: 'var(--text-primary)' }}>{s.value}</strong> {s.label}
              </div>
            ))}
          </div>
        </div>

        {/* Commits */}
        <div className="card" style={{ padding: '18px 22px' }}>
          <h3 style={{ fontSize: 13.5, fontWeight: 700, color: 'var(--text-primary)', margin: '0 0 14px', display: 'flex', alignItems: 'center', gap: 7 }}>
            <GitCommit size={14} color="var(--primary)" /> Historique des commits
          </h3>
          <div style={{ display: 'flex', flexDirection: 'column' }}>
            {repo.commits.map((c, i) => (
              <div key={c.hash} style={{ display: 'flex', gap: 12, padding: '10px 0', borderBottom: i < repo.commits.length - 1 ? '1px solid var(--border)' : 'none' }}>
                <div style={{ width: 32, height: 32, borderRadius: 8, background: 'var(--surface-2)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                  <Avatar name={c.author} size={28} />
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ fontSize: 13.5, fontWeight: 600, color: 'var(--text-primary)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{c.message}</div>
                  <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 3, display: 'flex', alignItems: 'center', gap: 8 }}>
                    <span className="mono" style={{ color: 'var(--primary)', fontSize: 11.5 }}>{c.hash}</span>
                    <span>{c.author}</span>
                    <span>{new Date(c.date).toLocaleDateString('fr-FR')}</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Right: branches + info */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
        <div className="card" style={{ padding: '18px 20px' }}>
          <h3 style={{ fontSize: 11.5, fontWeight: 700, color: 'var(--text-muted)', margin: '0 0 12px', textTransform: 'uppercase', letterSpacing: '0.04em' }}>Branches ({repo.branches.length})</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
            {repo.branches.map((b) => (
              <div key={b} style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '7px 10px', borderRadius: 6, background: b === repo.defaultBranch ? 'var(--primary-light)' : 'var(--surface-2)' }}>
                <GitBranch size={13} color={b === repo.defaultBranch ? 'var(--primary)' : 'var(--text-muted)'} />
                <span style={{ fontSize: 12.5, fontFamily: 'JetBrains Mono, monospace', color: b === repo.defaultBranch ? 'var(--primary)' : 'var(--text-secondary)', fontWeight: 500, flex: 1 }}>{b}</span>
                {b === repo.defaultBranch && <span style={{ fontSize: 10.5, padding: '1px 6px', borderRadius: 3, background: 'var(--primary)', color: 'white', fontWeight: 600 }}>défaut</span>}
              </div>
            ))}
          </div>
        </div>

        <div className="card" style={{ padding: '18px 20px' }}>
          <h3 style={{ fontSize: 11.5, fontWeight: 700, color: 'var(--text-muted)', margin: '0 0 12px', textTransform: 'uppercase', letterSpacing: '0.04em' }}>Infos du dépôt</h3>
          {[
            { label: 'Dernier commit', value: new Date(repo.lastCommit).toLocaleDateString('fr-FR') },
            { label: 'Branche défaut', value: repo.defaultBranch },
            { label: 'Langage principal', value: repo.language },
          ].map((item) => (
            <div key={item.label} style={{ display: 'flex', justifyContent: 'space-between', padding: '7px 0', borderBottom: '1px solid var(--border)', fontSize: 13 }}>
              <span style={{ color: 'var(--text-muted)', fontWeight: 500 }}>{item.label}</span>
              <span style={{ color: 'var(--text-primary)', fontFamily: 'JetBrains Mono, monospace', fontSize: 12.5 }}>{item.value}</span>
            </div>
          ))}
          <button className="btn btn-danger btn-sm" style={{ marginTop: 14, width: '100%' }}>
            <X size={13} /> Délier le dépôt
          </button>
        </div>
      </div>
    </div>
  )
}

// ─── Team Tab ────────────────────────────────────────────────────────────────
function TeamTab({ project }: { project: Project }) {
  const [showInvite, setShowInvite] = useState(false)
  const [inviteEmail, setInviteEmail] = useState('')
  const [inviteRole, setInviteRole] = useState('informatique')

  return (
    <div>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 18 }}>
        <div>
          <h2 style={{ fontSize: 16, fontWeight: 800, color: 'var(--text-primary)', margin: '0 0 3px' }}>Équipe du projet</h2>
          <p style={{ fontSize: 13, color: 'var(--text-muted)', margin: 0 }}>{project.team.length} membre(s)</p>
        </div>
        <button className="btn btn-primary" onClick={() => setShowInvite(!showInvite)}>
          <Plus size={15} /> Inviter un membre
        </button>
      </div>

      {showInvite && (
        <div className="card" style={{ padding: '18px 22px', marginBottom: 16, background: 'var(--primary-light)', borderColor: '#bfdbfe' }}>
          <h3 style={{ fontSize: 13.5, fontWeight: 700, color: 'var(--primary)', margin: '0 0 14px' }}>Inviter un nouveau membre</h3>
          <div style={{ display: 'flex', gap: 10 }}>
            <div style={{ position: 'relative', flex: 1 }}>
              <Mail size={14} style={{ position: 'absolute', left: 11, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
              <input className="input-field" style={{ paddingLeft: 34 }} placeholder="adresse@exemple.dz" value={inviteEmail} onChange={(e) => setInviteEmail(e.target.value)} />
            </div>
            <select className="input-field" style={{ width: 180 }} value={inviteRole} onChange={(e) => setInviteRole(e.target.value)}>
              <option value="chef">Chef de projet</option>
              <option value="mécanique">Mécanique</option>
              <option value="électronique">Électronique</option>
              <option value="informatique">Informatique</option>
              <option value="autre">Autre</option>
            </select>
            <button className="btn btn-primary"><Mail size={14} /> Envoyer</button>
            <button className="btn btn-secondary" onClick={() => setShowInvite(false)}><X size={14} /></button>
          </div>
        </div>
      )}

      <div className="card" style={{ overflow: 'hidden' }}>
        <table className="data-table">
          <thead>
            <tr>
              <th>Membre</th>
              <th>Rôle</th>
              <th>Email</th>
              <th>Rejoint le</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {project.team.map((m) => (
              <tr key={m.id}>
                <td>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    <Avatar name={m.name} size={34} />
                    <div>
                      <div style={{ fontSize: 13.5, fontWeight: 600, color: 'var(--text-primary)' }}>{m.name}</div>
                    </div>
                  </div>
                </td>
                <td><span className={`badge ${roleColors[m.role]}`}>{roleLabels[m.role]}</span></td>
                <td><span style={{ fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'JetBrains Mono, monospace' }}>{m.email}</span></td>
                <td><span style={{ fontSize: 12.5, color: 'var(--text-muted)' }}>{new Date(m.joinedAt).toLocaleDateString('fr-FR')}</span></td>
                <td>
                  <div style={{ display: 'flex', gap: 4, justifyContent: 'flex-end' }}>
                    <button className="btn btn-ghost btn-icon btn-sm"><Settings size={14} /></button>
                    <button className="btn btn-ghost btn-icon btn-sm" style={{ color: 'var(--danger)' }}><Trash2 size={14} /></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}

// ─── Settings Tab ─────────────────────────────────────────────────────────────
function SettingsTab({ project }: { project: Project }) {
  const [form, setForm] = useState({ name: project.name, description: project.description, status: project.status })

  return (
    <div style={{ maxWidth: 580 }}>
      <div className="card" style={{ padding: '24px 28px', marginBottom: 16 }}>
        <h3 style={{ fontSize: 14.5, fontWeight: 800, color: 'var(--text-primary)', margin: '0 0 20px' }}>Informations du projet</h3>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <div>
            <label className="input-label">Nom du projet</label>
            <input className="input-field" value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} />
          </div>
          <div>
            <label className="input-label">Description</label>
            <textarea className="input-field" style={{ resize: 'vertical', minHeight: 90 }} value={form.description} onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))} />
          </div>
          <div>
            <label className="input-label">Statut</label>
            <select className="input-field" value={form.status} onChange={(e) => setForm((f) => ({ ...f, status: e.target.value as any }))}>
              <option value="draft">Brouillon</option>
              <option value="in-progress">En cours</option>
              <option value="testing">Tests</option>
              <option value="completed">Terminé</option>
              <option value="archived">Archivé</option>
            </select>
          </div>
          <button className="btn btn-primary" style={{ alignSelf: 'flex-start' }}>
            <CheckCircle2 size={15} /> Sauvegarder
          </button>
        </div>
      </div>

      <div className="card" style={{ padding: '20px 24px', borderColor: '#fecaca' }}>
        <h3 style={{ fontSize: 14, fontWeight: 700, color: 'var(--danger)', margin: '0 0 10px' }}>Zone dangereuse</h3>
        <p style={{ fontSize: 13.5, color: 'var(--text-muted)', margin: '0 0 14px', lineHeight: 1.6 }}>La suppression d'un projet est irréversible. Toutes les données, ressources et le cahier des charges seront définitivement effacés.</p>
        <button className="btn btn-danger">
          <Trash2 size={14} /> Supprimer ce projet
        </button>
      </div>
    </div>
  )
}

// ─── Main ProjectDetailPage ───────────────────────────────────────────────────
export function ProjectDetailPage({ project, onOpenCDC, onBack }: {
  project: Project
  onOpenCDC: () => void
  onBack: () => void
}) {
  const [activeTab, setActiveTab] = useState<ProjectTab>('overview')

  const tabs: Array<{ id: ProjectTab; label: string; icon: React.ReactNode }> = [
    { id: 'overview', label: 'Aperçu', icon: <Eye size={14} /> },
    { id: 'cdc', label: 'Cahier des Charges', icon: <FileText size={14} /> },
    { id: 'resources', label: `Ressources (${project.resources.length})`, icon: <Layers size={14} /> },
    { id: 'github', label: 'GitHub', icon: <GitBranch size={14} /> },
    { id: 'team', label: `Équipe (${project.team.length})`, icon: <Users size={14} /> },
    { id: 'settings', label: 'Paramètres', icon: <Settings size={14} /> },
  ]

  return (
    <div className="page-enter">
      {/* Project header */}
      <div style={{ marginBottom: 22 }}>
        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 14 }}>
          <div style={{ display: 'flex', alignItems: 'flex-start', gap: 14 }}>
            <div style={{ width: 50, height: 50, borderRadius: 13, background: project.status === 'completed' ? 'var(--success-light)' : 'var(--primary-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: project.status === 'completed' ? 'var(--success)' : 'var(--primary)', flexShrink: 0, fontSize: 22 }}>
              {project.type === 'arm' ? '🦾' : project.type === 'drone' ? '🚁' : project.type === 'mobile' ? '🤖' : '⚙️'}
            </div>
            <div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 4 }}>
                <h1 style={{ fontSize: 20, fontWeight: 800, color: 'var(--text-primary)', margin: 0, letterSpacing: '-0.025em' }}>{project.name}</h1>
                <span className={`badge ${statusColors[project.status]}`}>{statusLabels[project.status]}</span>
                {project.hasCDC && <span className="badge badge-green" style={{ fontSize: 11 }}><CheckCircle2 size={11} /> CDC</span>}
              </div>
              <p style={{ fontSize: 13.5, color: 'var(--text-muted)', margin: 0 }}>{project.category} · {typeLabels[project.type]} · Mis à jour le {new Date(project.updatedAt).toLocaleDateString('fr-FR')}</p>
            </div>
          </div>
          <div style={{ display: 'flex', gap: 8 }}>
            {activeTab === 'cdc' && (
              <button className="btn btn-primary btn-sm" onClick={onOpenCDC}>
                <FileText size={14} /> {project.hasCDC ? 'Modifier CDC' : 'Créer CDC'}
              </button>
            )}
          </div>
        </div>

        {/* Progress bar */}
        <div style={{ background: 'white', borderRadius: 8, padding: '12px 18px', border: '1.5px solid var(--border)', display: 'flex', alignItems: 'center', gap: 14 }}>
          <span style={{ fontSize: 12.5, color: 'var(--text-muted)', fontWeight: 600, whiteSpace: 'nowrap' }}>Avancement global</span>
          <div className="progress-bar" style={{ flex: 1 }}>
            <div className="progress-fill" style={{ width: `${project.progress}%` }} />
          </div>
          <span style={{ fontSize: 13, fontWeight: 700, color: 'var(--primary)', whiteSpace: 'nowrap' }}>{project.progress}%</span>
        </div>
      </div>

      {/* Tabs */}
      <div className="tab-nav" style={{ marginBottom: 22 }}>
        {tabs.map((t) => (
          <button key={t.id} className={`tab-item${activeTab === t.id ? ' active' : ''}`} style={{ border: 'none', background: 'none', cursor: 'pointer' }} onClick={() => setActiveTab(t.id)}>
            {t.icon} {t.label}
          </button>
        ))}
      </div>

      {/* Tab content */}
      {activeTab === 'overview' && <OverviewTab project={project} />}
      {activeTab === 'cdc' && <CDCTab project={project} onOpenWizard={onOpenCDC} />}
      {activeTab === 'resources' && <ResourcesTab project={project} />}
      {activeTab === 'github' && <GitHubTab project={project} />}
      {activeTab === 'team' && <TeamTab project={project} />}
      {activeTab === 'settings' && <SettingsTab project={project} />}
    </div>
  )
}
