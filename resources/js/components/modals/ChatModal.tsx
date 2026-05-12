import React, { JSX, useState, useRef, useEffect } from 'react';
import { X, Send, Loader, Check, CheckCircle, MessageCircle } from 'lucide-react';
import type { PlanDetail, IdeaGroup } from '@/types/ideas';

interface Message {
    id: string;
    role: 'user' | 'assistant';
    content: string;
    timestamp: Date;
}

interface AIChatModalProps {
    isOpen: boolean;
    plan: PlanDetail;
    ideaGroups: IdeaGroup[];
    onClose: () => void;
}

export default function AIChatModal({
    isOpen,
    plan,
    ideaGroups,
    onClose,
}: AIChatModalProps): JSX.Element | null {
    const [messages, setMessages] = useState<Message[]>([]);
    const [inputValue, setInputValue] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [selectedGroupIds, setSelectedGroupIds] = useState<number[]>([]);
    const [showGroupSelector, setShowGroupSelector] = useState(true);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    if (!isOpen) return null;

    const handleGroupToggle = (groupId: number) => {
        setSelectedGroupIds((prev) =>
            prev.includes(groupId) ? prev.filter((id) => id !== groupId) : [...prev, groupId]
        );
    };

    const handleSelectAll = () => {
        if (selectedGroupIds.length === ideaGroups.length) {
            setSelectedGroupIds([]);
        } else {
            setSelectedGroupIds(ideaGroups.map((g) => g.id));
        }
    };

    const handleStartChat = () => {
        setShowGroupSelector(false);
        // Initialize chat with welcome message
        const selectedGroups = ideaGroups.filter((g) => selectedGroupIds.includes(g.id));
        const groupInfo =
            selectedGroupIds.length === 0
                ? 'without any specific idea groups'
                : selectedGroupIds.length === ideaGroups.length
                    ? 'with all idea groups'
                    : `with ${selectedGroupIds.length} selected idea group(s)`;

        const welcomeMessage: Message = {
            id: Date.now().toString(),
            role: 'assistant',
            content: `I'm ready to help with your "${plan.name}" plan ${groupInfo}. How can I assist you today?`,
            timestamp: new Date(),
        };

        setMessages([welcomeMessage]);
    };

    const handleSendMessage = async () => {
        if (!inputValue.trim()) return;

        // Add user message
        const userMessage: Message = {
            id: Date.now().toString(),
            role: 'user',
            content: inputValue,
            timestamp: new Date(),
        };

        setMessages((prev) => [...prev, userMessage]);
        setInputValue('');
        setIsLoading(true);

        // Simulate API call to Claude
        setTimeout(() => {
            const selectedGroups = ideaGroups.filter((g) => selectedGroupIds.includes(g.id));
            const context = {
                plan: {
                    id: plan.id,
                    name: plan.name,
                    description: plan.description,
                },
                selectedGroups:
                    selectedGroupIds.length === 0
                        ? []
                        : selectedGroups.map((g) => ({
                            id: g.id,
                            name: g.name,
                            ideasCount: g.ideas.length,
                        })),
            };

            // This would be replaced with actual API call
            const assistantMessage: Message = {
                id: (Date.now() + 1).toString(),
                role: 'assistant',
                content: `I'll help you with that! Based on the context of "${plan.name}" ${selectedGroupIds.length === 0 ? '(no specific groups selected)' : `and the ${selectedGroupIds.length === ideaGroups.length ? 'all' : selectedGroupIds.length} selected group(s)`}, here's what I can do:\n\n1. Provide insights on your ideas\n2. Suggest improvements\n3. Help prioritize tasks\n4. Generate new ideas\n\nWhat would you like to focus on?`,
                timestamp: new Date(),
            };

            setMessages((prev) => [...prev, assistantMessage]);
            setIsLoading(false);
        }, 500);
    };

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-slate-900 rounded-xl border border-slate-700 w-full max-w-2xl h-[600px] flex flex-col shadow-2xl">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-slate-700">
                    <div className="flex items-center gap-3">
                        <MessageCircle size={24} className="text-purple-400" />
                        <div>
                            <h2 className="text-xl font-bold text-white">AI Chat Assistant</h2>
                            <p className="text-sm text-slate-400">{plan.name}</p>
                        </div>
                    </div>
                    <button
                        onClick={onClose}
                        className="text-slate-400 hover:text-white transition"
                    >
                        <X size={24} />
                    </button>
                </div>

                {/* Content */}
                {showGroupSelector ? (
                    // Group Selection Screen
                    <div className="flex-1 overflow-y-auto p-6 space-y-6">
                        <div>
                            <h3 className="text-lg font-semibold text-white mb-4">
                                Select Idea Groups to Include
                            </h3>
                            <p className="text-sm text-slate-400 mb-6">
                                Choose which groups you'd like me to consider in our conversation. You can select all, none, or specific groups.
                            </p>
                        </div>

                        {/* Select All Button */}
                        <div className="flex items-center gap-3">
                            <button
                                onClick={handleSelectAll}
                                className={`px-4 py-2 rounded-lg font-medium transition ${selectedGroupIds.length === ideaGroups.length
                                        ? 'bg-purple-500 text-white'
                                        : 'bg-slate-700/50 text-slate-300 hover:bg-slate-700'
                                    }`}
                            >
                                {selectedGroupIds.length === ideaGroups.length ? 'Deselect All' : 'Select All'}
                            </button>
                            <span className="text-sm text-slate-400">
                                {selectedGroupIds.length} of {ideaGroups.length} selected
                            </span>
                        </div>

                        {/* Groups List */}
                        <div className="space-y-3">
                            {ideaGroups.map((group) => {
                                const isSelected = selectedGroupIds.includes(group.id);
                                return (
                                    <button
                                        key={group.id}
                                        onClick={() => handleGroupToggle(group.id)}
                                        className={`w-full flex items-center gap-4 p-4 rounded-lg border-2 transition ${isSelected
                                                ? 'border-purple-500 bg-purple-500/10'
                                                : 'border-slate-600 bg-slate-700/30 hover:border-slate-500'
                                            }`}
                                    >
                                        <div
                                            className={`w-6 h-6 rounded border-2 flex items-center justify-center flex-shrink-0 transition ${isSelected
                                                    ? 'border-purple-500 bg-purple-500'
                                                    : 'border-slate-500 bg-transparent'
                                                }`}
                                        >
                                            {isSelected && <Check size={16} className="text-white" />}
                                        </div>
                                        <div className="flex-1 text-left">
                                            <p className="font-medium text-white">{group.name}</p>
                                            <p className="text-sm text-slate-400">
                                                {group.ideas.length} idea{group.ideas.length !== 1 ? 's' : ''}
                                            </p>
                                        </div>
                                        {isSelected && (
                                            <CheckCircle size={20} className="text-purple-400 flex-shrink-0" />
                                        )}
                                    </button>
                                );
                            })}
                        </div>

                        {/* Start Chat Button */}
                        <button
                            onClick={handleStartChat}
                            className="w-full mt-6 px-6 py-3 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-semibold transition"
                        >
                            Start Chat
                        </button>
                    </div>
                ) : (
                    <>
                        {/* Chat Messages */}
                        <div className="flex-1 overflow-y-auto p-6 space-y-4">
                            {messages.map((message) => (
                                <div
                                    key={message.id}
                                    className={`flex ${message.role === 'user' ? 'justify-end' : 'justify-start'}`}
                                >
                                    <div
                                        className={`max-w-xs px-4 py-3 rounded-lg ${message.role === 'user'
                                                ? 'bg-purple-600 text-white'
                                                : 'bg-slate-700 text-slate-100'
                                            }`}
                                    >
                                        <p className="text-sm whitespace-pre-wrap break-words">
                                            {message.content}
                                        </p>
                                    </div>
                                </div>
                            ))}
                            {isLoading && (
                                <div className="flex justify-start">
                                    <div className="bg-slate-700 text-slate-100 px-4 py-3 rounded-lg">
                                        <Loader size={20} className="animate-spin" />
                                    </div>
                                </div>
                            )}
                            <div ref={messagesEndRef} />
                        </div>

                        {/* Input Area */}
                        <div className="border-t border-slate-700 p-4 bg-slate-800/50">
                            <div className="flex gap-3">
                                <button
                                    onClick={() => {
                                        setShowGroupSelector(true);
                                        setMessages([]);
                                    }}
                                    className="px-3 py-2 rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 text-sm font-medium transition"
                                    title="Change selected groups"
                                >
                                    Change Groups
                                </button>
                                <div className="flex-1 flex gap-2">
                                    <input
                                        type="text"
                                        value={inputValue}
                                        onChange={(e) => setInputValue(e.target.value)}
                                        onKeyPress={(e) => {
                                            if (e.key === 'Enter' && !e.shiftKey) {
                                                e.preventDefault();
                                                handleSendMessage();
                                            }
                                        }}
                                        placeholder="Ask me anything..."
                                        className="flex-1 px-4 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white placeholder-slate-400 focus:outline-none focus:border-purple-500 transition"
                                    />
                                    <button
                                        onClick={handleSendMessage}
                                        disabled={!inputValue.trim() || isLoading}
                                        className="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 disabled:bg-slate-600 text-white font-medium transition flex items-center gap-2"
                                    >
                                        <Send size={18} />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
}