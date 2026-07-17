import { useState } from 'react'
import { Bot, Mail, Lock, Eye, EyeOff, User, Building2, ArrowLeft, CheckCircle2, AlertCircle } from 'lucide-react'
import type { Page } from '../../types'

interface AuthPagesProps {
  page: Page
  onNavigate: (page: Page) => void
  onLogin: () => void
}

function AuthLayout({ children, title, subtitle }: { children: React.ReactNode; title: string; subtitle: string }) {
  return (
    <div style={{ minHeight: '100vh', background: 'var(--bg)', display: 'flex' }}>
      {/* Left panel */}
      <div
        style={{
          width: 420,
          minWidth: 420,
          background: 'linear-gradient(145deg, #1e3a8a 0%, #1d4ed8 45%, #2563eb 100%)',
          display: 'flex',
          flexDirection: 'column',
          padding: '48px 40px',
          position: 'relative',
          overflow: 'hidden',
        }}
        className="hidden lg:flex"
      >
        {/* Subtle dot grid */}
        <div
          style={{
            position: 'absolute',
            inset: 0,
            backgroundImage: 'radial-gradient(circle, rgba(255,255,255,0.08) 1px, transparent 1px)',
            backgroundSize: '24px 24px',
          }}
        />
        {/* Glow circle */}
        <div
          style={{
            position: 'absolute',
            right: -80,
            top: '30%',
            width: 320,
            height: 320,
            background: 'rgba(99,179,237,0.15)',
            borderRadius: '50%',
            filter: 'blur(60px)',
          }}
        />

        <div style={{ position: 'relative', zIndex: 1 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 64 }}>
            <div style={{ width: 36, height: 36, background: 'rgba(255,255,255,0.15)', borderRadius: 9, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Bot size={20} color="white" />
            </div>
            <span style={{ fontSize: 17, fontWeight: 700, color: 'white', letterSpacing: '-0.02em' }}>RoboForge</span>
          </div>

          <div>
            <h2 style={{ fontSize: 28, fontWeight: 800, color: 'white', lineHeight: 1.3, marginBottom: 12, letterSpacing: '-0.03em' }}>
              Gérez vos projets<br />robotiques avec<br />précision.
            </h2>
            <p style={{ fontSize: 14.5, color: 'rgba(255,255,255,0.65)', lineHeight: 1.7 }}>
              Cahier des charges, ressources techniques, collaboration d'équipe et intégration GitHub — tout en un seul endroit.
            </p>
          </div>

          <div style={{ marginTop: 56, display: 'flex', flexDirection: 'column', gap: 18 }}>
            {[
              { icon: '📋', title: 'Cahier des Charges Guidé', desc: '12 étapes structurées pour documenter votre projet' },
              { icon: '🔧', title: 'Gestion des Ressources', desc: 'Fichiers CAO, schémas, code et plus encore' },
              { icon: '⚡', title: 'Collaboration en Équipe', desc: 'Rôles, permissions et suivi en temps réel' },
            ].map((f) => (
              <div key={f.title} style={{ display: 'flex', gap: 14, alignItems: 'flex-start' }}>
                <div style={{ width: 36, height: 36, background: 'rgba(255,255,255,0.1)', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 16, flexShrink: 0 }}>{f.icon}</div>
                <div>
                  <div style={{ fontSize: 13.5, fontWeight: 600, color: 'white' }}>{f.title}</div>
                  <div style={{ fontSize: 12.5, color: 'rgba(255,255,255,0.55)', marginTop: 2 }}>{f.desc}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Right panel */}
      <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '32px 24px' }}>
        <div style={{ width: '100%', maxWidth: 400 }}>
          {/* Mobile logo */}
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 36, justifyContent: 'center' }} className="flex lg:hidden">
            <div style={{ width: 32, height: 32, background: 'var(--primary)', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Bot size={18} color="white" />
            </div>
            <span style={{ fontSize: 16, fontWeight: 700, color: 'var(--text-primary)' }}>RoboForge</span>
          </div>

          <div style={{ marginBottom: 28 }}>
            <h1 style={{ fontSize: 22, fontWeight: 800, color: 'var(--text-primary)', marginBottom: 6, letterSpacing: '-0.025em' }}>{title}</h1>
            <p style={{ fontSize: 13.5, color: 'var(--text-muted)', lineHeight: 1.6 }}>{subtitle}</p>
          </div>

          {children}
        </div>
      </div>
    </div>
  )
}

function InputField({
  label,
  type = 'text',
  placeholder,
  value,
  onChange,
  icon,
  required,
  error,
  hint,
}: {
  label: string
  type?: string
  placeholder?: string
  value: string
  onChange: (v: string) => void
  icon?: React.ReactNode
  required?: boolean
  error?: string
  hint?: string
}) {
  const [showPwd, setShowPwd] = useState(false)
  const isPassword = type === 'password'

  return (
    <div>
      <label className="input-label">
        {label}
        {required && <span style={{ color: 'var(--primary)', marginLeft: 3 }}>*</span>}
      </label>
      <div style={{ position: 'relative' }}>
        {icon && (
          <div style={{ position: 'absolute', left: 13, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)', display: 'flex' }}>
            {icon}
          </div>
        )}
        <input
          className="input-field"
          style={{ paddingLeft: icon ? 40 : 14, paddingRight: isPassword ? 40 : 14, borderColor: error ? 'var(--danger)' : undefined }}
          type={isPassword ? (showPwd ? 'text' : 'password') : type}
          placeholder={placeholder}
          value={value}
          onChange={(e) => onChange(e.target.value)}
        />
        {isPassword && (
          <button
            type="button"
            onClick={() => setShowPwd(!showPwd)}
            style={{ position: 'absolute', right: 13, top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-muted)', display: 'flex', padding: 0 }}
          >
            {showPwd ? <EyeOff size={16} /> : <Eye size={16} />}
          </button>
        )}
      </div>
      {error && (
        <div style={{ display: 'flex', alignItems: 'center', gap: 5, marginTop: 5, color: 'var(--danger)', fontSize: 12 }}>
          <AlertCircle size={13} /> {error}
        </div>
      )}
      {hint && !error && <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 5 }}>{hint}</div>}
    </div>
  )
}

export function LoginPage({ onNavigate, onLogin }: AuthPagesProps) {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    setTimeout(() => { setLoading(false); onLogin() }, 900)
  }

  return (
    <AuthLayout title="Bienvenue" subtitle="Connectez-vous à votre espace de gestion de projets robotiques.">
      <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
        <InputField
          label="Adresse e-mail"
          type="email"
          placeholder="vous@exemple.dz"
          value={email}
          onChange={setEmail}
          icon={<Mail size={16} />}
          required
        />
        <InputField
          label="Mot de passe"
          type="password"
          placeholder="••••••••"
          value={password}
          onChange={setPassword}
          icon={<Lock size={16} />}
          required
        />

        <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
          <button
            type="button"
            onClick={() => onNavigate('forgot-password')}
            style={{ background: 'none', border: 'none', color: 'var(--primary)', fontSize: 13, cursor: 'pointer', padding: 0, fontWeight: 500 }}
          >
            Mot de passe oublié ?
          </button>
        </div>

        <button type="submit" className="btn btn-primary btn-lg" style={{ width: '100%', marginTop: 4 }} disabled={loading}>
          {loading ? (
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}>
              <span style={{ width: 16, height: 16, border: '2.5px solid rgba(255,255,255,0.3)', borderTopColor: 'white', borderRadius: '50%', animation: 'spin 0.7s linear infinite', display: 'inline-block' }} />
              Connexion...
            </span>
          ) : 'Se connecter'}
        </button>

        <div style={{ textAlign: 'center', fontSize: 13, color: 'var(--text-muted)', marginTop: 4 }}>
          Pas encore de compte ?{' '}
          <button
            type="button"
            onClick={() => onNavigate('register')}
            style={{ background: 'none', border: 'none', color: 'var(--primary)', cursor: 'pointer', fontWeight: 600, padding: 0, fontSize: 13 }}
          >
            Créer un compte
          </button>
        </div>
      </form>

      <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
    </AuthLayout>
  )
}

