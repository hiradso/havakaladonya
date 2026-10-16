import type { ServiceSlug } from '@/content/services';
import { Briefcase, FileCheck2, Globe2, Lightbulb, Luggage, Plane, Ship, TrainFront, Truck, type LucideProps } from 'lucide-react';

const ICONS: Record<ServiceSlug, React.ComponentType<LucideProps>> = {
    'air-freight': Plane,
    'sea-freight': Ship,
    'road-freight': Truck,
    'rail-freight': TrainFront,
    'customs-clearance': FileCheck2,
    sourcing: Globe2,
    'passenger-freight': Luggage,
    consulting: Lightbulb,
    'trading-company': Briefcase,
};

export default function ServiceIcon({ slug, ...props }: { slug: ServiceSlug } & LucideProps) {
    const Icon = ICONS[slug];

    return <Icon aria-hidden="true" {...props} />;
}
