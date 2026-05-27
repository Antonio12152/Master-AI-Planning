import axios, { AxiosInstance } from 'axios';

// Create axios instance with default configuration
export const axiosInstance: AxiosInstance = axios.create({
    baseURL: '/api',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
});

// Add response interceptor to handle errors
axiosInstance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Handle unauthorized - redirect to login
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

// HTTP methods for convenience
export const methods = {
    get: axiosInstance.get,
    post: axiosInstance.post,
    put: axiosInstance.put,
    patch: axiosInstance.patch,
    delete: axiosInstance.delete,
};

export default axiosInstance;