export function RegisterPage({ onNavigate }: AuthPagesProps) {
  const [form, setForm] = useState({ name: '', email: '', org: '', password: '', confirm: '' })
  const [loading, setLoading] = useState(false)
  const [errors, setErrors] = useState<Record<string, string>>({})

  const update = (key: string) => (v: string) => setForm((f) => ({ ...f, [key]: v }))

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    const errs: Record<string, string> = {}
    if (!form.name.trim()) errs.name = 'Le nom est requis'
    if (!form.email.includes('@')) errs.email = 'E-mail invalide'
    if (form.password.length < 8) errs.password = 'Minimum 8 caractères'
    if (form.password !== form.confirm) errs.confirm = 'Les mots de passe ne correspondent pas'
    if (Object.keys(errs).length) { setErrors(errs); return }
    setLoading(true)
    setTimeout(() => { setLoading(false); onNavigate('login') }, 1200)
  }

  return (
    <AuthLayout title="Créer un compte" subtitle="Rejoignez RoboForge pour gérer vos projets robotiques en équipe.">
      <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
        <InputField label="Nom complet" placeholder="Ahmed Benali" value={form.name} onChange={update('name')} icon={<User size={16} />} required error={errors.name} />
        <InputField label="Adresse e-mail" type="email" placeholder="vous@exemple.dz" value={form.email} onChange={update('email')} icon={<Mail size={16} />} required error={errors.email} />
        <InputField label="Établissement / Organisation" placeholder="Lycée Technique d'Alger" value={form.org} onChange={update('org')} icon={<Building2 size={16} />} />
        <InputField label="Mot de passe" type="password" placeholder="••••••••" value={form.password} onChange={update('password')} icon={<Lock size={16} />} required error={errors.password} hint="Minimum 8 caractères" />
        <InputField label="Confirmer le mot de passe" type="password" placeholder="••••••••" value={form.confirm} onChange={update('confirm')} icon={<Lock size={16} />} required error={errors.confirm} />

        <div style={{ background: 'var(--surface-2)', borderRadius: 7, padding: '10px 13px', fontSize: 12.5, color: 'var(--text-muted)', marginTop: 2 }}>
          En créant un compte, vous acceptez les{' '}
          <span style={{ color: 'var(--primary)', cursor: 'pointer' }}>Conditions d'utilisation</span>{' '}
          et la{' '}
          <span style={{ color: 'var(--primary)', cursor: 'pointer' }}>Politique de confidentialité</span>.
        </div>

        <button type="submit" className="btn btn-primary btn-lg" style={{ width: '100%' }} disabled={loading}>
          {loading ? 'Création du compte...' : 'Créer mon compte'}
        </button>

        <div style={{ textAlign: 'center', fontSize: 13, color: 'var(--text-muted)' }}>
          Déjà inscrit ?{' '}
          <button type="button" onClick={() => onNavigate('login')} style={{ background: 'none', border: 'none', color: 'var(--primary)', cursor: 'pointer', fontWeight: 600, padding: 0, fontSize: 13 }}>
            Se connecter
          </button>
        </div>
      </form>
    </AuthLayout>
  )
}

