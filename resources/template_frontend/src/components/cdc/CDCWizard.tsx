import { useState } from 'react'
import { ChevronLeft, ChevronRight, Plus, Trash2, Check, CheckCircle2, ArrowLeft, Save, Bot } from 'lucide-react'
import type { CDCData, Project } from '../../types'
import { Avatar } from '../layout/AppLayout'

const STEP_NAMES = [
  'Informations Générales',
  'Contexte & Problématique',
  'Mission du Robot',
  'Utilisateurs Cibles',
  'Fonctions Principales',
  'Architecture du Robot',
  'Contraintes du Projet',
  'Critères de Performance',
  'Schéma Fonctionnel',
  'Matériel Nécessaire',
  'Planning du Projet',
  'Résultat Attendu',
]

const TOTAL = 12

function StepIndicator({ current, total }: { current: number; total: number }) {
  const pct = ((current - 1) / (total - 1)) * 100
  return (
    <div style={{ padding: '24px 32px', borderBottom: '1.5px solid var(--border)', background: 'white' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 }}>
        <div>
          <div style={{ fontSize: 11.5, fontWeight: 700, color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.06em', marginBottom: 2 }}>
            Étape {current} sur {total}
          </div>
          <div style={{ fontSize: 17, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
            {STEP_NAMES[current - 1]}
          </div>
        </div>
        <div style={{ fontSize: 24, fontWeight: 900, color: 'var(--primary)', opacity: 0.2, fontVariantNumeric: 'tabular-nums' }}>
          {String(current).padStart(2, '0')}
        </div>
      </div>

      {/* Step dots */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
        {Array.from({ length: total }).map((_, i) => (
          <div
            key={i}
            style={{
              flex: i < total - 1 ? 1 : undefined,
              display: 'flex',
              alignItems: 'center',
              gap: 4,
            }}
          >
            <div style={{
              width: i === current - 1 ? 22 : 8,
              height: 8,
              borderRadius: 99,
              background: i < current ? 'var(--primary)' : i === current - 1 ? 'var(--primary)' : 'var(--border)',
              transition: 'all 0.25s ease',
              flexShrink: 0,
            }} />
            {i < total - 1 && (
              <div style={{ flex: 1, height: 2, background: i < current - 1 ? 'var(--primary)' : 'var(--border)', borderRadius: 99, transition: 'background 0.25s ease' }} />
            )}
          </div>
        ))}
      </div>
    </div>
  )
}

function SectionLabel({ label }: { label: string }) {
  return (
    <div style={{ fontSize: 11.5, fontWeight: 700, color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.06em', marginBottom: 16, paddingBottom: 8, borderBottom: '1px solid var(--border)' }}>
      {label}
    </div>
  )
}

function Field({ label, required, hint, children }: { label: string; required?: boolean; hint?: string; children: React.ReactNode }) {
  return (
    <div>
      <label className="input-label">
        {label}
        {required && <span style={{ color: 'var(--primary)', marginLeft: 3 }}>*</span>}
      </label>
      {children}
      {hint && <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 5 }}>{hint}</div>}
    </div>
  )
}

function DynamicList({ items, onChange, placeholder = 'Saisir...', addLabel = 'Ajouter une ligne' }: {
  items: string[]
  onChange: (items: string[]) => void
  placeholder?: string
  addLabel?: string
}) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
      {items.map((item, i) => (
        <div key={i} style={{ display: 'flex', gap: 8 }}>
          <div style={{ width: 28, height: 38, display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--primary)', fontWeight: 700, fontSize: 13, flexShrink: 0 }}>•</div>
          <input
            className="input-field"
            style={{ flex: 1 }}
            placeholder={placeholder}
            value={item}
            onChange={(e) => { const next = [...items]; next[i] = e.target.value; onChange(next) }}
          />
          <button
            type="button"
            className="btn btn-ghost btn-icon btn-sm"
            style={{ color: 'var(--danger)', flexShrink: 0 }}
            onClick={() => onChange(items.filter((_, j) => j !== i))}
            disabled={items.length <= 1}
          >
            <Trash2 size={14} />
          </button>
        </div>
      ))}
      <button
        type="button"
        className="btn btn-ghost btn-sm"
        style={{ alignSelf: 'flex-start', color: 'var(--primary)' }}
        onClick={() => onChange([...items, ''])}
      >
        <Plus size={13} /> {addLabel}
      </button>
    </div>
  )
}

function DynamicTable({ rows, cols, onChange, addLabel = 'Ajouter une ligne' }: {
  rows: Record<string, string>[]
  cols: Array<{ key: string; label: string; placeholder?: string; width?: number }>
  onChange: (rows: Record<string, string>[]) => void
  addLabel?: string
}) {
  const empty = Object.fromEntries(cols.map((c) => [c.key, '']))
  return (
    <div>
      <table style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr>
            {cols.map((c) => (
              <th key={c.key} style={{ textAlign: 'left', padding: '8px 10px', background: 'var(--surface-2)', borderBottom: '1.5px solid var(--border)', fontSize: 12, fontWeight: 700, color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.05em', width: c.width }}>
                {c.label}
              </th>
            ))}
            <th style={{ width: 40, background: 'var(--surface-2)', borderBottom: '1.5px solid var(--border)' }} />
          </tr>
        </thead>
        <tbody>
          {rows.map((row, ri) => (
            <tr key={ri}>
              {cols.map((c) => (
                <td key={c.key} style={{ padding: '6px 6px', borderBottom: '1px solid var(--border)', verticalAlign: 'middle' }}>
                  {c.key === 'id' ? (
                    <div style={{ padding: '6px 10px', fontSize: 13, fontWeight: 700, color: 'var(--primary)', fontFamily: 'JetBrains Mono, monospace' }}>{row[c.key]}</div>
                  ) : (
                    <input
                      className="input-field"
                      style={{ margin: 0, fontSize: 13 }}
                      placeholder={c.placeholder}
                      value={row[c.key] || ''}
                      onChange={(e) => { const next = [...rows]; next[ri] = { ...next[ri], [c.key]: e.target.value }; onChange(next) }}
                    />
                  )}
                </td>
              ))}
              <td style={{ borderBottom: '1px solid var(--border)', textAlign: 'center' }}>
                <button type="button" className="btn btn-ghost btn-icon btn-sm" style={{ color: 'var(--danger)' }} onClick={() => onChange(rows.filter((_, j) => j !== ri))} disabled={rows.length <= 1}>
                  <Trash2 size={13} />
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <button type="button" className="btn btn-ghost btn-sm" style={{ marginTop: 8, color: 'var(--primary)' }} onClick={() => onChange([...rows, { ...empty, id: rows.length > 0 && 'id' in rows[0] ? `F${rows.length + 1}` : '' }])}>
        <Plus size={13} /> {addLabel}
      </button>
    </div>
  )
}

// ─── Step components ─────────────────────────────────────────────────────────

function Step1({ data, onChange }: { data: CDCData['step1']; onChange: (d: CDCData['step1']) => void }) {
  const f = (k: keyof CDCData['step1']) => (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) =>
    onChange({ ...data, [k]: e.target.value })
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
      <Field label="Nom du projet" required hint="Ex: Robot Transporteur AGV, Bras Robotisé 6-DOF">
        <input className="input-field" placeholder="Saisissez le nom du projet..." value={data.projectName} onChange={f('projectName')} />
      </Field>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
        <Field label="Équipe / Groupe" required>
          <input className="input-field" placeholder="Ex: Groupe Mécatronique - 3ème année" value={data.team} onChange={f('team')} />
        </Field>
        <Field label="Date de création" required>
          <input className="input-field" type="date" value={data.date} onChange={f('date')} />
        </Field>
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
        <Field label="Type de projet">
          <select className="input-field" value={data.projectType} onChange={f('projectType')}>
            <option value="">Choisir...</option>
            <option>Robot Mobile Autonome</option>
            <option>Bras Robotisé</option>
            <option>Drone / UAV</option>
            <option>Robot Humanoïde</option>
            <option>Robot Fixe</option>
            <option>Système Embarqué</option>
            <option>Autre</option>
          </select>
        </Field>
        <Field label="Encadrant / Superviseur">
          <input className="input-field" placeholder="Ex: Prof. Kaci Meziane" value={data.supervisor || ''} onChange={f('supervisor')} />
        </Field>
      </div>
    </div>
  )
}

function Step2({ data, onChange }: { data: CDCData['step2']; onChange: (d: CDCData['step2']) => void }) {
  const f = (k: keyof CDCData['step2']) => (e: React.ChangeEvent<HTMLTextAreaElement>) =>
    onChange({ ...data, [k]: e.target.value })
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
      <div style={{ background: 'var(--primary-light)', border: '1px solid #bfdbfe', borderRadius: 8, padding: '12px 16px', fontSize: 13, color: '#1e40af' }}>
        <strong>Guide :</strong> Décrivez le problème concret que votre robot va résoudre. Soyez précis sur le contexte d'utilisation.
      </div>
      <Field label="Contexte général" required hint="Décrivez le secteur et le cadre dans lequel s'inscrit le projet">
        <textarea className="input-field" style={{ resize: 'vertical', minHeight: 90 }} placeholder="Ex: Dans un entrepôt de distribution, le transport manuel des colis est lent et source d'erreurs fréquentes..." value={data.context} onChange={f('context')} />
      </Field>
      <Field label="Problème à résoudre" required hint="Formulez la problématique principale de manière précise">
        <textarea className="input-field" style={{ resize: 'vertical', minHeight: 80 }} placeholder="Ex: Comment automatiser le transport de colis entre les zones de stockage et d'expédition ?" value={data.problem} onChange={f('problem')} />
      </Field>
      <Field label="Pourquoi un robot ?" required hint="Justifiez le choix d'une solution robotique par rapport à d'autres approches">
        <textarea className="input-field" style={{ resize: 'vertical', minHeight: 80 }} placeholder="Ex: Un robot permettra de réduire le temps de traitement de 60% tout en éliminant les risques d'accidents..." value={data.whyRobot} onChange={f('whyRobot')} />
      </Field>
    </div>
  )
}

function Step3({ data, onChange }: { data: CDCData['step3']; onChange: (d: CDCData['step3']) => void }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <div style={{ background: 'var(--primary-light)', border: '1px solid #bfdbfe', borderRadius: 8, padding: '12px 16px', fontSize: 13, color: '#1e40af' }}>
        <strong>Format :</strong> "Le robot doit être capable de..." — chaque point doit être mesurable et précis.
      </div>
      <Field label="Missions du robot" required hint="Listez toutes les missions que le robot doit accomplir">
        <DynamicList
          items={data.missions.length ? data.missions : ['']}
          onChange={(missions) => onChange({ ...data, missions })}
          placeholder="Ex: Transporter des colis de 0 à 25 kg entre les zones définies"
          addLabel="Ajouter une mission"
        />
      </Field>
    </div>
  )
}

const USER_OPTIONS = ['Élèves', 'Étudiants', 'Techniciens', 'Agriculteurs', 'Personnel médical', 'Industrie', 'Grand public', 'Chercheurs', 'Militaire']

function Step4({ data, onChange }: { data: CDCData['step4']; onChange: (d: CDCData['step4']) => void }) {
  const toggle = (u: string) => {
    const users = data.users.includes(u) ? data.users.filter((x) => x !== u) : [...data.users, u]
    onChange({ ...data, users })
  }
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
      <Field label="Qui utilisera le robot ?" required hint="Sélectionnez toutes les catégories d'utilisateurs concernées">
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10, marginTop: 4 }}>
          {USER_OPTIONS.map((u) => {
            const checked = data.users.includes(u)
            return (
              <label
                key={u}
                style={{
                  display: 'flex', alignItems: 'center', gap: 10,
                  padding: '11px 14px', border: '1.5px solid', borderRadius: 8, cursor: 'pointer',
                  borderColor: checked ? 'var(--primary)' : 'var(--border)',
                  background: checked ? 'var(--primary-light)' : 'white',
                  transition: 'all 0.15s',
                  userSelect: 'none',
                }}
              >
                <div style={{
                  width: 18, height: 18, borderRadius: 5, border: '1.5px solid',
                  borderColor: checked ? 'var(--primary)' : 'var(--border)',
                  background: checked ? 'var(--primary)' : 'white',
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  flexShrink: 0, transition: 'all 0.15s',
                }}>
                  {checked && <Check size={12} color="white" strokeWidth={3} />}
                </div>
                <input type="checkbox" checked={checked} onChange={() => toggle(u)} style={{ display: 'none' }} />
                <span style={{ fontSize: 13, fontWeight: checked ? 600 : 400, color: checked ? 'var(--primary)' : 'var(--text-secondary)' }}>{u}</span>
              </label>
            )
          })}
        </div>
      </Field>
      <Field label="Autres utilisateurs">
        <input className="input-field" placeholder="Précisez d'autres types d'utilisateurs..." value={data.otherUsers} onChange={(e) => onChange({ ...data, otherUsers: e.target.value })} />
      </Field>
    </div>
  )
}

function Step5({ data, onChange }: { data: CDCData['step5']; onChange: (d: CDCData['step5']) => void }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <div style={{ background: 'var(--warning-light)', border: '1px solid #fde68a', borderRadius: 8, padding: '12px 16px', fontSize: 13, color: '#92400e' }}>
        <strong>F1, F2...</strong> = Fonctions principales que le robot doit absolument réaliser pour accomplir sa mission.
      </div>
      <Field label="Fonctions principales" required hint="Listez toutes les fonctions essentielles du robot (minimum 4)">
        <DynamicTable
          rows={data.functions.length ? data.functions : [{ id: 'F1', name: '' }, { id: 'F2', name: '' }, { id: 'F3', name: '' }, { id: 'F4', name: '' }]}
          cols={[
            { key: 'id', label: 'N°', width: 60 },
            { key: 'name', label: 'Fonction', placeholder: 'Ex: Détecter les obstacles' },
          ]}
          onChange={(rows) => onChange({ ...data, functions: rows as any })}
          addLabel="Ajouter une fonction"
        />
      </Field>
    </div>
  )
}

const CONTROL_UNITS = ['Arduino Uno', 'Arduino Mega', 'Arduino Nano', 'ESP8266', 'ESP32', 'Raspberry Pi', 'Raspberry Pi 4', 'STM32', 'PIC', 'BeagleBone', 'Jetson Nano', 'Autre']
const ENERGY_SOURCES = ['Batterie Li-Po', 'Batterie Li-Ion', 'Batterie NiMH', 'Pile alcaline', 'Alimentation secteur', 'Panneau solaire', 'Supercondensateur', 'Hybride']

function Step6({ data, onChange }: { data: CDCData['step6']; onChange: (d: CDCData['step6']) => void }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
      <div>
        <SectionLabel label="Capteurs" />
        <Field label="Liste des capteurs" required hint="Précisez le nom et le rôle de chaque capteur">
          <DynamicTable
            rows={data.capteurs.length ? data.capteurs : [{ name: '', role: '' }]}
            cols={[
              { key: 'name', label: 'Capteur', placeholder: 'Ex: LIDAR TF-Luna', width: 220 },
              { key: 'role', label: 'Rôle', placeholder: 'Ex: Détection d\'obstacles à 360°' },
            ]}
            onChange={(rows) => onChange({ ...data, capteurs: rows as any })}
            addLabel="Ajouter un capteur"
          />
        </Field>
      </div>

      <div>
        <SectionLabel label="Unité de Contrôle" />
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 8 }}>
          {CONTROL_UNITS.map((u) => {
            const checked = data.controlUnit === u
            return (
              <label key={u} style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '9px 12px', border: '1.5px solid', borderRadius: 7, cursor: 'pointer', borderColor: checked ? 'var(--primary)' : 'var(--border)', background: checked ? 'var(--primary-light)' : 'white', transition: 'all 0.15s', userSelect: 'none' }}>
                <div style={{ width: 16, height: 16, borderRadius: '50%', border: '1.5px solid', borderColor: checked ? 'var(--primary)' : 'var(--border)', background: checked ? 'var(--primary)' : 'white', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                  {checked && <div style={{ width: 7, height: 7, borderRadius: '50%', background: 'white' }} />}
                </div>
                <input type="radio" name="controlUnit" checked={checked} onChange={() => onChange({ ...data, controlUnit: u })} style={{ display: 'none' }} />
                <span style={{ fontSize: 12.5, fontWeight: checked ? 600 : 400, color: checked ? 'var(--primary)' : 'var(--text-secondary)', fontFamily: 'JetBrains Mono, monospace' }}>{u}</span>
              </label>
            )
          })}
        </div>
        {data.controlUnit === 'Autre' && (
          <input className="input-field" style={{ marginTop: 10 }} placeholder="Précisez l'unité de contrôle..." value={data.otherControlUnit} onChange={(e) => onChange({ ...data, otherControlUnit: e.target.value })} />
        )}
      </div>

      <div>
        <SectionLabel label="Actionneurs" />
        <DynamicTable
          rows={data.actionneurs.length ? data.actionneurs : [{ name: '', role: '' }]}
          cols={[
            { key: 'name', label: 'Actionneur', placeholder: 'Ex: Moteur DC 12V', width: 220 },
            { key: 'role', label: 'Rôle', placeholder: 'Ex: Propulsion du robot' },
          ]}
          onChange={(rows) => onChange({ ...data, actionneurs: rows as any })}
          addLabel="Ajouter un actionneur"
        />
      </div>

      <div>
        <SectionLabel label="Source d'Énergie" />
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 8 }}>
          {ENERGY_SOURCES.map((e) => {
            const checked = data.energySource === e
            return (
              <label key={e} style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '10px 14px', border: '1.5px solid', borderRadius: 7, cursor: 'pointer', borderColor: checked ? 'var(--primary)' : 'var(--border)', background: checked ? 'var(--primary-light)' : 'white', transition: 'all 0.15s', userSelect: 'none' }}>
                <div style={{ width: 16, height: 16, borderRadius: '50%', border: '1.5px solid', borderColor: checked ? 'var(--primary)' : 'var(--border)', background: checked ? 'var(--primary)' : 'white', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                  {checked && <div style={{ width: 7, height: 7, borderRadius: '50%', background: 'white' }} />}
                </div>
                <input type="radio" name="energySource" checked={checked} onChange={() => onChange({ ...data, energySource: e })} style={{ display: 'none' }} />
                <span style={{ fontSize: 13, fontWeight: checked ? 600 : 400, color: checked ? 'var(--primary)' : 'var(--text-secondary)' }}>{e}</span>
              </label>
            )
          })}
        </div>
        {data.energySource && !ENERGY_SOURCES.slice(0, -1).includes(data.energySource) && (
          <input className="input-field" style={{ marginTop: 10 }} placeholder="Précisez la source d'énergie et les caractéristiques..." />
        )}
      </div>
    </div>
  )
}

