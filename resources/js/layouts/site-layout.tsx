import ChatButton from '@/components/site/chat-button';
import SiteFooter from '@/components/site/site-footer';
import SiteHeader from '@/components/site/site-header';
import { type ReactNode } from 'react';

/** Public website layout: header, page content, footer and the chat button. */
export default function SiteLayout({ children }: { children: ReactNode }) {
    return (
        <div className="bg-background text-foreground flex min-h-screen flex-col overflow-x-clip">
            <SiteHeader />
            <main className="flex-1">{children}</main>
            <SiteFooter />
            <ChatButton />
        </div>
    );
}
