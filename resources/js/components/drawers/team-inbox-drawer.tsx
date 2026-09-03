import * as React from "react"
import {
  LoaderCircleIcon,
  PaperclipIcon,
  SendHorizontalIcon,
  XIcon,
} from "lucide-react"

import {
  Drawer,
  DrawerClose,
  DrawerContent,
  DrawerDescription,
  DrawerHeader,
  DrawerTitle,
} from "@/components/ui/drawer"
import { Button } from "@/components/ui/button"
import {
  MessageScroller,
  MessageScrollerButton,
  MessageScrollerContent,
  MessageScrollerItem,
  MessageScrollerProvider,
  MessageScrollerViewport,
} from "@/components/ui/message-scroller"
import {
  fetchTeamChatBootstrap,
  fetchTeamChatMessages,
  readTeamChatConfig,
  sendTeamChatMessage,
  type TeamChatBootstrap,
  type TeamChatConfig,
  type TeamChatConversation,
  type TeamChatMessage,
  type TeamChatUser,
} from "@/lib/team-chat-api"
import { cn } from "@/lib/utils"

declare global {
  interface Window {
    Alpine?: {
      store: (name: string) => {
        open: (name: string) => void
        close: (name: string) => void
      }
    }
  }
}

type ActiveChat = {
  user: TeamChatUser
  conversationId: number | null
}

function formatMessageTime(value: string): string {
  const date = new Date(value)

  return new Intl.DateTimeFormat(undefined, {
    hour: "numeric",
    minute: "2-digit",
  }).format(date)
}

function formatPreview(message: TeamChatMessage | null): string {
  if (!message) {
    return "Start a conversation"
  }

  if (message.body) {
    return message.body
  }

  if (message.attachments.length > 0) {
    return message.attachments.length === 1
      ? "Attachment"
      : `${message.attachments.length} attachments`
  }

  return "Message"
}

function TeamChatMessageBubble({ message }: { message: TeamChatMessage }) {
  return (
    <div
      className={cn(
        "flex w-full",
        message.is_mine ? "justify-end" : "justify-start"
      )}
    >
      <div
        className={cn(
          "max-w-[85%] space-y-2 rounded-2xl px-3 py-2 text-sm shadow-sm",
          message.is_mine
            ? "bg-primary text-primary-foreground"
            : "border border-border bg-background text-foreground"
        )}
      >
        {!message.is_mine && (
          <p className="text-xs font-medium opacity-80">{message.sender_name}</p>
        )}

        {message.body && <p className="whitespace-pre-wrap break-words">{message.body}</p>}

        {message.attachments.length > 0 && (
          <div className="space-y-1">
            {message.attachments.map((attachment) => (
              <a
                key={attachment.id}
                href={attachment.url}
                className={cn(
                  "flex items-center gap-2 rounded-md px-2 py-1 text-xs underline-offset-2 hover:underline",
                  message.is_mine
                    ? "bg-primary-foreground/10 text-primary-foreground"
                    : "bg-muted text-foreground"
                )}
              >
                <PaperclipIcon className="size-3.5 shrink-0" />
                <span className="truncate">{attachment.name}</span>
              </a>
            ))}
          </div>
        )}

        <p
          className={cn(
            "text-[10px]",
            message.is_mine ? "text-primary-foreground/70" : "text-muted-foreground"
          )}
        >
          {formatMessageTime(message.created_at)}
        </p>
      </div>
    </div>
  )
}

function TeamInboxComposer({
  disabled,
  onSend,
}: {
  disabled: boolean
  onSend: (body: string, attachments: File[]) => Promise<void>
}) {
  const [body, setBody] = React.useState("")
  const [attachments, setAttachments] = React.useState<File[]>([])
  const [sending, setSending] = React.useState(false)
  const fileInputRef = React.useRef<HTMLInputElement>(null)

  const canSend =
    !disabled && !sending && (body.trim() !== "" || attachments.length > 0)

  async function handleSend() {
    if (!canSend) {
      return
    }

    setSending(true)

    try {
      await onSend(body, attachments)
      setBody("")
      setAttachments([])
    } finally {
      setSending(false)
    }
  }

  function handleFileChange(event: React.ChangeEvent<HTMLInputElement>) {
    const files = Array.from(event.target.files ?? [])

    if (files.length === 0) {
      return
    }

    setAttachments((current) => [...current, ...files].slice(0, 5))
    event.target.value = ""
  }

  return (
    <div className="shrink-0 border-t border-border bg-background p-3">
      {attachments.length > 0 && (
        <div className="mb-2 flex flex-wrap gap-2">
          {attachments.map((file, index) => (
            <div
              key={`${file.name}-${index}`}
              className="inline-flex max-w-full items-center gap-1 rounded-full border border-border bg-muted px-2 py-1 text-xs"
            >
              <PaperclipIcon className="size-3 shrink-0" />
              <span className="truncate">{file.name}</span>
              <button
                type="button"
                className="rounded-full p-0.5 hover:bg-background"
                onClick={() =>
                  setAttachments((current) =>
                    current.filter((_, fileIndex) => fileIndex !== index)
                  )
                }
                aria-label={`Remove ${file.name}`}
              >
                <XIcon className="size-3" />
              </button>
            </div>
          ))}
        </div>
      )}

      <div className="flex items-end gap-2">
        <input
          ref={fileInputRef}
          type="file"
          multiple
          className="hidden"
          onChange={handleFileChange}
        />

        <Button
          type="button"
          variant="outline"
          size="icon-sm"
          disabled={disabled || sending || attachments.length >= 5}
          onClick={() => fileInputRef.current?.click()}
          aria-label="Attach files"
        >
          <PaperclipIcon />
        </Button>

        <textarea
          value={body}
          onChange={(event) => setBody(event.target.value)}
          rows={2}
          placeholder="Write a message..."
          disabled={disabled || sending}
          className="min-h-10 max-h-32 min-w-0 flex-1 resize-y rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 disabled:opacity-50"
          onKeyDown={(event) => {
            if (event.key === "Enter" && !event.shiftKey) {
              event.preventDefault()
              void handleSend()
            }
          }}
        />

        <Button
          type="button"
          size="icon-sm"
          disabled={!canSend}
          onClick={() => void handleSend()}
          aria-label="Send message"
        >
          {sending ? <LoaderCircleIcon className="animate-spin" /> : <SendHorizontalIcon />}
        </Button>
      </div>
    </div>
  )
}

