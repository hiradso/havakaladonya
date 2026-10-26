import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useI18n } from '@/i18n';
import { useContact } from '@/lib/contact';
import { cn } from '@/lib/utils';
import axios, { AxiosError } from 'axios';
import { MessageCircle, Phone, SendHorizontal, X } from 'lucide-react';
import { type FormEvent, useCallback, useEffect, useRef, useState } from 'react';

/**
 * Live chat with the support team.
 * The visitor's browser keeps a random token; messages are fetched every few seconds
 * (often while the window is open, rarely while it is closed, to show the unread badge).
 */

interface ChatMessage {
    id: number;
    from: 'visitor' | 'agent';
    body: string;
    at: string;
}

interface ChatState {
    status: 'open' | 'closed';
    online: boolean;
    unread: number;
    messages: ChatMessage[];
}

const TOKEN_KEY = 'hkd-chat-token';
const OPEN_POLL_MS = 3000;
const CLOSED_POLL_MS = 20000;

const readToken = () => {
    try {
        return localStorage.getItem(TOKEN_KEY);
    } catch {
        return null;
    }
};

const writeToken = (token: string | null) => {
    try {
        if (token) localStorage.setItem(TOKEN_KEY, token);
        else localStorage.removeItem(TOKEN_KEY);
    } catch {
        // Private mode: the chat still works for this visit.
    }
};

