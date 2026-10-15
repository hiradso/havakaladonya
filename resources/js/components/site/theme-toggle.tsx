import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/hooks/use-appearance';
import { useI18n } from '@/i18n';
import { Monitor, Moon, Sun } from 'lucide-react';

export default function ThemeToggle() {
    const { appearance, updateAppearance } = useAppearance();
    const { t } = useI18n();

    const Icon = appearance === 'dark' ? Moon : appearance === 'light' ? Sun : Monitor;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="size-11" aria-label={t.theme.label}>
                    <Icon className="size-5" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem onClick={() => updateAppearance('light')} className="gap-2">
                    <Sun className="size-4" />
                    {t.theme.light}
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => updateAppearance('dark')} className="gap-2">
                    <Moon className="size-4" />
                    {t.theme.dark}
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => updateAppearance('system')} className="gap-2">
                    <Monitor className="size-4" />
                    {t.theme.system}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