function TeamInboxChatPanel({
  activeChat,
  messages,
  loading,
  onSend,
}: {
  activeChat: ActiveChat | null
  messages: TeamChatMessage[]
  loading: boolean
  onSend: (body: string, attachments: File[]) => Promise<void>
}) {
  if (!activeChat) {
    return (
      <div className="flex flex-1 items-center justify-center px-6 text-center text-sm text-muted-foreground">
        Select a team member to start chatting.
      </div>
    )
  }

  return (
    <div className="flex min-h-0 min-w-0 flex-1 flex-col">
      <div className="flex shrink-0 items-center justify-between border-b border-border px-4 py-3">
        <div>
          <p className="font-medium text-foreground">{activeChat.user.name}</p>
          <p className="text-xs text-muted-foreground">Team chat</p>
        </div>
      </div>

      <MessageScrollerProvider autoScroll defaultScrollPosition="end">
        <MessageScroller className="min-h-0 flex-1">
          <MessageScrollerViewport className="px-4 py-4">
            {loading ? (
              <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                <LoaderCircleIcon className="mr-2 size-4 animate-spin" />
                Loading messages...
              </div>
            ) : messages.length === 0 ? (
              <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                No messages yet. Say hello to {activeChat.user.name}.
              </div>
            ) : (
              <MessageScrollerContent className="gap-3">
                {messages.map((message, index) => (
                  <MessageScrollerItem
                    key={message.id}
                    messageId={String(message.id)}
                    scrollAnchor={index === messages.length - 1}
                  >
                    <TeamChatMessageBubble message={message} />
                  </MessageScrollerItem>
                ))}
              </MessageScrollerContent>
            )}
          </MessageScrollerViewport>
          <MessageScrollerButton direction="end" />
        </MessageScroller>
      </MessageScrollerProvider>

      <TeamInboxComposer disabled={loading} onSend={onSend} />
    </div>
  )
}

