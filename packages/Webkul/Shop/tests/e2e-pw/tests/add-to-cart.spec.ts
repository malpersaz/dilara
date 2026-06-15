import { test, expect } from "../setup";

test("should add product to cart", async ({ page }) => {
    await page.goto("digital-air-fryer-with-touch-controls-45l");

    await page.getByRole("button", { name: "Add To Cart" }).first().click();

    await expect(
        page.getByText("Item Added Successfully").first()
    ).toBeVisible();
});
