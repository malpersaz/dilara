import { test, expect } from "../setup";

test("Increment", async ({ page }) => {
    await page.goto("digital-air-fryer-with-touch-controls-45l");
    await page.getByRole("button", { name: "Add To Cart" }).first().click();
    await expect(
        page.getByText("Item Added Successfully").first()
    ).toBeVisible();

    await page.getByRole("button", { name: "Shopping Cart" }).click();
    await page.getByRole("banner").getByRole("button", { name: "Increase Quantity" }).first().click();
    await page.getByRole("banner").getByRole("button", { name: "Increase Quantity" }).first().click();

    await expect(
        page.locator("svg.text-blue.animate-spin.font-semibold")
    ).toBeHidden();
});

test("Decrement", async ({ page }) => {
    await page.goto("digital-air-fryer-with-touch-controls-45l");
    await page.getByRole("button", { name: "Add To Cart" }).first().click();
    await expect(
        page.getByText("Item Added Successfully").first()
    ).toBeVisible();

    await page.getByRole("button", { name: "Shopping Cart" }).click();
    await page.getByRole("banner").getByRole("button", { name: "Increase Quantity" }).first().click();
    await page.getByRole("banner").getByRole("button", { name: "Increase Quantity" }).first().click();
    await page.getByRole("banner").getByRole("button", { name: "Decrease Quantity" }).first().click();
    await page.getByRole("banner").getByRole("button", { name: "Decrease Quantity" }).first().click();

    await expect(
        page.locator("svg.text-blue.animate-spin.font-semibold")
    ).toBeHidden();
});

test("Remove", async ({ page }) => {
    await page.goto("digital-air-fryer-with-touch-controls-45l");
    await page.getByRole("button", { name: "Add To Cart" }).first().click();
    await expect(
        page.getByText("Item Added Successfully").first()
    ).toBeVisible();

    await page.getByRole("button", { name: "Shopping Cart" }).click();
    await page.getByRole("button", { name: "Remove" }).click();
    await page.getByRole("button", { name: "Agree", exact: true }).click();

    await expect(
        page.getByText("Item is successfully removed from the cart.").first()
    ).toBeVisible();
});
