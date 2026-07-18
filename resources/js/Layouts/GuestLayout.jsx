import { Link } from '@inertiajs/react';
import { Bot } from 'lucide-react';

export default function GuestLayout({ children }) {
    return (
        <div className="rf-auth-shell">
            <div className="rf-auth-card">
                <Link href="/" className="rf-auth-brand">
                    <div className="rf-brand-mark"><Bot size={19} /></div>
                    <strong>RoboForge</strong>
                </Link>

                <div className="rf-panel">{children}</div>
            </div>
        </div>
    );
}