function Step7({ data, onChange }: { data: CDCData['step7']; onChange: (d: CDCData['step7']) => void }) {
  const f = (k: keyof CDCData['step7']) => (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) =>
    onChange({ ...data, [k]: e.target.value })
  const toggleSafety = (s: string) => {
    const arr = data.safetyConstraints.includes(s) ? data.safetyConstraints.filter((x) => x !== s) : [...data.safetyConstraints, s]
    onChange({ ...data, safetyConstraints: arr })
  }
  const safetyOptions = ['Arrêt d\'urgence physique', 'Protection contre la surchauffe', 'Respect des normes IEC 60950', 'Protection contre les courts-circuits', 'Signalisation lumineuse et sonore', 'Isolation électrique complète', 'Limiteur de courant', 'Détection de chute']

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
      <div>
        <SectionLabel label="Contraintes Techniques" />
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
          <Field label="Taille maximale (L × l × H)"><input className="input-field" placeholder="Ex: 60 × 40 × 35 cm" value={data.maxSize} onChange={f('maxSize')} /></Field>
          <Field label="Poids maximal"><input className="input-field" placeholder="Ex: 10 kg" value={data.maxWeight} onChange={f('maxWeight')} /></Field>
          <Field label="Autonomie minimale"><input className="input-field" placeholder="Ex: 4 heures" value={data.minAutonomy} onChange={f('minAutonomy')} /></Field>
          <Field label="Vitesse minimale"><input className="input-field" placeholder="Ex: 0.5 m/s" value={data.minSpeed} onChange={f('minSpeed')} /></Field>
        </div>
      </div>

      <div>
        <SectionLabel label="Contraintes Économiques" />
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
          <Field label="Budget maximal" hint="Budget total alloué"><input className="input-field" placeholder="Ex: 35 000 DA" value={data.maxBudget} onChange={f('maxBudget')} /></Field>
          <Field label="Coût estimé" hint="Estimation actuelle"><input className="input-field" placeholder="Ex: 28 500 DA" value={data.estimatedCost} onChange={f('estimatedCost')} /></Field>
        </div>
      </div>

      <div>
        <SectionLabel label="Contraintes de Sécurité" />
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
          {safetyOptions.map((s) => {
            const checked = data.safetyConstraints.includes(s)
            return (
              <label key={s} onClick={() => toggleSafety(s)} style={{ display: 'flex', alignItems: 'center', gap: 9, padding: '9px 12px', border: '1.5px solid', borderRadius: 7, cursor: 'pointer', borderColor: checked ? 'var(--success)' : 'var(--border)', background: checked ? 'var(--success-light)' : 'white', transition: 'all 0.15s', userSelect: 'none' }}>
                <div style={{ width: 17, height: 17, borderRadius: 5, border: '1.5px solid', borderColor: checked ? 'var(--success)' : 'var(--border)', background: checked ? 'var(--success)' : 'white', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                  {checked && <Check size={11} color="white" strokeWidth={3} />}
                </div>
                <span style={{ fontSize: 12.5, color: checked ? '#065f46' : 'var(--text-secondary)', fontWeight: checked ? 600 : 400 }}>{s}</span>
              </label>
            )
          })}
        </div>
      </div>

      <div>
        <SectionLabel label="Contraintes Environnementales" />
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
          <Field label="Température de fonctionnement"><input className="input-field" placeholder="Ex: 10°C à 40°C" value={data.temperature} onChange={f('temperature')} /></Field>
          <Field label="Conditions d'utilisation"><input className="input-field" placeholder="Ex: Sol plat intérieur, éclairage normal..." value={data.usageConditions} onChange={f('usageConditions')} /></Field>
        </div>
      </div>
    </div>
  )
}

const DEFAULT_CRITERIA = [
  { name: 'Autonomie', value: '' },
  { name: 'Vitesse', value: '' },
  { name: 'Charge utile', value: '' },
  { name: 'Distance de détection', value: '' },
  { name: 'Précision de positionnement', value: '' },
]

function Step8({ data, onChange }: { data: CDCData['step8']; onChange: (d: CDCData['step8']) => void }) {
  const criteria = data.criteria.length ? data.criteria : DEFAULT_CRITERIA
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <div style={{ background: 'var(--primary-light)', border: '1px solid #bfdbfe', borderRadius: 8, padding: '12px 16px', fontSize: 13, color: '#1e40af' }}>
        <strong>Conseil :</strong> Les critères de performance doivent être quantifiables avec des valeurs mesurables (ex: ≥ 4 heures, 0.5 m/s, ± 2 cm).
      </div>
      <DynamicTable
        rows={criteria}
        cols={[
          { key: 'name', label: 'Critère', placeholder: 'Ex: Autonomie batterie', width: 240 },
          { key: 'value', label: 'Valeur attendue', placeholder: 'Ex: ≥ 4 heures' },
        ]}
        onChange={(rows) => onChange({ ...data, criteria: rows as any })}
        addLabel="Ajouter un critère"
      />
    </div>
  )
}

