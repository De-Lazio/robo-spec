import { Head, Link } from '@inertiajs/react'
import { AlertTriangle, Bot, Home, Lock, ServerCrash, TimerReset } from 'lucide-react'

interface ErrorProps {
    status: 403 | 404 | 419 | 500 | 503
}

const content: Record<ErrorProps['status'], { icon: typeof Lock; title: string; message: string }> = {
    403: { icon: Lock, title: 'Accès refusé', message: 'Vous n’avez pas les droits nécessaires pour accéder à cette page.' },
    404: { icon: AlertTriangle, title: 'Page introuvable', message: 'Cette page n’existe pas ou a été déplacée.' },
    419: { icon: TimerReset, title: 'Session expirée', message: 'Votre session a expiré. Merci de réessayer.' },
    500: { icon: ServerCrash, title: 'Erreur serveur', message: 'Une erreur inattendue est survenue. Réessayez dans quelques instants.' },
    503: { icon: ServerCrash, title: 'Service indisponible', message: 'RoboForge est temporairement indisponible. Réessayez dans quelques instants.' },
}

export default function Error({ status }: ErrorProps) {
    const { icon: Icon, title, message } = content[status]

    return (
        <div className="rf-auth-shell">
            <Head title={title} />
            <div className="rf-auth-card">
                <Link href="/" className="rf-auth-brand">
                    <div className="rf-brand-mark"><Bot size={19} /></div>
                    <strong>RoboForge</strong>
                </Link>

                <div className="rf-empty">
                    <Icon size={28} />
                    <h2>{title}</h2>
                    <p>{message}</p>
                    <Link href="/dashboard" className="rf-button rf-button--primary"><Home size={15} /> Retour au tableau de bord</Link>
                </div>
            </div>
        </div>
    )
}
