import {
    Briefcase,
    Container,
    FileCheck2,
    Globe2,
    Handshake,
    Lightbulb,
    Luggage,
    Package,
    Plane,
    ShieldCheck,
    Ship,
    TrainFront,
    Truck,
    Warehouse,
    type LucideProps,
} from 'lucide-react';

/** Icon keys chosen in the panel (App\Models\Service::ICONS). */
const ICONS: Record<string, React.ComponentType<LucideProps>> = {
    plane: Plane,
    ship: Ship,
    truck: Truck,
    train: TrainFront,
    'file-check': FileCheck2,
    globe: Globe2,
    luggage: Luggage,
    lightbulb: Lightbulb,
    briefcase: Briefcase,
    package: Package,
    warehouse: Warehouse,
    shield: ShieldCheck,
    container: Container,
    handshake: Handshake,
};

export default function ServiceIcon({ icon, ...props }: { icon: string } & LucideProps) {
    const Icon = ICONS[icon] ?? Package;

    return <Icon aria-hidden="true" {...props} />;
}