export function ForgotPasswordPage({ onNavigate }: AuthPagesProps) {
  const [email, setEmail] = useState('')
  const [sent, setSent] = useState(false)
  const [loading, setLoading] = useState(false)

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    setTimeout(() => { setLoading(false); setSent(true) }, 1000)
  }

  return (
    <AuthLayout title="Mot de passe oublié" subtitle="Saisissez votre adresse e-mail pour recevoir un lien de réinitialisation.">
      {!sent ? (
        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <InputField label="Adresse e-mail" type="email" placeholder="vous@exemple.dz" value={email} onChange={setEmail} icon={<Mail size={16} />} required />

          <button type="submit" className="btn btn-primary btn-lg" style={{ width: '100%' }} disabled={loading}>
            {loading ? 'Envoi en cours...' : 'Envoyer le lien'}
          </button>

          <button
            type="button"
            onClick={() => onNavigate('login')}
            className="btn btn-ghost"
            style={{ width: '100%', color: 'var(--text-secondary)' }}
          >
            <ArrowLeft size={15} /> Retour à la connexion
          </button>
        </form>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 20, textAlign: 'center', paddingTop: 8 }}>
          <div style={{ width: 60, height: 60, background: 'var(--success-light)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <CheckCircle2 size={28} color="var(--success)" />
          </div>
          <div>
            <h3 style={{ fontSize: 16, fontWeight: 700, color: 'var(--text-primary)', marginBottom: 8 }}>E-mail envoyé !</h3>
            <p style={{ fontSize: 13.5, color: 'var(--text-muted)', lineHeight: 1.6 }}>
              Un lien de réinitialisation a été envoyé à<br />
              <strong style={{ color: 'var(--text-primary)' }}>{email}</strong>
              <br />Vérifiez votre boîte de réception.
            </p>
          </div>
          <button type="button" onClick={() => onNavigate('login')} className="btn btn-secondary" style={{ width: '100%' }}>
            <ArrowLeft size={15} /> Retour à la connexion
          </button>
          <p style={{ fontSize: 12.5, color: 'var(--text-muted)' }}>
            Pas reçu ?{' '}
            <button onClick={() => setSent(false)} style={{ background: 'none', border: 'none', color: 'var(--primary)', cursor: 'pointer', fontSize: 12.5, padding: 0 }}>
              Renvoyer
            </button>
          </p>
        </div>
      )}
    </AuthLayout>
  )
}