function Step9({ data, onChange }: { data: CDCData['step9']; onChange: (d: CDCData['step9']) => void }) {
  const f = (k: keyof CDCData['step9']) => (e: React.ChangeEvent<HTMLInputElement>) =>
    onChange({ ...data, [k]: e.target.value })

  const blocks = [
    { key: 'missionLabel', label: 'Mission (entrée)', icon: '🎯', color: '#2563eb', desc: 'L\'objectif à atteindre' },
    { key: 'perceptionLabel', label: 'Perception (Capteurs)', icon: '👁️', color: '#7c3aed', desc: 'Acquisition de données' },
    { key: 'decisionLabel', label: 'Décision (Microcontrôleur)', icon: '🧠', color: '#059669', desc: 'Traitement et algorithmes' },
    { key: 'actionLabel', label: 'Action (Actionneurs)', icon: '⚙️', color: '#d97706', desc: 'Exécution physique' },
    { key: 'feedbackLabel', label: 'Retour (Feedback)', icon: '📡', color: '#dc2626', desc: 'Supervision et monitoring' },
  ] as const

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
      <div style={{ background: 'var(--surface-2)', border: '1.5px solid var(--border)', borderRadius: 10, padding: '20px', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 0 }}>
        <div style={{ fontSize: 12.5, fontWeight: 600, color: 'var(--text-muted)', marginBottom: 12, textAlign: 'center' }}>Aperçu du schéma fonctionnel</div>
        {blocks.map((b, i) => (
          <div key={b.key} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
            <div style={{ padding: '10px 24px', borderRadius: 8, border: '1.5px solid', borderColor: b.color + '40', background: b.color + '10', textAlign: 'center', minWidth: 220 }}>
              <div style={{ fontSize: 16, marginBottom: 2 }}>{b.icon}</div>
              <div style={{ fontSize: 12, fontWeight: 700, color: b.color, textTransform: 'uppercase', letterSpacing: '0.04em' }}>{b.label}</div>
              <div style={{ fontSize: 12, color: 'var(--text-secondary)', marginTop: 3 }}>{data[b.key] || b.desc}</div>
            </div>
            {i < blocks.length - 1 && (
              <div style={{ width: 2, height: 20, background: 'var(--border)' }} />
            )}
          </div>
        ))}
      </div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
        <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-secondary)', marginBottom: -4 }}>Personnalisez chaque bloc :</div>
        {blocks.map((b) => (
          <div key={b.key} style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <div style={{ width: 28, height: 28, borderRadius: 7, background: b.color + '15', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 14, flexShrink: 0 }}>{b.icon}</div>
            <input
              className="input-field"
              placeholder={b.desc}
              value={data[b.key] || ''}
              onChange={f(b.key)}
              style={{ flex: 1 }}
            />
          </div>
        ))}
      </div>
    </div>
  )
}

