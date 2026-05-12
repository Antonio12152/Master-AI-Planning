import React, { JSX, useState } from 'react';
import { X } from 'lucide-react';

interface AddIdeaModalProps {
    isOpen: boolean;
    groupName: string;
    onClose: () => void;
    onSubmit: (idea: string) => void;
}

export default function AddIdeaModal({ isOpen, groupName, onClose, onSubmit }: AddIdeaModalProps): JSX.Element | null {
    const [ideaText, setIdeaText] = useState('');

    const handleSubmit = () => {
        if (ideaText.trim()) {
            onSubmit(ideaText);
            setIdeaText('');
        }
    };

    const handleClose = () => {
        setIdeaText('');
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-slate-800 rounded-xl border border-slate-700 p-8 w-full max-w-md">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-2xl font-semibold">Add Idea</h2>
                    <button
                        onClick={handleClose}
                        className="text-slate-400 hover:text-white transition"
                    >
                        <X size={24} />
                    </button>
                </div>

                <p className="text-slate-400 mb-6">
                    Add new idea to "<span className="text-white font-semibold">{groupName}</span>"
                </p>

                <div className="mb-6">
                    <label className="block text-sm font-medium text-slate-300 mb-2">
                        Idea
                    </label>
                    <textarea
                        placeholder="Enter your idea..."
                        value={ideaText}
                        onChange={(e) => setIdeaText(e.target.value)}
                        rows={4}
                        className="w-full px-4 py-2 rounded-lg bg-slate-700/30 border border-slate-600/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 transition resize-none"
                        autoFocus
                    />
                </div>

                {/* Buttons */}
                <div className="flex gap-3">
                    <button
                        onClick={handleClose}
                        className="flex-1 px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600 text-white hover:bg-slate-700 transition font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        onClick={handleSubmit}
                        disabled={!ideaText.trim()}
                        className="flex-1 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white transition font-medium"
                    >
                        Add Idea
                    </button>
                </div>
            </div>
        </div>
    );
}
