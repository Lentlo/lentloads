import { defineStore } from 'pinia'
import api from '@/services/api'

export const useMessagesStore = defineStore('messages', {
  state: () => ({
    conversations: [],
    currentConversation: null,
    messages: [],
    loading: false,
    loadingMessages: false,
    unreadCount: 0,
  }),

  getters: {
    hasUnread: (state) => state.unreadCount > 0,

    sortedConversations: (state) => {
      return [...state.conversations].sort((a, b) => {
        const dateA = new Date(a.last_message?.created_at || a.updated_at)
        const dateB = new Date(b.last_message?.created_at || b.updated_at)
        return dateB - dateA
      })
    },
  },

  actions: {
    async fetchConversations() {
      this.loading = true
      try {
        const response = await api.get('/conversations')
        this.conversations = response.data.data
        this.updateUnreadCount()
        return response.data
      } finally {
        this.loading = false
      }
    },

    async fetchConversation(id) {
      this.loadingMessages = true
      try {
        const response = await api.get(`/conversations/${id}`)
        this.currentConversation = response.data.data
        this.messages = response.data.data.messages || []

        // Mark as read
        if (this.currentConversation.unread_count > 0) {
          await this.markAsRead(id)
        }

        return response.data
      } finally {
        this.loadingMessages = false
      }
    },

    async sendMessage(conversationId, content, offer = null) {
      const payload = { content }
      if (offer) {
        payload.offer_amount = offer
      }

      const response = await api.post(`/conversations/${conversationId}/messages`, payload)
      const message = response.data.data

      // Add to messages list
      this.messages.push(message)

      // Update conversation's last message
      const conversation = this.conversations.find(c => c.id === conversationId)
      if (conversation) {
        conversation.last_message = message
        conversation.updated_at = message.created_at
      }

      return message
    },

    async startConversation(listingId, receiverId, message, offer = null) {
      const payload = {
        listing_id: listingId,
        receiver_id: receiverId,
        message,
      }
      if (offer) {
        payload.offer_amount = offer
      }

      const response = await api.post('/conversations', payload)
      const conversation = response.data.data

      // Add to conversations list
      this.conversations.unshift(conversation)

      return conversation
    },

    async markAsRead(conversationId) {
      await api.post(`/conversations/${conversationId}/read`)

      const conversation = this.conversations.find(c => c.id === conversationId)
      if (conversation) {
        conversation.unread_count = 0
      }

      this.updateUnreadCount()
    },

    async acceptOffer(messageId) {
      const response = await api.post(`/messages/${messageId}/accept-offer`)

      // Update message status
      const message = this.messages.find(m => m.id === messageId)
      if (message) {
        message.offer_status = 'accepted'
      }

      return response.data
    },

    async rejectOffer(messageId) {
      const response = await api.post(`/messages/${messageId}/reject-offer`)

      // Update message status
      const message = this.messages.find(m => m.id === messageId)
      if (message) {
        message.offer_status = 'rejected'
      }

      return response.data
    },

    async blockUser(conversationId) {
      await api.post(`/conversations/${conversationId}/block`)

      const conversation = this.conversations.find(c => c.id === conversationId)
      if (conversation) {
        conversation.is_blocked = true
      }
    },

    async unblockUser(conversationId) {
      await api.post(`/conversations/${conversationId}/unblock`)

      const conversation = this.conversations.find(c => c.id === conversationId)
      if (conversation) {
        conversation.is_blocked = false
      }
    },

    async deleteConversation(conversationId) {
      await api.delete(`/conversations/${conversationId}`)
      this.conversations = this.conversations.filter(c => c.id !== conversationId)

      if (this.currentConversation?.id === conversationId) {
        this.currentConversation = null
        this.messages = []
      }
    },

    async fetchUnreadCount() {
      try {
        const response = await api.get('/conversations/unread-count')
        this.unreadCount = response.data.count
      } catch (error) {
        console.error('Failed to fetch unread count')
      }
    },

    updateUnreadCount() {
      this.unreadCount = this.conversations.reduce((total, conv) => {
        return total + (conv.unread_count || 0)
      }, 0)
    },

    // Real-time message handling (for WebSocket integration)
    handleNewMessage(message) {
      // Add message if it's for the current conversation
      if (this.currentConversation?.id === message.conversation_id) {
        this.messages.push(message)
      }

      // Update conversation
      const conversation = this.conversations.find(c => c.id === message.conversation_id)
      if (conversation) {
        conversation.last_message = message
        conversation.updated_at = message.created_at
        if (this.currentConversation?.id !== message.conversation_id) {
          conversation.unread_count = (conversation.unread_count || 0) + 1
        }
      }

      this.updateUnreadCount()
    },

    clearCurrentConversation() {
      this.currentConversation = null
      this.messages = []
    },
  },
})
