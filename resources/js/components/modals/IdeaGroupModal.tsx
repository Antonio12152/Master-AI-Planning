import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';

interface IdeaGroupModalProps {
    isOpen: boolean;
    mode: 'create' | 'edit';
    groupData?: {
        id: number;
        name: string;
        description?: string;
        color?: string;
    } | null;
    onClose: () => void;
    onSubmit: (data: { name: string; description?: string; color?: string }) => Promise<void>;
    isLoading?: boolean;
}

export default function IdeaGroupModal({
    isOpen,
    mode,
    groupData,
    onClose,
    onSubmit,
    isLoading = false,
}: IdeaGroupModalProps): React.JSX.Element | null {
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        color: '#3b82f6',
    });
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (groupData) {
            setFormData({
                name: groupData.name,
                description: groupData.description || '',
                color: groupData.color || '#3b82f6',
            });
        } else {
            setFormData({
                name: '',
                description: '',
                color: '#3b82f6',
            });
        }
        setError(null);
    }, [groupData, isOpen]);

    const handleSubmit = async () => {
        if (!formData.name.trim()) {
            setError('Group name is required');
            return;
        }

        try {
            setError(null);
            await onSubmit(formData);
            setFormData({ name: '', description: '', color: '#3b82f6' });
            onClose();
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Failed to save group');
        }
    };

    const handleClose = () => {
        setFormData({ name: '', description: '', color: '#3b82f6' });
        setError(null);
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-slate-800 rounded-xl border border-slate-700 p-8 w-full max-w-md">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-2xl font-semibold">
                        {mode === 'create' ? 'Create New Group' : 'Edit Group'}
                    </h2>
                    <button
                        onClick={handleClose}
                        disabled={isLoading}
                        className="text-slate-400 hover:text-white transition disabled:opacity-50"
                    >
                        <X size={24} />
                    </button>
                </div>

                {error && (
                    <div className="mb-4 p-3 rounded-lg bg-red-500/20 border border-red-500/50 text-red-400 text-sm">
                        {error}
                    </div>
                )}

                <div className="space-y-4 mb-6">
                    {/* Group Name */}
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">
                            Group Name
                        </label>
                        <input
                            type="text"
                            placeholder="Enter group name..."
                            value={formData.name}
                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                            disabled={isLoading}
                            className="w-full px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 disabled:opacity-50"
                        />
                    </div>

                    {/* Description */}
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">
                            Description (optional)
                        </label>
                        <textarea
                            placeholder="Enter group description..."
                            value={formData.description}
                            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                            disabled={isLoading}
                            rows={3}
                            className="w-full px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 disabled:opacity-50 resize-none"
                        />
                    </div>

                    {/* Color Picker */}
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">
                            Color
                        </label>
                        <div className="flex gap-2">
                            <input
                                type="color"
                                value={formData.color}
                                onChange={(e) => setFormData({ ...formData, color: e.target.value })}
                                disabled={isLoading}
                                className="w-12 h-10 rounded-lg cursor-pointer disabled:opacity-50"
                            />
                            <input
                                type="text"
                                value={formData.color}
                                onChange={(e) => setFormData({ ...formData, color: e.target.value })}
                                disabled={isLoading}
                                placeholder="#3b82f6"
                                className="flex-1 px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 disabled:opacity-50"
                            />
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex gap-3">
                    <button
                        onClick={handleClose}
                        disabled={isLoading}
                        className="flex-1 px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600/50 text-slate-300 hover:text-white hover:bg-slate-700 transition disabled:opacity-50 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        onClick={handleSubmit}
                        disabled={isLoading}
                        className="flex-1 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        {isLoading ? (
                            <>
                                <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                Saving...
                            </>
                        ) : mode === 'create' ? (
                            'Create Group'
                        ) : (
                            'Update Group'
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}
