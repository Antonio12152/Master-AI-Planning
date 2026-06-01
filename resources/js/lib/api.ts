import { axiosInstance } from '@/lib/http';

/**
 * Example: Fetch all plans for the current user
 */
export async function fetchPlans(perPage = 15) {
    try {
        const response = await axiosInstance.get(
            `/plans?per_page=${perPage}`
        );
        return response.data;
    } catch (error) {
        console.error('Failed to fetch plans:', error);
        throw error;
    }
}

/**
 * Example: Create a new plan
 */
export async function createPlan(data: {
    name: string;
    description?: string;
    color?: string;
    icon?: string;
}) {
    try {
        const response = await axiosInstance.post('/plans', data);
        return response.data;
    } catch (error) {
        console.error('Failed to create plan:', error);
        throw error;
    }
}

/**
 * Example: Get plan details
 */
export async function getPlanDetails(planId: number) {
    try {
        const response = await axiosInstance.get(`/plans/${planId}`);
        return response.data;
    } catch (error) {
        console.error('Failed to fetch plan details:', error);
        throw error;
    }
}

/**
 * Example: Update plan
 */
export async function updatePlan(
    planId: number,
    data: {
        name?: string;
        description?: string;
        color?: string;
        icon?: string;
        status?: string;
    }
) {
    try {
        const response = await axiosInstance.put(`/plans/${planId}`, data);
        return response.data;
    } catch (error) {
        console.error('Failed to update plan:', error);
        throw error;
    }
}

/**
 * Example: Fetch ideas for a plan
 */
export async function fetchPlanIdeas(
    planId: number,
    status?: string,
    perPage = 20
) {
    try {
        const params = new URLSearchParams();
        if (status) params.append('status', status);
        params.append('per_page', perPage.toString());

        const response = await axiosInstance.get(
            `/plans/${planId}/ideas?${params}`
        );
        return response.data;
    } catch (error) {
        console.error('Failed to fetch ideas:', error);
        throw error;
    }
}

/**
 * Example: Create a new idea
 */
export async function createIdea(
    groupId: number,
    data: {
        text: string;
        description?: string;
        priority?: number;
        tags?: string[];
    }
) {
    try {
        const response = await axiosInstance.post(
            `/groups/${groupId}/ideas`,
            data
        );
        return response.data;
    } catch (error) {
        console.error('Failed to create idea:', error);
        throw error;
    }
}

/**
 * Example: Update an idea
 */
export async function updateIdea(
    ideaId: number,
    data: {
        text?: string;
        description?: string;
        status?: string;
        priority?: number;
        tags?: string[];
    }
) {
    try {
        const response = await axiosInstance.put(`/ideas/${ideaId}`, data);
        return response.data;
    } catch (error) {
        console.error('Failed to update idea:', error);
        throw error;
    }
}

/**
 * Example: Complete an idea
 */
export async function completeIdea(ideaId: number) {
    try {
        const response = await axiosInstance.post(
            `/ideas/${ideaId}/complete`
        );
        return response.data;
    } catch (error) {
        console.error('Failed to complete idea:', error);
        throw error;
    }
}

/**
 * Example: Delete an idea
 */
export async function deleteIdea(ideaId: number) {
    try {
        await axiosInstance.delete(`/ideas/${ideaId}`);
        return true;
    } catch (error) {
        console.error('Failed to delete idea:', error);
        throw error;
    }
}
