import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import * as LucideIcons from 'lucide-vue-next';
import { LayoutGrid } from 'lucide-vue-next';

function resolveLucideIcon(name) {
    if (typeof name === 'string' && name.length > 0 && LucideIcons[name]) {
        return LucideIcons[name];
    }
    return LayoutGrid;
}

function hasPermission(ctx, key) {
    const props = ctx.props ?? {};
    const flag = props?.permissions?.[key];
    if (typeof flag === 'boolean') return flag;

    const abilities = props?.auth?.user?.abilities;
    if (abilities && typeof abilities === 'object' && typeof abilities[key] === 'boolean') {
        return abilities[key];
    }

    const permissions = props?.auth?.user?.permissions;
    if (Array.isArray(permissions)) return permissions.includes(key);

    return true;
}

function isAllowed(ctx, item) {
    if (item.permission) {
        const required = Array.isArray(item.permission) ? item.permission : [item.permission];
        if (!required.every((key) => hasPermission(ctx, key))) return false;
    }

    return true;
}

/**
 * @param {object} item — server shape: { title, route?, href?, icon?, permission?, children? }
 */
function mapServerItem(ctx, item) {
    if (!isAllowed(ctx, item)) return null;

    const children =
        item.children
            ?.map((child) => mapServerItem(ctx, child))
            .filter((x) => Boolean(x)) ?? [];

    const hasRoute = Boolean(item.route);
    if (!hasRoute && item.children?.length && children.length === 0) return null;

    const out = {
        title: item.title,
        href: item.href || undefined,
        children: children.length > 0 ? children : undefined,
    };

    if (item.icon) {
        out.icon = resolveLucideIcon(item.icon);
    }

    return out;
}

export function useSidebarNav() {
    const page = usePage();
    const navContext = computed(() => ({ props: page.props }));

    const items = computed(() => {
        const server = page.props.sidebarNav;
        if (!Array.isArray(server) || server.length === 0) {
            return [];
        }

        return server.map((item) => mapServerItem(navContext.value, item)).filter((x) => Boolean(x));
    });

    const homeHref = computed(() => items.value.find((i) => i.href)?.href ?? '/');
    return { items, homeHref };
}
