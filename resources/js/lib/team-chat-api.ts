export type TeamChatUser = {
  id: number
  name: string
}

export type TeamChatAttachment = {
  id: string
  name: string
  url: string
  mime_type: string | null
  size: number
}

export type TeamChatMessage = {
  id: number
  sender_id: number
  sender_name: string
  body: string | null
  attachments: TeamChatAttachment[]
  created_at: string
  is_mine: boolean
}

export type TeamChatConversation = {
  id: number
  participant: TeamChatUser
  last_message: TeamChatMessage | null
}

export type TeamChatBootstrap = {
  current_user: TeamChatUser
  users: TeamChatUser[]
  conversations: TeamChatConversation[]
}

export type TeamChatConfig = {
  bootstrapUrl: string
  messagesUrlTemplate: string
  storeUrl: string
  currentUserId: number
}

export function readTeamChatConfig(root: HTMLElement): TeamChatConfig {
  return {
    bootstrapUrl: root.dataset.bootstrapUrl ?? "",
    messagesUrlTemplate: root.dataset.messagesUrlTemplate ?? "",
    storeUrl: root.dataset.storeUrl ?? "",
    currentUserId: Number(root.dataset.currentUserId ?? 0),
  }
}

function csrfToken(): string {
  return (
    document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ??
    ""
  )
}

async function parseJson<T>(response: Response): Promise<T> {
  if (!response.ok) {
    throw new Error(`Request failed with status ${response.status}`)
  }

  return (await response.json()) as T
}

export async function fetchTeamChatBootstrap(
  config: TeamChatConfig
): Promise<TeamChatBootstrap> {
  const response = await fetch(config.bootstrapUrl, {
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  })

  return parseJson<TeamChatBootstrap>(response)
}

export async function fetchTeamChatMessages(
  config: TeamChatConfig,
  conversationId: number
): Promise<TeamChatMessage[]> {
  const url = config.messagesUrlTemplate.replace("__ID__", String(conversationId))
  const response = await fetch(url, {
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  })

  const payload = await parseJson<{ messages: TeamChatMessage[] }>(response)

  return payload.messages
}

export async function sendTeamChatMessage(
  config: TeamChatConfig,
  recipientId: number,
  body: string,
  attachments: File[]
): Promise<{ conversation_id: number; message: TeamChatMessage }> {
  const formData = new FormData()
  formData.append("recipient_id", String(recipientId))

  if (body.trim() !== "") {
    formData.append("body", body.trim())
  }

  attachments.forEach((file) => {
    formData.append("attachments[]", file)
  })

  const response = await fetch(config.storeUrl, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
      "X-CSRF-TOKEN": csrfToken(),
    },
    body: formData,
  })

  return parseJson<{ conversation_id: number; message: TeamChatMessage }>(
    response
  )
}

export function messagesUrlForConversation(
  config: TeamChatConfig,
  conversationId: number
): string {
  return config.messagesUrlTemplate.replace("__ID__", String(conversationId))
}