export default function ChatButton() {
    const { t, locale, digits } = useI18n();
    const contact = useContact();
    const c = t.chat;

    const [open, setOpen] = useState(false);
    const [token, setToken] = useState<string | null>(null);
    const [online, setOnline] = useState(false);
    const [chat, setChat] = useState<ChatState | null>(null);
    const [error, setError] = useState<string | null>(null);
    const lastId = useRef(0);

    // Restore an earlier conversation (browser only).
    useEffect(() => {
        setToken(readToken());
    }, []);

    const merge = useCallback((data: ChatState & { messages: ChatMessage[] }, replace = false) => {
        setOnline(data.online);
        setChat((prev) => {
            const known = replace || !prev ? [] : prev.messages;
            const ids = new Set(known.map((m) => m.id));
            const messages = [...known, ...data.messages.filter((m) => !ids.has(m.id))];
            lastId.current = messages.length ? messages[messages.length - 1].id : 0;
            return { status: data.status, online: data.online, unread: data.unread, messages };
        });
    }, []);

    const refresh = useCallback(
        async (markRead: boolean) => {
            if (!token) {
                const { data } = await axios.get('/chat/status');
                setOnline(data.online);
                return;
            }
            try {
                const { data } = await axios.get(`/chat/${token}`, { params: { after: lastId.current, read: markRead ? 1 : 0 } });
                merge(data);
            } catch (e) {
                if ((e as AxiosError).response?.status === 404) {
                    // Conversation was deleted on the server.
                    writeToken(null);
                    setToken(null);
                    setChat(null);
                    lastId.current = 0;
                }
            }
        },
        [token, merge],
    );

    // Polling
    useEffect(() => {
        if (!open && !token) return;
        refresh(open).catch(() => {});
        const id = window.setInterval(() => refresh(open).catch(() => {}), open ? OPEN_POLL_MS : CLOSED_POLL_MS);
        return () => window.clearInterval(id);
    }, [open, token, refresh]);

    // Escape closes the window
    useEffect(() => {
        if (!open) return;
        const onKey = (e: KeyboardEvent) => e.key === 'Escape' && setOpen(false);
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [open]);

    const unread = open ? 0 : (chat?.unread ?? 0);

    const startNew = () => {
        writeToken(null);
        setToken(null);
        setChat(null);
        lastId.current = 0;
    };

    const failed = (e: unknown) => setError((e as AxiosError).response?.status === 429 ? c.tooMany : c.error);

    return (
        <>
            <Button
                onClick={() => setOpen(true)}
                className={cn('fixed end-4 bottom-4 z-40 h-14 gap-2 rounded-full px-5 font-bold shadow-lg sm:end-6 sm:bottom-6', open && 'hidden')}
                aria-label={c.button}
                aria-haspopup="dialog"
            >
                <MessageCircle className="size-6" />
                <span className="hidden sm:inline">{c.button}</span>
                {unread > 0 && (
                    <span
                        className="bg-destructive absolute -end-1 -top-1 flex min-w-6 items-center justify-center rounded-full px-1.5 text-xs leading-6 font-bold text-white"
                        aria-label={`${digits(unread)} ${c.unread}`}
                    >
                        {digits(unread)}
                    </span>
                )}
            </Button>

            {open && (
                <div
                    role="dialog"
                    aria-modal="false"
                    aria-label={c.title}
                    className="bg-background fixed inset-0 z-50 flex flex-col sm:inset-auto sm:end-6 sm:bottom-6 sm:h-[min(38rem,calc(100dvh-3rem))] sm:w-[24rem] sm:overflow-hidden sm:rounded-2xl sm:border sm:shadow-2xl"
                >
                    {/* Header */}
                    <div className="bg-card flex items-center gap-3 border-b px-4 py-3">
                        <div className="flex min-w-0 flex-1 flex-col">
                            <strong className="font-black">{c.title}</strong>
                            <span className="text-muted-foreground flex items-center gap-1.5 text-xs">
                                <span
                                    className={cn('size-2 rounded-full', online ? 'bg-emerald-500' : 'bg-muted-foreground/50')}
                                    aria-hidden="true"
                                />
                                {online ? c.online : c.offline}
                            </span>
                        </div>
                        <Button asChild variant="ghost" size="icon" className="size-10" aria-label={c.call}>
                            <a href={contact.phone.href}>
                                <Phone />
                            </a>
                        </Button>
                        {contact.whatsapp && (
                            <Button asChild variant="ghost" size="icon" className="size-10" aria-label={c.whatsapp}>
                                <a href={contact.whatsapp} target="_blank" rel="noopener noreferrer">
                                    <MessageCircle />
                                </a>
                            </Button>
                        )}
                        <Button variant="ghost" size="icon" className="size-10" onClick={() => setOpen(false)} aria-label={c.close}>
                            <X />
                        </Button>
                    </div>

                    {token && chat ? (
                        <Thread
                            chat={chat}
                            online={online}
                            locale={locale}
                            error={error}
                            onNewChat={startNew}
                            onSend={async (body) => {
                                setError(null);
                                try {
                                    const { data } = await axios.post(`/chat/${token}/messages`, { body });
                                    setChat((prev) => (prev ? { ...prev, status: 'open', messages: [...prev.messages, data.message] } : prev));
                                    lastId.current = data.message.id;
                                    return true;
                                } catch (e) {
                                    failed(e);
                                    return false;
                                }
                            }}
                        />
                    ) : (
                        <StartForm
                            online={online}
                            onStart={async (fields) => {
                                setError(null);
                                const { data } = await axios.post('/chat/start', { ...fields, locale, page: window.location.pathname });
                                writeToken(data.token);
                                setToken(data.token);
                                merge(data, true);
                            }}
                        />
                    )}
                </div>
            )}
        </>
    );
}

function StartForm({ online, onStart }: { online: boolean; onStart: (f: Record<string, string>) => Promise<void> }) {
    const { t } = useI18n();
    const c = t.chat;
    const [fields, setFields] = useState({ name: '', phone: '', message: '', website: '' });
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [busy, setBusy] = useState(false);
    const [failed, setFailed] = useState<string | null>(null);

    const submit = async (e: FormEvent) => {
        e.preventDefault();
        setBusy(true);
        setErrors({});
        setFailed(null);
        try {
            await onStart(fields);
        } catch (err) {
            const res = (err as AxiosError<{ errors?: Record<string, string[]> }>).response;
            if (res?.status === 422 && res.data.errors) {
                setErrors(Object.fromEntries(Object.entries(res.data.errors).map(([k, v]) => [k, v[0]])));
            } else {
                setFailed(res?.status === 429 ? c.tooMany : c.error);
            }
        } finally {
            setBusy(false);
        }
    };

    const field = (key: 'name' | 'phone', label: string, props: Record<string, string> = {}) => (
        <div className="flex flex-col gap-1.5">
            <Label htmlFor={`chat-${key}`} className="text-sm font-bold">
                {label}
            </Label>
            <Input
                id={`chat-${key}`}
                value={fields[key]}
                onChange={(e) => setFields({ ...fields, [key]: e.target.value })}
                aria-invalid={!!errors[key]}
                className="h-11"
                {...props}
            />
            {errors[key] && <p className="text-destructive text-sm">{errors[key]}</p>}
        </div>
    );

    return (
        <form onSubmit={submit} noValidate className="relative flex flex-1 flex-col gap-4 overflow-y-auto p-4">
            <p className="bg-secondary rounded-2xl p-4 text-sm leading-7">{online ? c.intro : c.offlineNote}</p>

            <div aria-hidden="true" className="pointer-events-none absolute start-0 top-0 h-px w-px overflow-hidden opacity-0">
                <input tabIndex={-1} autoComplete="off" value={fields.website} onChange={(e) => setFields({ ...fields, website: e.target.value })} />
            </div>

            {field('name', c.name, { autoComplete: 'name' })}
            {field('phone', c.phone, { type: 'tel', inputMode: 'tel', dir: 'ltr', autoComplete: 'tel', className: 'h-11 rtl:text-right' })}

            <div className="flex flex-col gap-1.5">
                <Label htmlFor="chat-message" className="text-sm font-bold">
                    {c.message}
                </Label>
                <Textarea
                    id="chat-message"
                    rows={3}
                    value={fields.message}
                    onChange={(e) => setFields({ ...fields, message: e.target.value })}
                    placeholder={c.messagePlaceholder}
                    aria-invalid={!!errors.message}
                />
                {errors.message && <p className="text-destructive text-sm">{errors.message}</p>}
            </div>

            {failed && (
                <p role="alert" className="text-destructive text-sm">
                    {failed}
                </p>
            )}

            <Button type="submit" size="lg" className="mt-auto h-12 font-bold" disabled={busy}>
                {busy ? c.sending : c.start}
            </Button>
        </form>
    );
}

function Thread({
    chat,
    online,
    locale,
    error,
    onSend,
    onNewChat,
}: {
    chat: ChatState;
    online: boolean;
    locale: 'fa' | 'en';
    error: string | null;
    onSend: (body: string) => Promise<boolean>;
    onNewChat: () => void;
}) {
    const { t } = useI18n();
    const c = t.chat;
    const [text, setText] = useState('');
    const [busy, setBusy] = useState(false);
    const box = useRef<HTMLDivElement>(null);
    const time = new Intl.DateTimeFormat(locale === 'fa' ? 'fa-IR' : 'en-GB', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Tehran' });

    // Scroll to the newest message
    useEffect(() => {
        box.current?.scrollTo({ top: box.current.scrollHeight });
    }, [chat.messages.length]);

    const send = async (e?: FormEvent) => {
        e?.preventDefault();
        const body = text.trim();
        if (!body || busy) return;
        setBusy(true);
        if (await onSend(body)) setText('');
        setBusy(false);
    };

    return (
        <>
            <div ref={box} className="flex flex-1 flex-col gap-2 overflow-y-auto p-4" aria-live="polite">
                {!online && chat.messages.every((m) => m.from === 'visitor') && (
                    <p className="bg-secondary mb-2 rounded-2xl p-3 text-sm leading-7">{c.offlineNote}</p>
                )}
                {chat.messages.map((m) => (
                    <div
                        key={m.id}
                        className={cn(
                            'max-w-[85%] rounded-2xl px-3.5 py-2 leading-7 break-words whitespace-pre-wrap',
                            m.from === 'visitor'
                                ? 'bg-primary text-primary-foreground self-end rounded-ee-md'
                                : 'bg-secondary self-start rounded-es-md',
                        )}
                    >
                        {m.from === 'agent' && <span className="text-muted-foreground block text-xs font-bold">{c.agent}</span>}
                        <span dir="auto">{m.body}</span>
                        <time dateTime={m.at} className="mt-0.5 block text-[0.7rem] opacity-70">
                            {time.format(new Date(m.at))}
                        </time>
                    </div>
                ))}
                {chat.status === 'closed' && (
                    <div className="text-muted-foreground mt-2 flex flex-col items-center gap-2 text-center text-sm">
                        <p>{c.closedNote}</p>
                        <Button variant="link" size="sm" onClick={onNewChat}>
                            {c.newChat}
                        </Button>
                    </div>
                )}
            </div>

            {error && (
                <p role="alert" className="text-destructive px-4 pb-1 text-sm">
                    {error}
                </p>
            )}

            <form onSubmit={send} className="bg-card flex items-end gap-2 border-t p-3">
                <Textarea
                    value={text}
                    onChange={(e) => setText(e.target.value)}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            send();
                        }
                    }}
                    rows={1}
                    placeholder={c.placeholder}
                    aria-label={c.placeholder}
                    className="max-h-32 min-h-11 flex-1 resize-none"
                />
                <Button type="submit" size="icon" className="size-11 shrink-0" disabled={busy || !text.trim()} aria-label={c.send}>
                    <SendHorizontal className="rtl:-scale-x-100" />
                </Button>
            </form>
        </>
    );
}
