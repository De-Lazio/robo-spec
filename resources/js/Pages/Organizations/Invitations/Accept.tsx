import GuestLayout from '@/Layouts/GuestLayout'
import { organizationRoleLabels } from '@/types/organizations'
import type { OrganizationRole } from '@/types/organizations'
import { Head, Link } from '@inertiajs/react'

type Status = 'guest' | 'email_mismatch' | 'invalid' | 'expired' | 'accepted'

interface AcceptProps {
    status: Status
    organization: { name: string } | null
    role: OrganizationRole | null
    email: string | null
}

export default function Accept({ status, organization, role, email }: AcceptProps) {
    return (
        <GuestLayout>
            <Head title="Invitation" />
            {status === 'invalid' && (
                <div className="rf-auth-hint">Ce lien d’invitation n’est pas valide.</div>
            )}
            {status === 'expired' && (
                <div className="rf-auth-hint">Cette invitation a expiré. Demandez à un responsable de l’organisation de vous en envoyer une nouvelle.</div>
            )}
            {status === 'accepted' && (
                <div className="rf-auth-hint">Cette invitation a déjà été acceptée.</div>
            )}
            {status === 'email_mismatch' && (
                <>
                    <p className="rf-auth-hint">Cette invitation est destinée à <strong>{email}</strong>. Vous êtes connecté(e) avec un autre compte.</p>
                    <div className="rf-auth-row"><Link href="/logout" method="post" as="button" className="rf-auth-link">Se déconnecter</Link></div>
                </>
            )}
            {status === 'guest' && organization && role && (
                <>
                    <p className="rf-auth-hint">
                        Vous êtes invité(e) à rejoindre <strong>{organization.name}</strong> en tant que {organizationRoleLabels[role]}.
                        Connectez-vous ou créez un compte avec l’adresse <strong>{email}</strong> pour continuer.
                    </p>
                    <div className="rf-auth-row">
                        <Link href="/login" className="rf-button rf-button--secondary">Se connecter</Link>
                        <Link href="/register" className="rf-button rf-button--primary">Créer un compte</Link>
                    </div>
                </>
            )}
        </GuestLayout>
    )
}
