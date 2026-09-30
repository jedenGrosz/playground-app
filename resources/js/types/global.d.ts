import type { Auth } from '@/types';

declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps {
        name: string;
        quote: { message: string; author: string };
        auth: Auth;
        flash: {
            success?: string;
            error?: string;
        };
    }
}
