import { test, expect } from "../setup";
import { loginAsCustomer } from "../utils/customer";

test("should review a product", async ({ page }) => {
    await loginAsCustomer(page);

    await page.goto("digital-air-fryer-with-touch-controls-45l");
    await page.getByRole("button", { name: "Reviews" }).click();
    await page.locator("#review-tab").getByText("Write a Review").click();
    await page.locator("#review-tab span").nth(3).click();
    await page.locator("#review-tab span").nth(4).click();
    await page.getByPlaceholder("Title").click();
    await page.getByPlaceholder("Title").fill("My Review");
    await page.getByPlaceholder("Comment").click();
    await page.getByPlaceholder("Comment").fill("Great Product");
    await page.getByRole("button", { name: "Submit Review" }).click();

    await expect(
        page.getByText("Review submitted successfully.").first()
    ).toBeVisible();
});
