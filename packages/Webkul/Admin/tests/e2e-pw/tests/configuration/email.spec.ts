import { test, expect } from '../../setup';
import  * as forms from '../../utils/form';

test.describe('email configuration', () => {
    test('Settings of Email', async ({ adminPage }) => {
        await adminPage.goto('admin/configuration/emails/configure');

        await adminPage.click('input[type="text"].rounded-md:visible');

        const inputs = await adminPage.$$('input[type="text"].rounded-md:visible');

        for (let input of inputs) {
            const name = await input.getAttribute('name');

            if (name && (name.includes('port') || name.includes('sort'))) {
                await input.fill('1025');
            } else if (name && name.includes('email')) {
                await input.fill(forms.form.email);
            } else {
                await input.fill(forms.generateRandomStringWithSpaces(50));
            }
        }

        await adminPage.click('button[type="submit"].primary-button:visible');

        await expect(adminPage.getByText('Configuration saved successfully').first()).toBeVisible();
    });

    test('Notifications of Email', async ({ adminPage }) => {
        await adminPage.goto('admin/configuration/emails/general');

        await adminPage.click('button[type="submit"].primary-button:visible');

        await expect(adminPage.getByText('Configuration saved successfully').first()).toBeVisible();
    });
});
