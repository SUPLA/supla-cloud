/**
 * Common TypeScript types for the application
 */

declare global {
    interface User {
        id: number;
        email: string;
        name?: string;
    }

    interface ApiResponse<T> {
        success: boolean;
        data?: T;
        error?: string;
    }

    interface IODevice {
        id: number;
        name: string;
        connected: boolean;
    }
}

// This empty export makes this file a module
export {}
