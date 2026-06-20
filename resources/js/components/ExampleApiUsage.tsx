import React, { useEffect, useState } from 'react';
import { useForm } from '@inertiajs/react';
import {
    fetchPlans,
    createPlan,
    fetchPlanIdeas,
    updateIdea,
    completeIdea,
    deleteIdea,
} from '@/lib/api';

interface Plan {
    id: number;
    name: string;
    description?: string;
    idea_count: number;
}

interface Idea {
    id: number;
    text: string;
    description?: string;
    status: string;
    priority: number;
    tags?: string[];
}

/**
 * Example Component: Plans List
 * Demonstrates how to fetch and create plans
 */
export function PlansList() {
    const [plans, setPlans] = useState<Plan[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    // Example form using Inertia's useForm
    const form = useForm({
        name: '',
        description: '',
        color: '#3b82f6',
    });

    // Fetch plans on component mount
    useEffect(() => {
        loadPlans();
    }, []);

    async function loadPlans() {
        try {
            setLoading(true);
            const data = await fetchPlans();
            setPlans(data);
            setError(null);
        } catch (err) {
            setError('Failed to load plans');
            console.error(err);
        } finally {
            setLoading(false);
        }
    }

    async function handleCreatePlan() {
        try {
            const newPlan = await createPlan(form.data);
            setPlans([...plans, newPlan]);
            form.reset();
        } catch (err) {
            setError('Failed to create plan');
        }
    }

    if (loading) return <div>Loading...</div>;

    return (
        <div className="space-y-6">
            {error && (
                <div className="rounded-lg bg-red-50 p-4 text-red-800">
                    {error}
                </div>
            )}

            {/* Create Plan Form */}
            <div className="rounded-lg border border-gray-200 p-6">
                <h2 className="mb-4 text-lg font-semibold">Create New Plan</h2>
                <div className="space-y-4">
                    <input
                        type="text"
                        placeholder="Plan name"
                        className="w-full rounded-lg border border-gray-300 px-4 py-2"
                        value={form.data.name}
                        onChange={(e) =>
                            form.setData('name', e.target.value)
                        }
                    />
                    <textarea
                        placeholder="Description"
                        className="w-full rounded-lg border border-gray-300 px-4 py-2"
                        value={form.data.description}
                        onChange={(e) =>
                            form.setData('description', e.target.value)
                        }
                    />
                    <button
                        onClick={handleCreatePlan}
                        disabled={form.processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {form.processing ? 'Creating...' : 'Create Plan'}
                    </button>
                </div>
            </div>

            {/* Plans List */}
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-white">Your Plans</h2>
                {plans.length === 0 ? (
                    <p className="text-gray-600">No plans yet</p>
                ) : (
                    <div className="grid gap-4 grid-cols-1 md:grid-cols-2">
                        {plans.map((plan) => (
                            <div
                                key={plan.id}
                                className="rounded-lg border border-gray-200 p-4 hover:shadow-lg transition"
                            >
                                <h3 className="font-semibold">{plan.name}</h3>
                                <p className="text-sm text-gray-600">
                                    {plan.description}
                                </p>
                                <p className="mt-2 text-sm text-gray-500">
                                    {plan.idea_count} ideas
                                </p>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}

/**
 * Example Component: Ideas List for a Plan
 */
export function IdeasList({ planId }: { planId: number }) {
    const [ideas, setIdeas] = useState<Idea[]>([]);
    const [loading, setLoading] = useState(true);

    const form = useForm({
        text: '',
        description: '',
        priority: 0,
    });

    useEffect(() => {
        loadIdeas();
    }, [planId]);

    async function loadIdeas() {
        try {
            setLoading(true);
            const data = await fetchPlanIdeas(planId);
            setIdeas(data.ideas?.data || []);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    }

    async function handleCompleteIdea(ideaId: number) {
        try {
            await completeIdea(ideaId);
            setIdeas((prev) =>
                prev.map((idea) =>
                    idea.id === ideaId
                        ? { ...idea, status: 'completed' }
                        : idea
                )
            );
        } catch (err) {
            console.error(err);
        }
    }

    async function handleDeleteIdea(ideaId: number) {
        try {
            await deleteIdea(ideaId);
            setIdeas((prev) =>
                prev.filter((idea) => idea.id !== ideaId)
            );
        } catch (err) {
            console.error(err);
        }
    }

    if (loading) return <div>Loading ideas...</div>;

    return (
        <div className="space-y-4">
            <h3 className="font-semibold">Ideas</h3>
            {ideas.length === 0 ? (
                <p className="text-gray-600">No ideas yet</p>
            ) : (
                <div className="space-y-2">
                    {ideas.map((idea) => (
                        <div
                            key={idea.id}
                            className="flex items-center justify-between rounded-lg border border-gray-200 p-3"
                        >
                            <div className="flex-1">
                                <p className="font-medium">{idea.text}</p>
                                <p className="text-sm text-gray-600">
                                    {idea.description}
                                </p>
                                <div className="mt-1 flex items-center gap-2 text-xs text-gray-500">
                                    <span
                                        className={`px-2 py-1 rounded ${
                                            idea.status === 'completed'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-blue-100 text-blue-800'
                                        }`}
                                    >
                                        {idea.status}
                                    </span>
                                    <span>Priority: {idea.priority}</span>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                {idea.status !== 'completed' && (
                                    <button
                                        onClick={() =>
                                            handleCompleteIdea(idea.id)
                                        }
                                        className="rounded bg-green-600 px-3 py-1 text-sm text-white hover:bg-green-700"
                                    >
                                        Complete
                                    </button>
                                )}
                                <button
                                    onClick={() => handleDeleteIdea(idea.id)}
                                    className="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