function Step10({ data, onChange }: { data: CDCData['step10']; onChange: (d: CDCData['step10']) => void }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <div style={{ background: 'var(--surface-2)', border: '1.5px solid var(--border)', borderRadius: 8, padding: '12px 16px', fontSize: 13, color: 'var(--text-secondary)' }}>
        Listez tous les composants matériels nécessaires à la réalisation du robot. Incluez les références si connues.
      </div>
      <DynamicTable
        rows={data.materials.length ? data.materials : [{ component: '', quantity: '', reference: '' }, { component: '', quantity: '', reference: '' }]}
        cols={[
          { key: 'component', label: 'Composant', placeholder: 'Ex: ESP32 DevKit V1' },
          { key: 'quantity', label: 'Quantité', placeholder: 'Ex: 2', width: 100 },
          { key: 'reference', label: 'Référence', placeholder: 'Ex: AZ-ESP32-01', width: 150 },
        ]}
        onChange={(rows) => onChange({ ...data, materials: rows as any })}
        addLabel="Ajouter un composant"
      />
    </div>
  )
}

const DEFAULT_PLANNING = [
  { stage: 'Analyse du besoin et spécifications', date: '', status: 'pending' },
  { stage: 'Conception mécanique (CAO)', date: '', status: 'pending' },
  { stage: 'Schéma électronique', date: '', status: 'pending' },
  { stage: 'Assemblage du châssis', date: '', status: 'pending' },
  { stage: 'Programmation', date: '', status: 'pending' },
  { stage: 'Tests et validation', date: '', status: 'pending' },
]

