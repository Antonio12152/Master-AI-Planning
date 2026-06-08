import axios, { AxiosInstance } from 'axios';

// Get CSRF token from meta tag or cookie
function getCsrfToken(): string {
    // Try to get from meta tag first
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content') || '';
    }

    // Fallback: try to get from cookies
    const name = 'XSRF-TOKEN=';
    const decodedCookie = decodeURIComponent(document.cookie);
    const cookieArray = decodedCookie.split(';');

    for (let cookie of cookieArray) {
        cookie = cookie.trim();
        if (cookie.indexOf(name) === 0) {
            return cookie.substring(name.length, cookie.length);
        }
    }

    return '';
}

// Create axios instance with default configuration
export const axiosInstance: AxiosInstance = axios.create({
    baseURL: '/api',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
    withCredentials: true, // Important for cookies/session
});

// Add request interceptor to add CSRF token
axiosInstance.interceptors.request.use((config) => {
    const token = getCsrfToken();
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
    }
    return config;
});

// Add response interceptor to handle errors
axiosInstance.interceptors.response.use(
    (response) => response,
    (error) => {
        console.error('API Error:', error.response?.status, error.response?.data);

        if (error.response?.status === 401) {
            // Handle unauthorized - redirect to login
            window.location.href = '/login';
        }

        if (error.response?.status === 403) {
            console.error('Forbidden:', error.response.data);
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

// import axios, { AxiosInstance } from 'axios';

// // Create axios instance with default configuration
// export const axiosInstance: AxiosInstance = axios.create({
//     baseURL: '/api',
//     headers: {
//         'X-Requested-With': 'XMLHttpRequest',
//         'Content-Type': 'application/json',
//         Accept: 'application/json',
//     },
// });

// // Add response interceptor to handle errors
// axiosInstance.interceptors.response.use(
//     (response) => response,
//     (error) => {
//         if (error.response?.status === 401) {
//             // Handle unauthorized - redirect to login
//             window.location.href = '/login';
//         }
//         return Promise.reject(error);
//     }
// );

// // HTTP methods for convenience
// export const methods = {
//     get: axiosInstance.get,
//     post: axiosInstance.post,
//     put: axiosInstance.put,
//     patch: axiosInstance.patch,
//     delete: axiosInstance.delete,
// };

// export default axiosInstance;
