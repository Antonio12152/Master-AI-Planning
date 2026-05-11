import React, { JSX, useState } from 'react';
import { X } from 'lucide-react';

interface CreatePlanModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSubmit: (data: { name: string; description: string }) => void;
}

export default function CreatePlanModal({ isOpen, onClose, onSubmit }: CreatePlanModalProps): JSX.Element | null {
    const [formData, setFormData] = useState({ name: '', description: '' });

    const handleSubmit = () => {
        if (formData.name.trim()) {
            onSubmit(formData);
            setFormData({ name: '', description: '' });
        }
    };

    const handleClose = () => {
        setFormData({ name: '', description: '' });
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-slate-800 rounded-xl border border-slate-700 p-8 w-full max-w-md">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-2xl font-semibold">Create New Plan</h2>
                    <button
                        onClick={handleClose}
                        className="text-slate-400 hover:text-white transition"
                    >
                        <X size={24} />
                    </button>
                </div>

                <div className="space-y-4 mb-6">
                    {/* Plan Name */}
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">
                            Plan Name
                        </label>
                        <input
                            type="text"
                            placeholder="Enter plan name..."
                            value={formData.name}
                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                            className="w-full px-4 py-2 rounded-lg bg-slate-700/30 border border-slate-600/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 transition"
                            autoFocus
                        />
                    </div>

                    {/* Plan Description */}
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">
                            Description
                        </label>
                        <textarea
                            placeholder="Enter plan description..."
                            value={formData.description}
                            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                            rows={4}
                            className="w-full px-4 py-2 rounded-lg bg-slate-700/30 border border-slate-600/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 transition resize-none"
                        />
                    </div>
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
                        disabled={!formData.name.trim()}
                        className="flex-1 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white transition font-medium"
                    >
                        Create Plan
                    </button>
                </div>
            </div>
        </div>
    );
}