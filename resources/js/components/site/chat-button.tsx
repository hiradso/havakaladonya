import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { useI18n } from '@/i18n';
import { Mail, MessageCircle, Phone } from 'lucide-react';

/**
 * Floating support button. The real live chat (with Laravel Reverb)
 * is built in a later step; for now it shows the contact options.
 */
export default function ChatButton() {
    const { t } = useI18n();

    return (
        <Sheet>
            <SheetTrigger asChild>
                <Button
                    className="fixed end-4 bottom-4 z-40 h-14 gap-2 rounded-full px-5 font-bold shadow-lg sm:end-6 sm:bottom-6"
                    aria-label={t.chat.button}
                >
                    <MessageCircle className="size-6" />
                    <span className="hidden sm:inline">{t.chat.button}</span>
                </Button>
            </SheetTrigger>
            <SheetContent side="bottom" className="mx-auto max-w-lg rounded-t-2xl">
                <SheetHeader>
                    <SheetTitle className="text-start">{t.chat.title}</SheetTitle>
                    <SheetDescription className="text-start leading-7">{t.chat.soon}</SheetDescription>
                </SheetHeader>
                <div className="mt-6 grid gap-3 sm:grid-cols-2">
                    <Button asChild size="lg" className="h-12">
                        <a href="tel:+989123336855">
                            <Phone />
                            {t.chat.call}
                        </a>
                    </Button>
                    <Button asChild size="lg" variant="outline" className="h-12">
                        <a href="mailto:info@havakaladonya.com">
                            <Mail />
                            {t.chat.email}
                        </a>
                    </Button>
                </div>
            </SheetContent>
        </Sheet>
    );
}