export function ResetPasswordPage({ onNavigate }: AuthPagesProps) {
  const [form, setForm] = useState({ password: '', confirm: '' })
  const [done, setDone] = useState(false)
  const [loading, setLoading] = useState(false)
  const [errors, setErrors] = useState<Record<string, string>>({})

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    const errs: Record<string, string> = {}
    if (form.password.length < 8) errs.password = 'Minimum 8 caractères'
    if (form.password !== form.confirm) errs.confirm = 'Les mots de passe ne correspondent pas'
    if (Object.keys(errs).length) { setErrors(errs); return }
    setLoading(true)
    setTimeout(() => { setLoading(false); setDone(true) }, 1000)
  }

  return (
    <AuthLayout title="Nouveau mot de passe" subtitle="Choisissez un nouveau mot de passe sécurisé pour votre compte.">
      {!done ? (
        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <InputField label="Nouveau mot de passe" type="password" placeholder="••••••••" value={form.password} onChange={(v) => setForm((f) => ({ ...f, password: v }))} icon={<Lock size={16} />} required error={errors.password} hint="Minimum 8 caractères" />
          <InputField label="Confirmer le mot de passe" type="password" placeholder="••••••••" value={form.confirm} onChange={(v) => setForm((f) => ({ ...f, confirm: v }))} icon={<Lock size={16} />} required error={errors.confirm} />

          {/* Password strength */}
          <div style={{ display: 'flex', gap: 5, marginTop: -4 }}>
            {[1,2,3,4].map((i) => {
              const len = form.password.length
              const active = (i === 1 && len >= 1) || (i === 2 && len >= 6) || (i === 3 && len >= 10) || (i === 4 && len >= 14)
              const colors = ['var(--danger)', 'var(--warning)', 'var(--primary)', 'var(--success)']
              return <div key={i} style={{ flex: 1, height: 3, borderRadius: 99, background: active ? colors[i-1] : 'var(--border)', transition: 'background 0.2s' }} />
            })}
          </div>

          <button type="submit" className="btn btn-primary btn-lg" style={{ width: '100%', marginTop: 8 }} disabled={loading}>
            {loading ? 'Modification...' : 'Réinitialiser le mot de passe'}
          </button>
        </form>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 20, textAlign: 'center', paddingTop: 8 }}>
          <div style={{ width: 60, height: 60, background: 'var(--success-light)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <CheckCircle2 size={28} color="var(--success)" />
          </div>
          <div>
            <h3 style={{ fontSize: 16, fontWeight: 700, color: 'var(--text-primary)', marginBottom: 8 }}>Mot de passe modifié !</h3>
            <p style={{ fontSize: 13.5, color: 'var(--text-muted)', lineHeight: 1.6 }}>
              Votre mot de passe a été réinitialisé avec succès.
            </p>
          </div>
          <button type="button" onClick={() => onNavigate('login')} className="btn btn-primary btn-lg" style={{ width: '100%' }}>
            Se connecter
          </button>
        </div>
      )}
    </AuthLayout>
  )
}