export function TeamInboxDrawer() {
  const rootRef = React.useRef<HTMLElement | null>(null)
  const [config, setConfig] = React.useState<TeamChatConfig | null>(null)
  const [open, setOpen] = React.useState(false)
  const [loadingBootstrap, setLoadingBootstrap] = React.useState(false)
  const [loadingMessages, setLoadingMessages] = React.useState(false)
  const [bootstrap, setBootstrap] = React.useState<TeamChatBootstrap | null>(null)
  const [activeChat, setActiveChat] = React.useState<ActiveChat | null>(null)
  const [messages, setMessages] = React.useState<TeamChatMessage[]>([])

  React.useEffect(() => {
    const root = document.getElementById("team-inbox-drawer-root")

    if (!root) {
      return
    }

    rootRef.current = root
    setConfig(readTeamChatConfig(root))
  }, [])

  React.useEffect(() => {
    const handleOpen = (event: Event) => {
      if ((event as CustomEvent<string>).detail === "team-inbox") {
        setOpen(true)
      }
    }

    const handleClose = (event: Event) => {
      if ((event as CustomEvent<string>).detail === "team-inbox") {
        setOpen(false)
      }
    }

    window.addEventListener("open-drawer", handleOpen)
    window.addEventListener("close-drawer", handleClose)

    return () => {
      window.removeEventListener("open-drawer", handleOpen)
      window.removeEventListener("close-drawer", handleClose)
    }
  }, [])

  React.useEffect(() => {
    const store = window.Alpine?.store("drawers")

    if (!store) {
      return
    }

    if (open) {
      store.open("team-inbox")
    } else {
      store.close("team-inbox")
    }
  }, [open])

  const loadBootstrap = React.useCallback(async () => {
    if (!config) {
      return
    }

    setLoadingBootstrap(true)

    try {
      const payload = await fetchTeamChatBootstrap(config)
      setBootstrap(payload)
    } finally {
      setLoadingBootstrap(false)
    }
  }, [config])

  React.useEffect(() => {
    if (!open || !config) {
      return
    }

    void loadBootstrap()
  }, [open, config, loadBootstrap])

  const selectUser = React.useCallback(
    async (user: TeamChatUser, conversation?: TeamChatConversation | null) => {
      if (!config) {
        return
      }

      const conversationId = conversation?.id ?? null
      setActiveChat({ user, conversationId })
      setMessages([])
      setLoadingMessages(true)

      try {
        if (conversationId) {
          const fetchedMessages = await fetchTeamChatMessages(config, conversationId)
          setMessages(fetchedMessages)
        }
      } finally {
        setLoadingMessages(false)
      }
    },
    [config]
  )

  const handleSend = React.useCallback(
    async (body: string, attachments: File[]) => {
      if (!config || !activeChat) {
        return
      }

      const result = await sendTeamChatMessage(
        config,
        activeChat.user.id,
        body,
        attachments
      )

      setMessages((current) => [...current, result.message])
      setActiveChat((current) =>
        current
          ? {
              ...current,
              conversationId: result.conversation_id,
            }
          : current
      )

      await loadBootstrap()
    },
    [activeChat, config, loadBootstrap]
  )

  const conversations = bootstrap?.conversations ?? []
  const users = bootstrap?.users ?? []

  const roster = React.useMemo(() => {
    const conversationByUserId = new Map<number, TeamChatConversation>()

    conversations.forEach((conversation) => {
      conversationByUserId.set(conversation.participant.id, conversation)
    })

    return users.map((user) => ({
      user,
      conversation: conversationByUserId.get(user.id) ?? null,
    }))
  }, [conversations, users])

  return (
    <Drawer open={open} onOpenChange={setOpen} swipeDirection="right">
      <DrawerContent
        overlayClassName="z-40 bg-navy/40 backdrop-blur-sm"
        className="h-dvh max-h-dvh sm:[--drawer-content-width:42rem] lg:[--drawer-content-width:48rem]"
      >
        <DrawerHeader className="border-b border-border pb-4">
          <div className="flex items-start justify-between gap-3">
            <div>
              <DrawerTitle>Team Inbox</DrawerTitle>
              <DrawerDescription>
                Chat with your team and share files.
              </DrawerDescription>
            </div>
            <DrawerClose
              render={<Button type="button" variant="ghost" size="icon-sm" />}
              aria-label="Close inbox"
            >
              <XIcon />
            </DrawerClose>
          </div>
        </DrawerHeader>

        <div className="flex min-h-0 flex-1 overflow-hidden">
          <aside className="flex w-40 shrink-0 flex-col border-r border-border bg-muted/20 sm:w-52">
            <div className="border-b border-border px-3 py-2 text-xs font-medium uppercase tracking-wide text-muted-foreground">
              Team
            </div>

            <div className="min-h-0 flex-1 overflow-y-auto p-2">
              {loadingBootstrap ? (
                <div className="flex items-center justify-center py-8 text-muted-foreground">
                  <LoaderCircleIcon className="size-4 animate-spin" />
                </div>
              ) : roster.length === 0 ? (
                <p className="px-2 py-4 text-xs text-muted-foreground">
                  No other team members yet.
                </p>
              ) : (
                <div className="space-y-1">
                  {roster.map(({ user, conversation }) => {
                    const isActive = activeChat?.user.id === user.id

                    return (
                      <button
                        key={user.id}
                        type="button"
                        onClick={() => void selectUser(user, conversation)}
                        className={cn(
                          "w-full rounded-lg px-3 py-2 text-left transition-colors",
                          isActive
                            ? "bg-primary text-primary-foreground"
                            : "hover:bg-muted"
                        )}
                      >
                        <p className="truncate text-sm font-medium">{user.name}</p>
                        <p
                          className={cn(
                            "truncate text-xs",
                            isActive
                              ? "text-primary-foreground/80"
                              : "text-muted-foreground"
                          )}
                        >
                          {formatPreview(conversation?.last_message ?? null)}
                        </p>
                      </button>
                    )
                  })}
                </div>
              )}
            </div>
          </aside>

          <TeamInboxChatPanel
            activeChat={activeChat}
            messages={messages}
            loading={loadingMessages}
            onSend={handleSend}
          />
        </div>
      </DrawerContent>
    </Drawer>
  )
}

export function TeamInboxDrawerHost() {
  const root = document.getElementById("team-inbox-drawer-root")

  if (!root) {
    return null
  }

  return <TeamInboxDrawer />
}