function Step11({ data, onChange }: { data: CDCData['step11']; onChange: (d: CDCData['step11']) => void }) {
  const planning = data.planning.length ? data.planning : DEFAULT_PLANNING
  const statusOpts = ['pending', 'in-progress', 'done']
  const statusStyles: Record<string, { label: string; color: string; bg: string }> = {
    pending: { label: 'À faire', color: 'var(--text-muted)', bg: 'var(--surface-2)' },
    'in-progress': { label: 'En cours', color: 'var(--primary)', bg: 'var(--primary-light)' },
    done: { label: 'Terminé', color: 'var(--success)', bg: 'var(--success-light)' },
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
      {planning.map((row, ri) => (
        <div key={ri} style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
          <div style={{ width: 28, height: 28, borderRadius: 7, background: statusStyles[row.status]?.bg || 'var(--surface-2)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
            {row.status === 'done' ? <Check size={14} color="var(--success)" strokeWidth={3} /> : <span style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-muted)' }}>{ri + 1}</span>}
          </div>
          <input
            className="input-field"
            style={{ flex: 1 }}
            placeholder="Nom de l'étape..."
            value={row.stage}
            onChange={(e) => { const next = [...planning]; next[ri] = { ...next[ri], stage: e.target.value }; onChange({ ...data, planning: next }) }}
          />
          <input
            className="input-field"
            type="date"
            style={{ width: 160 }}
            value={row.date}
            onChange={(e) => { const next = [...planning]; next[ri] = { ...next[ri], date: e.target.value }; onChange({ ...data, planning: next }) }}
          />
          <select
            className="input-field"
            style={{ width: 130 }}
            value={row.status}
            onChange={(e) => { const next = [...planning]; next[ri] = { ...next[ri], status: e.target.value }; onChange({ ...data, planning: next }) }}
          >
            {statusOpts.map((s) => <option key={s} value={s}>{statusStyles[s].label}</option>)}
          </select>
          <button type="button" className="btn btn-ghost btn-icon btn-sm" style={{ color: 'var(--danger)', flexShrink: 0 }} onClick={() => onChange({ ...data, planning: planning.filter((_, j) => j !== ri) })} disabled={planning.length <= 1}>
            <Trash2 size={13} />
          </button>
        </div>
      ))}
      <button type="button" className="btn btn-ghost btn-sm" style={{ alignSelf: 'flex-start', color: 'var(--primary)' }} onClick={() => onChange({ ...data, planning: [...planning, { stage: '', date: '', status: 'pending' }] })}>
        <Plus size={13} /> Ajouter une étape
      </button>
    </div>
  )
}

function Step12({ data, onChange }: { data: CDCData['step12']; onChange: (d: CDCData['step12']) => void }) {
  const f = (k: keyof CDCData['step12']) => (e: React.ChangeEvent<HTMLTextAreaElement | HTMLInputElement>) =>
    onChange({ ...data, [k]: e.target.value })
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
      <div style={{ background: '#ecfdf5', border: '1px solid #6ee7b7', borderRadius: 8, padding: '12px 16px', fontSize: 13, color: '#065f46' }}>
        <strong>Exemple :</strong> "Le robot doit détecter un obstacle à moins de 20 cm et changer de direction en moins de 2 secondes."
      </div>
      <Field label="Résultat attendu" required hint="Décrivez précisément ce qui permettra de considérer le projet comme réussi">
        <textarea className="input-field" style={{ resize: 'vertical', minHeight: 100 }} placeholder="Décrivez en détail le résultat attendu avec des valeurs mesurables..." value={data.expectedResult} onChange={f('expectedResult')} />
      </Field>
      <Field label="Critères de succès" required hint="Quelles conditions doivent être remplies pour valider le projet ?">
        <textarea className="input-field" style={{ resize: 'vertical', minHeight: 80 }} placeholder="Ex: 5 trajets consécutifs réussis sans intervention humaine, batterie ≥ 4h..." value={data.successCriteria} onChange={f('successCriteria')} />
      </Field>
      <Field label="Méthode de test et validation">
        <textarea className="input-field" style={{ resize: 'vertical', minHeight: 80 }} placeholder="Ex: Tests en conditions réelles dans le hall technique, avec 3 évaluateurs..." value={data.testMethod} onChange={f('testMethod')} />
      </Field>
    </div>
  )
}

// ─── CDCWizard main ───────────────────────────────────────────────────────────
function getDefaultCDC(): CDCData {
  return {
    step1: { projectName: '', team: '', date: new Date().toISOString().split('T')[0], projectType: '', supervisor: '' },
    step2: { context: '', problem: '', whyRobot: '' },
    step3: { missions: [''] },
    step4: { users: [], otherUsers: '' },
    step5: { functions: [{ id: 'F1', name: '' }, { id: 'F2', name: '' }, { id: 'F3', name: '' }, { id: 'F4', name: '' }] },
    step6: { capteurs: [{ name: '', role: '' }], controlUnit: '', otherControlUnit: '', actionneurs: [{ name: '', role: '' }], energySource: '' },
    step7: { maxSize: '', maxWeight: '', minAutonomy: '', minSpeed: '', maxBudget: '', estimatedCost: '', safetyConstraints: [], temperature: '', usageConditions: '' },
    step8: { criteria: DEFAULT_CRITERIA },
    step9: { missionLabel: '', perceptionLabel: '', decisionLabel: '', actionLabel: '', feedbackLabel: '' },
    step10: { materials: [{ component: '', quantity: '', reference: '' }] },
    step11: { planning: DEFAULT_PLANNING },
    step12: { expectedResult: '', successCriteria: '', testMethod: '' },
  }
}

export function CDCWizard({ project, onSave, onBack }: {
  project: Project
  onSave: (cdc: CDCData) => void
  onBack: () => void
}) {
  const [step, setStep] = useState(1)
  const [cdc, setCDC] = useState<CDCData>(project.cdcData || getDefaultCDC())
  const [saving, setSaving] = useState(false)

  const updateStep = (s: keyof CDCData) => (d: CDCData[typeof s]) =>
    setCDC((prev) => ({ ...prev, [s]: d }))

  const handleSave = () => {
    setSaving(true)
    setTimeout(() => { setSaving(false); onSave(cdc) }, 700)
  }

  const isLast = step === TOTAL

  return (
    <div style={{ minHeight: '100vh', background: 'var(--bg)', display: 'flex', flexDirection: 'column' }}>
      {/* Top bar */}
      <div style={{ background: 'white', borderBottom: '1.5px solid var(--border)', padding: '12px 24px', display: 'flex', alignItems: 'center', gap: 12 }}>
        <button className="btn btn-ghost btn-sm" onClick={onBack} style={{ color: 'var(--text-secondary)' }}>
          <ArrowLeft size={15} /> Retour
        </button>
        <div style={{ width: '1px', height: 20, background: 'var(--border)' }} />
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, flex: 1 }}>
          <div style={{ width: 28, height: 28, background: 'var(--primary)', borderRadius: 7, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Bot size={15} color="white" />
          </div>
          <div>
            <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--text-primary)' }}>{project.name}</div>
            <div style={{ fontSize: 11.5, color: 'var(--text-muted)' }}>Cahier des Charges</div>
          </div>
        </div>
        <button className="btn btn-secondary btn-sm" onClick={handleSave} disabled={saving}>
          <Save size={14} /> {saving ? 'Sauvegarde...' : 'Sauvegarder'}
        </button>
        {isLast && (
          <button className="btn btn-primary btn-sm" onClick={handleSave} disabled={saving}>
            <CheckCircle2 size={14} /> Terminer
          </button>
        )}
      </div>

      <div style={{ flex: 1, display: 'flex', maxWidth: 1100, margin: '0 auto', width: '100%', padding: '0' }}>
        {/* Left: step list */}
        <div style={{ width: 220, borderRight: '1.5px solid var(--border)', background: 'white', padding: '20px 12px', overflowY: 'auto' }}>
          {STEP_NAMES.map((name, i) => {
            const n = i + 1
            const active = n === step
            const done = n < step
            return (
              <button
                key={n}
                onClick={() => setStep(n)}
                style={{
                  display: 'flex', alignItems: 'center', gap: 10, width: '100%',
                  padding: '8px 10px', borderRadius: 7, border: 'none', cursor: 'pointer',
                  background: active ? 'var(--primary-light)' : 'none',
                  textAlign: 'left', marginBottom: 2,
                  transition: 'background 0.15s',
                }}
                onMouseEnter={(e) => { if (!active) (e.currentTarget as HTMLElement).style.background = 'var(--surface-2)' }}
                onMouseLeave={(e) => { if (!active) (e.currentTarget as HTMLElement).style.background = 'none' }}
              >
                <div style={{
                  width: 22, height: 22, borderRadius: 6, flexShrink: 0,
                  background: done ? 'var(--success)' : active ? 'var(--primary)' : 'var(--surface-2)',
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  border: active ? '2px solid var(--primary)' : done ? '2px solid var(--success)' : '1.5px solid var(--border)',
                  transition: 'all 0.2s',
                }}>
                  {done ? <Check size={12} color="white" strokeWidth={3} /> : <span style={{ fontSize: 11, fontWeight: 700, color: active ? 'white' : 'var(--text-muted)' }}>{n}</span>}
                </div>
                <span style={{ fontSize: 12.5, fontWeight: active ? 600 : 400, color: active ? 'var(--primary)' : done ? 'var(--text-secondary)' : 'var(--text-muted)', lineHeight: 1.3 }}>{name}</span>
              </button>
            )
          })}
        </div>

        {/* Main content */}
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', overflow: 'hidden' }}>
          <StepIndicator current={step} total={TOTAL} />

          <div style={{ flex: 1, overflowY: 'auto', padding: '28px 36px' }}>
            {step === 1 && <Step1 data={cdc.step1} onChange={updateStep('step1')} />}
            {step === 2 && <Step2 data={cdc.step2} onChange={updateStep('step2')} />}
            {step === 3 && <Step3 data={cdc.step3} onChange={updateStep('step3')} />}
            {step === 4 && <Step4 data={cdc.step4} onChange={updateStep('step4')} />}
            {step === 5 && <Step5 data={cdc.step5} onChange={updateStep('step5')} />}
            {step === 6 && <Step6 data={cdc.step6} onChange={updateStep('step6')} />}
            {step === 7 && <Step7 data={cdc.step7} onChange={updateStep('step7')} />}
            {step === 8 && <Step8 data={cdc.step8} onChange={updateStep('step8')} />}
            {step === 9 && <Step9 data={cdc.step9} onChange={updateStep('step9')} />}
            {step === 10 && <Step10 data={cdc.step10} onChange={updateStep('step10')} />}
            {step === 11 && <Step11 data={cdc.step11} onChange={updateStep('step11')} />}
            {step === 12 && <Step12 data={cdc.step12} onChange={updateStep('step12')} />}
          </div>

          {/* Navigation */}
          <div style={{ padding: '16px 36px', borderTop: '1.5px solid var(--border)', background: 'white', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <button
              className="btn btn-secondary"
              onClick={() => setStep((s) => Math.max(1, s - 1))}
              disabled={step === 1}
            >
              <ChevronLeft size={16} /> Précédent
            </button>

            <div style={{ display: 'flex', gap: 4 }}>
              {Array.from({ length: TOTAL }).map((_, i) => (
                <div
                  key={i}
                  onClick={() => setStep(i + 1)}
                  style={{
                    width: i + 1 === step ? 20 : 8,
                    height: 8,
                    borderRadius: 99,
                    background: i + 1 === step ? 'var(--primary)' : i < step ? '#bfdbfe' : 'var(--border)',
                    cursor: 'pointer',
                    transition: 'all 0.2s',
                  }}
                />
              ))}
            </div>

            {isLast ? (
              <button className="btn btn-primary" onClick={handleSave} disabled={saving}>
                <CheckCircle2 size={16} /> {saving ? 'Sauvegarde...' : 'Enregistrer le CDC'}
              </button>
            ) : (
              <button className="btn btn-primary" onClick={() => setStep((s) => Math.min(TOTAL, s + 1))}>
                Suivant <ChevronRight size={16} />
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
