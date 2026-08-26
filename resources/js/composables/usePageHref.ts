import type { LinkComponentBaseProps } from '@inertiajs/core';

type PageRoute = (options?: {
    query?: Record<string, number | string>;
}) => LinkComponentBaseProps['href'];

export type UsePageHrefReturn = {
    pageHref: (page: number) => LinkComponentBaseProps['href'];
};

export function usePageHref(route: PageRoute): UsePageHrefReturn {
    function pageHref(page: number): LinkComponentBaseProps['href'] {
        if (page <= 1) {
            return route();
        }

        return route({ query: { page } });
    }

    return { pageHref };
}
