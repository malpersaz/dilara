import {
    test as base,
    expect,
    type BrowserContext,
    type Page,
} from "@playwright/test";
import fs from "fs";
import { loginAsAdmin } from "./utils/admin";
import { ADMIN_AUTH_STATE_PATH, ensureStateDir } from "./utils/paths";
import { fillTinymce } from "./utils/TinymcePage";

export interface AdminPage extends Page {
    fillInTinymce: (iframeSelector: string, content: string) => Promise<void>;
}

export interface ShopPage extends Page {
    fillInTinymce: (iframeSelector: string, content: string) => Promise<void>;
}

type Fixtures = {
    adminPage: AdminPage;
    shopPage: ShopPage;
};

export function withTinymce(page: Page): AdminPage {
    (page as AdminPage).fillInTinymce = (
        iframeSelector: string,
        content: string,
    ) => fillTinymce(page, iframeSelector, content);

    return page as AdminPage;
}

async function saveAdminAuth(context: BrowserContext): Promise<void> {
    ensureStateDir();

    await context.storageState({ path: ADMIN_AUTH_STATE_PATH });
}

export const test = base.extend<Fixtures>({
    adminPage: async ({ browser, baseURL }, use) => {
        const authExists = fs.existsSync(ADMIN_AUTH_STATE_PATH);

        const context = await browser.newContext({
            ...(authExists ? { storageState: ADMIN_AUTH_STATE_PATH } : {}),
            baseURL,
        });

        const page = await context.newPage();

        if (!authExists) {
            await loginAsAdmin(page);
            await saveAdminAuth(context);
        } else {
            await page.goto("admin/dashboard");
        }

        if (page.url().includes("admin/login")) {
            await loginAsAdmin(page);
            await saveAdminAuth(context);
        }

        await use(withTinymce(page));
        await context.close();
    },

    shopPage: async ({ browser, baseURL }, use) => {
        const context = await browser.newContext({ baseURL });
        const url = new URL(baseURL || "http://localhost");
        await context.addCookies([
            {
                name: "cookie-consent",
                value: "1",
                domain: url.hostname,
                path: "/",
            },
        ]);
        const page = await context.newPage();

        await use(withTinymce(page) as ShopPage);
        await context.close();
    },
});

export { expect };
