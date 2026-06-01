import React, { JSX } from 'react';
import { X } from 'lucide-react';
import type { Plan } from '@/types/plans';

interface ChangeStatusModalProps {
    isOpen: boolean;
    plan: Plan | null;
    onClose: () => void;
    onSubmit: (status: string) => void;
}

export default function ChangeStatusModal({
    isOpen,
    plan,
    onClose,
    onSubmit,
}: ChangeStatusModalProps): JSX.Element | null {
    if (!isOpen || !plan) return null;

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'active':
                return '🟢';
            case 'inactive':
                return '⚫';
            case 'archived':
                return '📦';
            default:
                return '•';
        }
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active':
                return plan.status === status
                    ? 'bg-green-600/30 border-green-500 text-green-400'
                    : 'border-slate-600/50 text-slate-300 hover:border-slate-500 hover:text-white';
            case 'inactive':
                return plan.status === status
                    ? 'bg-slate-600/30 border-slate-500 text-slate-300'
                    : 'border-slate-600/50 text-slate-300 hover:border-slate-500 hover:text-white';
            case 'archived':
                return plan.status === status
                    ? 'bg-orange-600/30 border-orange-500 text-orange-400'
                    : 'border-slate-600/50 text-slate-300 hover:border-slate-500 hover:text-white';
            default:
                return 'border-slate-600/50 text-slate-300';
        }
    };

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-slate-800 rounded-xl border border-slate-700 p-8 w-full max-w-md">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-2xl font-semibold">Change Status</h2>
                    <button
                        onClick={onClose}
                        className="text-slate-400 hover:text-white transition"
                    >
                        <X size={24} />
                    </button>
                </div>

                <p className="text-slate-400 mb-6">
                    Change status for "<span className="text-white font-semibold">{plan.name}</span>"
                </p>

                <div className="space-y-2 mb-6">
                    {(['active', 'inactive', 'archived'] as const).map((status) => (
                        <button
                            key={status}
                            onClick={() => {
                                onSubmit(status);
                            }}
                            className={`w-full p-3 rounded-lg border text-left font-medium transition ${getStatusColor(
                                status
                            )}`}
                        >
                            <span className="mr-2">{getStatusIcon(status)}</span>
                            {status.charAt(0).toUpperCase() + status.slice(1)}
                        </button>
                    ))}
                </div>

                {/* Button */}
                <button
                    onClick={onClose}
                    className="w-full px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600 text-white hover:bg-slate-700 transition font-medium"
                >
                    Cancel
                </button>
            </div>
        </div>
    );
}