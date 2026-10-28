import { expect, Page } from "@playwright/test";
import { BasePage } from "../../../BasePage";
import { uniqueStamp } from "../../../../utils/faker";
import { ProductEditPage } from "./ProductEditPage";
import { BaseProduct } from "../../types/product.types";

export class ProductCreatePage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    private get createButton() {
        return this.page.getByRole("button", { name: "Create Product" });
    }

    private get typeSelect() {
        return this.page.locator('select[name="type"]');
    }

    private get attributeFamilySelect() {
        return this.page.locator('select[name="attribute_family_id"]');
    }

    private get skuInput() {
        return this.page.locator('input[name="sku"]');
    }

    private get saveProductButton() {
        return this.page.getByRole("button", { name: "Save Product" });
    }

    private get validationErrors() {
        return this.page.locator("p.text-red-600");
    }

    private async openCreateModal(): Promise<void> {
        await this.visit("admin/catalog/products");

        await expect(this.createButton).toBeVisible();

        await this.createButton.click();

        await expect(this.typeSelect).toBeVisible();
    }

    async fillType(type: string) {
        await this.typeSelect.selectOption(type);
    }

    async fillAttributeFamily(attributeFamily: string | { label: string }) {
        await this.attributeFamilySelect.waitFor({ state: "visible" });

        let target: any = attributeFamily;

        if (typeof attributeFamily === "object" && "label" in attributeFamily && attributeFamily.label === "Clothing") {
            const hasValue3 = await this.attributeFamilySelect.locator('option[value="3"]').count() > 0;
            if (hasValue3) {
                target = { value: "3" };
            } else {
                target = { index: 0 };
            }
        } else if (typeof attributeFamily === "string") {
            const hasValue = await this.attributeFamilySelect.locator(`option[value="${attributeFamily}"]`).count() > 0;
            if (!hasValue) {
                target = { index: 0 };
            }
        }

        await this.attributeFamilySelect.selectOption(target);
    }

    async fillSku(sku: string) {
        await this.skuInput.fill(sku);
    }

    async submit() {
        await this.saveProductButton.click();
    }

    private async submitCreateModal(
        type: string,
        attributeFamily: string | { label: string },
        sku: string,
    ): Promise<void> {
        await this.openCreateModal();
        await this.fillType(type);
        await this.fillAttributeFamily(attributeFamily);
        await this.fillSku(sku);
        await this.submit();
    }

    async createProduct(
        type: string,
        attributeFamily: string | { label: string },
        sku: string,
    ): Promise<void> {
        await this.submitCreateModal(type, attributeFamily, sku);
    }

    async attemptCreateProduct(sku: string, type: string = "simple"): Promise<void> {
        await this.submitCreateModal(type, "1", sku);
    }

    async createSimpleProduct(
        product: BaseProduct,
        attributeFamily: string | { label: string } = "1",
    ): Promise<string> {
        const sku = product.sku ?? `SKU-${uniqueStamp()}`;

        await this.submitCreateModal("simple", attributeFamily, sku);

        const productEditPage = new ProductEditPage(this.page);
        await productEditPage.waitForForm();

        await productEditPage.fillGeneralDetails({
            productNumber: product.productNumber,
            name: product.name,
        });

        await productEditPage.fillDescriptions(
            product.shortDescription,
            product.description,
        );

        await productEditPage.fillMeta({
            metaTitle: product.name,
            metaKeywords: product.name,
            metaDescription: product.shortDescription,
        });

        await productEditPage.fillPrice(product.price ?? "");
        await productEditPage.fillWeight(product.weight ?? "");
        await productEditPage.fillInventory(product.inventory ?? "");

        if (product.allowRma) {
            await productEditPage.setAllowRma(true);
        }

        await productEditPage.saveAndVerifyUpdated();

        return sku;
    }

    async expectCreateRefused(message: string): Promise<void> {
        await expect(
            this.validationErrors.filter({ hasText: message }).first(),
        ).toBeVisible();
<<<<<<< HEAD
    }

    private async createBookingProductBase(): Promise<BookingProductSeed> {
        const product: BookingProductSeed = {
            name: generateName(),
            sku: generateSKU(),
            productNumber: generateSKU(),
            shortDescription: generateDescription(),
            description: generateDescription(),
            price: "199",
            weight: "25",
            date: generateRandomDateTime(),
            location: generateLocation(),
        };
        const availableFromDate = new Date();
        availableFromDate.setDate(availableFromDate.getDate() + 1);

        const availableToDate = new Date(
            availableFromDate.getTime() + 24 * 60 * 60000,
        );

        const formattedAvailableFromDate = availableFromDate
            .toISOString()
            .slice(0, 19)
            .replace("T", " ");

        const formattedAvailableToDate = availableToDate
            .toISOString()
            .slice(0, 19)
            .replace("T", " ");

        await this.startProductCreation("booking", "1");

        const productEditPage = new ProductEditPage(this.page);
        await productEditPage.waitForForm();
        await productEditPage.fillGeneralDetails({
            productNumber: product.productNumber,
            name: product.name,
        });
        await productEditPage.fillDescriptions(
            product.shortDescription,
            product.description,
        );
        await productEditPage.fillMeta({
            metaTitle: product.name,
            metaKeywords: product.name,
            metaDescription: product.shortDescription,
        });
        await productEditPage.fillPrice(product.price);

        await this.bookingInput("location").fill(product.location);
        await this.bookingInput("available_from").fill(
            formattedAvailableFromDate,
        );
        await this.bookingInput("available_to").fill(formattedAvailableToDate);

        return product;
    }

    async createSimpleProduct(product) {
        await this.startProductCreation("simple", "1");

        const productEditPage = new ProductEditPage(this.page);
        await productEditPage.waitForForm();

        await productEditPage.fillGeneralDetails({
            productNumber: product.productNumber,
            name: product.name,
        });

        await productEditPage.fillDescriptions(
            product.shortDescription,
            product.description,
        );

        await productEditPage.fillMeta({
            metaTitle: product.name,
            metaKeywords: product.name,
            metaDescription: product.shortDescription,
        });

        await productEditPage.fillPrice(product.price);
        await productEditPage.fillWeight(product.weight);
        await productEditPage.fillInventory(product.inventory);
        await productEditPage.saveAndVerifyUpdated();
    }

    async createConfigurableProduct(product) {
        await this.startProductCreation("configurable", { label: "Clothing" });
        await expect(this.configurableAttributesHeading).toBeVisible();

        for (const color of [
            "Red",
            "Black",
            "White",
            "Orange",
            "Blue",
            "Pink",
            "Purple",
            "Green",
            "Yellow",
            "Dual Tone",
            "Grey",
        ]) {
            await this.configurableAttributeOption(color).click();
        }

        for (let i = 0; i < 6; i++) {
            await this.configurableAttributeRemoveButton.click();
        }

        await this.saveProductButton.click();
        await expect(this.productCreatedSuccessToast).toBeVisible();

        const productEditPage = new ProductEditPage(this.page);
        await productEditPage.fillGeneralDetails({
            productNumber: product.productNumber,
            name: product.name,
        });
        await productEditPage.fillDescriptions(
            product.shortDescription,
            product.description,
        );
        await productEditPage.fillMeta({
            metaTitle: product.name,
            metaKeywords: product.name,
            metaDescription: product.shortDescription,
        });

        await this.firstUncheckedCheckbox.click();
        await this.selectActionButton.click();
        await this.editPricesOption.click();
        await this.agreeButton.click();
        await this.paragraphByText("Edit Prices").click();
        await this.bulkPriceInput.fill("100");
        await this.applyToAllButton.click();
        await this.modalSaveButton.click();

        await this.firstUncheckedCheckbox.click();
        await this.selectActionButton.click();
        await this.editInventoriesOption.click();
        await this.agreeButton.click();
        await this.bulkInventoryInput.fill("010");
        await this.applyToAllButton.click();
        await this.modalSaveButton.click();

        await this.firstUncheckedCheckbox.click();
        await this.selectActionButton.click();
        await this.editWeightOption.click();
        await this.agreeButton.click();
        await this.bulkWeightInput.fill("01");
        await this.applyToAllButton.click();
        await this.modalSaveButton.click();

        await productEditPage.saveAndVerifyUpdated();
    }

    async createGroupedProduct(product) {
        const simpleProduct = {
            name: `simple-${generateName()}`,
            sku: generateSKU(),
            productNumber: generateSKU(),
            shortDescription: generateDescription(),
            description: generateDescription(),
            price: "199",
            weight: "25",
            inventory: "5000",
        };

        await this.createSimpleProduct(simpleProduct);

        await this.startProductCreation("grouped", "1");
        const productEditPage = new ProductEditPage(this.page);
        await productEditPage.waitForForm();
        await productEditPage.fillGeneralDetails({
            productNumber: product.productNumber,
            name: product.name,
        });
        await productEditPage.fillDescriptions(
            product.shortDescription,
            product.description,
        );
        await productEditPage.fillMeta({
            metaTitle: product.name,
            metaKeywords: product.name,
            metaDescription: product.shortDescription,
        });

        await this.productSelectionButton.click();
        await expect(this.selectProductsModalTitle).toBeVisible();
        await this.searchByNameInput.fill("simple");
        await this.groupedProductCheckbox("simple-").check({ force: true });
        await this.addSelectedProductButton.click();
        await expect(this.paragraphByText("simple").nth(0)).toBeVisible();
        await productEditPage.saveAndVerifyUpdated();
    }

    async createVirtualProduct(product) {
        await this.startProductCreation("virtual", "1");
        const productEditPage = new ProductEditPage(this.page);
        await productEditPage.waitForForm();
        await productEditPage.fillGeneralDetails({
            productNumber: product.productNumber,
            name: product.name,
        });
        await productEditPage.fillDescriptions(
            product.shortDescription,
            product.description,
        );
        await productEditPage.fillMeta({
            metaTitle: product.name,
            metaKeywords: product.name,
            metaDescription: product.shortDescription,
        });
        await productEditPage.fillPrice(product.price);
        await productEditPage.fillInventory("5000");
        await productEditPage.saveAndVerifyUpdated();
        await this.verifyProductVisible(product.name);
    }

    async createDownloadableProduct(product) {
        await this.startProductCreation("downloadable", "1");
        const productEditPage = new ProductEditPage(this.page);
        await productEditPage.waitForForm();
        await productEditPage.fillGeneralDetails({
            productNumber: product.productNumber,
            name: product.name,
        });
        await productEditPage.fillDescriptions(
            product.shortDescription,
            product.description,
        );
        await productEditPage.fillMeta({
            metaTitle: product.name,
            metaKeywords: product.name,
            metaDescription: product.shortDescription,
        });
        await productEditPage.fillPrice(product.price);

        await this.addLinkButton.click();
        await expect(this.downloadableLinkPanel.nth(0)).toBeVisible();
        await this.linkTitleInput.fill(generateName());
        const linkTitle = await this.linkTitleInput.inputValue();
        await this.linkPriceInput.fill("100");
        await this.linkDownloadsInput.fill("2");
        await this.linkTypeSelect.selectOption("file");
        await this.linkFileInput.setInputFiles(
            path.resolve(__dirname, "../../../../data/images/1.webp"),
        );
        await this.page.waitForTimeout(1000);
        await this.modalSaveButton.click();
        await expect(this.linkTitleText(linkTitle)).toBeVisible();
        await productEditPage.saveAndVerifyUpdated();
    }

    async createDefaultBookingProductWithOneBookingForManyDays() {
        const product = await this.createBookingProductBase();
        await this.bookingInput("qty").fill(product.weight);

        for (let slot = 1; slot <= 2; slot++) {
            await this.addSlotsButton.click();
            await this.fromDaySelect.selectOption((slot - 1).toString());
            await this.fromTimeTextbox.click();
            await this.page.waitForTimeout(500);
            await this.minuteSpinbutton.click();
            await this.page.waitForTimeout(500);
            await this.toDaySelect.selectOption(slot.toString());
            await this.toTimeTextbox.click();
            await this.minuteSpinbutton.click();
            await this.page.waitForTimeout(500);
            await this.escapeTarget.press("Escape");
            await this.modalSaveButton.click();
            await expect(this.bookingSlotIdInput(slot - 1)).toHaveValue(/.+/);
        }

        await this.saveProductButton.click();
        return product.name
    }

    async createDefaultBookingProductWithManyBookingForOneDay() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("booking_type").selectOption("many");
        await this.bookingInput("qty").fill(product.weight);

        const weeks = [
            { name: "Sunday", status: 1 },
            { name: "Monday", status: 2 },
            { name: "Tuesday", status: 3 },
        ];

        for (const day of weeks) {
            await this.dayAvailabilityTrigger(day.status).click();
            await this.slotTimeTextbox("From", 0).click();
            await this.flatpickrCalendar.waitFor({ state: "visible" });
            await this.hourSpinbutton.fill("10");
            await this.minuteSpinbutton.fill("35");
            await this.minuteSpinbutton.press("Enter");
            await this.page.waitForTimeout(500);

            await this.slotTimeTextbox("To", 0).click();
            await this.flatpickrCalendar.waitFor({ state: "visible" });
            await this.hourSpinbutton.fill("20");
            await this.minuteSpinbutton.fill("35");
            await this.minuteSpinbutton.press("Enter");

            await this.page.waitForTimeout(500);
            await this.dayStatusSelect.selectOption(
                day.name === "Sunday" ? "0" : "1",
            );
            await this.page.waitForTimeout(500);
            await this.escapeTarget.press("Escape");
            await this.modalSaveButton.click();
            await expect(
                this.bookingDaySlotIdInput(day.status - 1),
            ).toHaveValue(/.+/);
        }

        await this.saveProductButton.click();
        return product.name
    }

    async handleDefaultBookingWithShorterTimeRangeThanSlots() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("booking_type").selectOption("many");
        await this.bookingInput("qty").fill(product.weight);

        const weeks = [
            { name: "Sunday", status: 1 },
        ];

        for (const day of weeks) {
            await this.dayAvailabilityTrigger(day.status).click();
            await this.slotTimeTextbox("From", 0).click();
            await this.flatpickrCalendar.waitFor({ state: "visible" });
            await this.hourSpinbutton.fill("10");
            await this.minuteSpinbutton.fill("35");
            await this.minuteSpinbutton.press("Enter");
            await this.page.waitForTimeout(500);

            await this.slotTimeTextbox("To", 0).click();
            await this.page.waitForTimeout(500);
            await this.flatpickrCalendar.waitFor({ state: "visible" });
            await this.hourSpinbutton.fill("10");
            await this.minuteSpinbutton.fill("50");
            await this.minuteSpinbutton.press("Enter");

            await this.page.waitForTimeout(500);
            await this.escapeTarget.press("Escape");
            await this.dayStatusSelect.selectOption("1");
            await this.modalSaveButton.click();
        }
    }

    async createAppointmentBookingProductNotAvailableEveryWeekWithSameSlotForAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("appointment");
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("0");
        await this.bookingSelect("same_slot_all_days").selectOption("1");
        await this.addSlotsButton.click();
        await this.fillTimeTextbox("From", 0, "10", "35");
        await this.page.waitForTimeout(500);
        await this.fillTimeTextbox("To", 0, "11", "30");
        await this.escapeTarget.press("Escape");
        await this.modalSaveButton.click();
        await expect(this.timeRangeText("10:35 - 11:30")).toBeVisible();
        await this.saveProductButton.click();
        return product.name;
    }

    async createAppointmentBookingProductNotAvailableEveryWeekWithNoSameSlotForAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("appointment");
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("0");
        await this.bookingSelect("same_slot_all_days").selectOption("0");

        const weeks = [
            { status: 0, slots: 1, fromHr: "10", toHr: "19" },
            { status: 1, slots: 2, fromHr: "07", toHr: "12" },
            { status: 2, slots: 1, fromHr: "09", toHr: "20" },
        ];

        for (const day of weeks) {
            await this.dayAvailabilityTrigger(day.status + 1).click();

            for (let slot = 0; slot < day.slots; slot++) {
                await this.slotEditorTrigger(day.status, slot).focus();
                await this.fillTimeTextbox("From", slot, day.fromHr, "00");
                await this.fillTimeTextbox("To", slot, day.toHr, "55");

                if (slot < day.slots - 1) {
                    await this.addSlotsButton.click();
                }
            }

            await this.escapeTarget.press("Escape");
            await this.modalSaveButton.click();
        }

        await expect(this.timeRangeText("10:00 - 19:55")).toBeVisible();
        await expect(this.timeRangeText("09:00 - 20:55")).toBeVisible();
        await this.saveProductButton.click();
        return product.name;
    }

    async createAppointmentBookingProductAvailableEveryWeekWithNoSameSlotForAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("appointment");
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("1");
        await this.bookingSelect("same_slot_all_days").selectOption("0");

        const weeks = [
            { status: 0, slots: 1, fromHr: "10", toHr: "19" },
            { status: 1, slots: 2, fromHr: "07", toHr: "12" },
            { status: 2, slots: 1, fromHr: "09", toHr: "20" },
        ];

        for (const day of weeks) {
            await this.dayAvailabilityTrigger(day.status + 1).click();

            for (let slot = 0; slot < day.slots; slot++) {
                await this.slotEditorTrigger(day.status, slot).focus();
                await this.fillTimeTextbox("From", slot, day.fromHr, "00");
                await this.fillTimeTextbox("To", slot, day.toHr, "55");
                await this.addSlotsButton.click();
            }

            await this.escapeTarget.press("Escape");
            await this.modalSaveButton.click();
        }

        await expect(this.timeRangeText("10:00 - 19:55")).toBeVisible();
        await expect(this.timeRangeText("09:00 - 20:55")).toBeVisible();
        await this.saveProductButton.click();
        return product.name;
    }

    async createAppointmentBookingProductAvailableEveryWeekWithSameSlotForAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("appointment");
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("1");
        await this.bookingSelect("same_slot_all_days").selectOption("1");
        await this.addSlotsButton.click();
        await this.fillTimeTextbox("From", 0, "10", "35");
        await this.fillTimeTextbox("To", 0, "11", "30");
        await this.escapeTarget.press("Escape");
        await this.modalSaveButton.click();
        await expect(this.timeRangeText(":35 - 11:30").first()).toBeVisible();
        await this.saveProductButton.click();
        return product.name;

    }

    async handleAppointmentBookingWithShorterTimeRangeThanSlots(isAvailableEveryWeek: boolean, isSameSlotAllDays: boolean) {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("appointment");
        await this.bookingInput("qty").fill(product.weight);

        if (!isAvailableEveryWeek) {
            await this.bookingSelect("available_every_week").selectOption("0");
            await this.fillAvailabilityRange(
                "2027-04-08 16:00:00",
                "2027-04-25 18:00",
            );

            if (isSameSlotAllDays) {
                await this.bookingSelect("same_slot_all_days").selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "10", "35");
                await this.fillTimeTextbox("To", 0, "11", "00");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();
            } else {
                await this.bookingSelect("same_slot_all_days").selectOption("0");
                const weeks = [
                    { status: 0, slots: 1, fromHr: "10", toHr: "10" },
                ];

                for (const day of weeks) {
                    await this.dayAvailabilityTrigger(day.status + 1).click();

                    for (let slot = 0; slot < day.slots; slot++) {
                        await this.slotEditorTrigger(day.status, slot).focus();
                        await this.fillTimeTextbox("From", slot, day.fromHr, "00");
                        await this.fillTimeTextbox("To", slot, day.toHr, "25");
                    }

                    await this.escapeTarget.press("Escape");
                    await this.modalSaveButton.click();

                }
            }
        } else {
            await this.bookingSelect("available_every_week").selectOption("1");

            if (isSameSlotAllDays) {
                await this.bookingSelect("same_slot_all_days").selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "10", "35");
                await this.page.waitForTimeout(500);
                await this.fillTimeTextbox("To", 0, "11", "00");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();

            } else {
                await this.bookingSelect("same_slot_all_days").selectOption("0");
                const weeks = [
                    { status: 0, slots: 1, fromHr: "10", toHr: "10" },
                ];

                for (const day of weeks) {
                    await this.dayAvailabilityTrigger(day.status + 1).click();

                    for (let slot = 0; slot < day.slots; slot++) {
                        await this.slotEditorTrigger(day.status, slot).focus();
                        await this.fillTimeTextbox("From", slot, day.fromHr, "00");
                        await this.fillTimeTextbox("To", slot, day.toHr, "25");
                    }

                    await this.escapeTarget.press("Escape");
                    await this.modalSaveButton.click();

                }
            }
        }
    }
    async createEventBookingProduct() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("event");
        await this.bookingInput("location").fill(product.location);
        await this.addTicketsButton.click();
        await this.ticketNameInput.fill(generateName());
        await this.ticketQuantityInput.fill("2");
        await this.ticketPriceInput.fill("500");
        await this.ticketDescriptionInput.fill(generateDescription());
        await this.modalSaveButton.click();
        await this.saveProductButton.click();
        return product.name;
    }

    async createRentalBookingProductDailyBasisNotAvailableEveryWeek() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("rental");
        await this.bookingInput("location").fill(product.location);
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("0");
        await this.fillAvailabilityRange(
            "2027-04-08 16:00:00",
            "2027-04-25 18:00",
        );
        await this.bookingSelect("renting_type").selectOption("daily");
        await this.dailyPriceTextbox.fill("3000");
        await this.escapeTarget.press("Escape");
        await this.saveProductButton.click();
        return product.name;
    }

    async createRentalBookingProductDailyBasisAvailableEveryWeek() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("rental");
        await this.bookingInput("location").fill(product.location);
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("1");
        await this.bookingSelect("renting_type").selectOption("daily");
        await this.dailyPriceTextbox.fill("3000");
        await this.saveProductButton.click();
        return product.name;
    }

    async createRentalBookingProductHourlyBasisSameSlotAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("rental");
        await this.bookingInput("location").fill(product.location);
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("1");
        await this.bookingSelect("renting_type").selectOption("hourly");
        await this.hourlyPriceTextbox.fill("300");
        await this.bookingSelect("same_slot_all_days").selectOption("1");
        await this.addSlotsButton.click();
        await this.fillTimeTextbox("From", 0, "14", "20");
        await this.fillTimeTextbox("To", 0, "18", "35");
        await this.escapeTarget.press("Escape");
        await this.modalSaveButton.click();
        await this.saveProductButton.click();
        return product.name;
    }

    async createRentalBookingProductHourlyBasisNotSameSlotAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("rental");
        await this.bookingInput("location").fill(product.location);
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("1");
        await this.bookingSelect("renting_type").selectOption("hourly");
        await this.hourlyPriceTextbox.fill("300");
        await this.bookingSelect("same_slot_all_days").selectOption("0");
        await this.fillInlineDaySlot(1, "10", "35", "13", "45", false);
        await this.fillInlineDaySlot(2, "09", "25", "13", "25", false);
        await this.saveProductButton.click();
        return product.name;
    }

    async handleRentalBookingWithShorterTimeRangeThanSlots(isAvailableEveryWeek: boolean, isSameSlotAllDays: boolean) {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("rental");
        await this.bookingInput("location").fill(product.location);
        await this.bookingInput("qty").fill(product.weight);
        if (isAvailableEveryWeek) {
            await this.bookingSelect("available_every_week").selectOption("1");
            await this.bookingSelect("renting_type").selectOption("hourly");
            await this.hourlyPriceTextbox.fill("300");
            if (isSameSlotAllDays) {
                await this.bookingSelect("same_slot_all_days").selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "14", "20");
                await this.fillTimeTextbox("To", 0, "14", "50");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();
            } else {
                await this.bookingSelect("same_slot_all_days").selectOption("0");
                await this.page.waitForLoadState("networkidle");

                await this.fillInlineDaySlot(1, "10", "35", "11", "00", false);
                await this.modalSaveButton.click();
            }
        } else {
            await this.bookingSelect("available_every_week").selectOption("0");
            await this.fillAvailabilityRange(
                "2027-04-08 16:00:00",
                "2027-04-25 18:00",
            );
            await this.bookingSelect("renting_type").selectOption("hourly");
            await this.hourlyPriceTextbox.fill("300");
            if (isSameSlotAllDays) {
                await this.bookingSelect("same_slot_all_days").selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "14", "20");
                await this.fillTimeTextbox("To", 0, "14", "45");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();
            } else {
                await this.bookingSelect("same_slot_all_days").selectOption("0");
                await this.fillInlineDaySlot(1, "10", "35", "11", "00", false);
                await this.modalSaveButton.click();

            }
        }
    }

    async createRentalBookingProductBothBasisSameSlotAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("rental");
        await this.bookingInput("location").fill(product.location);
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("1");
        await this.bookingSelect("renting_type").selectOption("daily_hourly");
        await this.dailyPriceTextbox.fill("3000");
        await this.hourlyPriceTextbox.fill("300");
        await this.bookingSelect("same_slot_all_days").selectOption("1");
        await this.addSlotsButton.click();
        await this.fillTimeTextbox("From", 0, "14", "20");
        await this.fillTimeTextbox("To", 0, "18", "35");
        await this.escapeTarget.press("Escape");
        await this.modalSaveButton.click();
        await this.saveProductButton.click();
        return product.name;
    }

    async createRentalBookingProductBothBasisNotSameSlotAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("rental");
        await this.bookingInput("location").fill(product.location);
        await this.bookingInput("qty").fill(product.weight);
        await this.bookingSelect("available_every_week").selectOption("1");
        await this.bookingSelect("renting_type").selectOption("daily_hourly");
        await this.dailyPriceTextbox.fill("3000");
        await this.hourlyPriceTextbox.fill("300");
        await this.bookingSelect("same_slot_all_days").selectOption("0");
        await this.fillInlineDaySlot(1, "10", "35", "13", "45", true);
        await this.fillInlineDaySlot(2, "09", "25", "13", "25", true);
        await this.saveProductButton.click();
        return product.name;
    }


    async createRentalBookingProductBothhourlyDailywith_and_withoutRange(isAvailableEveryWeek: boolean, isSameSlotAllDays: boolean) {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("rental");
        await this.bookingInput("location").fill(product.location);
        await this.bookingInput("qty").fill(product.weight);
        if (isAvailableEveryWeek) {
            await this.bookingSelect("available_every_week").selectOption("1");
            await this.bookingSelect("renting_type").selectOption(
                "daily_hourly",
            );
            await this.dailyPriceTextbox.fill("3000");
            await this.hourlyPriceTextbox.fill("300");
            if (isSameSlotAllDays) {
                await this.bookingSelect("same_slot_all_days").selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "14", "20");
                await this.fillTimeTextbox("To", 0, "14", "50");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();

            } else {
                await this.bookingSelect("same_slot_all_days").selectOption("0");
                await this.page.waitForLoadState("networkidle");

                await this.fillInlineDaySlot(1, "10", "35", "11", "00", false);
                await this.modalSaveButton.click();
            }
        } else {
            await this.bookingSelect("available_every_week").selectOption("0");
            await this.fillAvailabilityRange(
                "2027-04-08 16:00:00",
                "2027-04-25 18:00",
            );
            await this.bookingSelect("renting_type").selectOption(
                "daily_hourly",
            );
            await this.dailyPriceTextbox.fill("3000");
            await this.hourlyPriceTextbox.fill("300");
            if (isSameSlotAllDays) {
                await this.bookingSelect("same_slot_all_days").selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "14", "20");
                await this.fillTimeTextbox("To", 0, "14", "45");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();
            } else {
                await this.bookingSelect("same_slot_all_days").selectOption("0");
                await this.fillInlineDaySlot(1, "10", "35", "11", "00", false);
                await this.modalSaveButton.click();

            }
        }
    }

    async createTableBookingProductChargePerGuestSameSlotAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("table");
        await this.bookingInput("location").fill(product.location);
        await this.bookingSelect("available_every_week").selectOption("0");
        await this.fillAvailabilityRange(
            "2027-04-08 16:00:00",
            "2027-04-25 18:00",
        );
        await this.bookingSelect("price_type").selectOption("guest");
        await this.guestCapacityTextbox.fill("2");
        await this.slotDurationTextbox.fill("25");
        await this.breakTimeTextbox.fill("10");
        await this.preventSchedulingBeforeTextbox.fill("0");
        await this.tableSameSlotAllDaysSelect.selectOption("1");
        await this.addSlotsButton.click();
        await this.fillTimeTextbox("From", 0, "14", "20");
        await this.fillTimeTextbox("To", 0, "18", "35");
        await this.escapeTarget.press("Escape");
        await this.modalSaveButton.click();
        await this.saveProductButton.click();
        return product.name;
    }

    async createTableBookingProductChargePerGuestNotSameSlotAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("table");
        await this.bookingInput("location").fill(product.location);
        await this.bookingSelect("available_every_week").selectOption("0");
        await this.fillAvailabilityRange(
            "2027-04-08 16:00:00",
            "2027-04-25 18:00",
        );
        await this.bookingSelect("price_type").selectOption("guest");
        await this.guestCapacityTextbox.fill("2");
        await this.slotDurationTextbox.fill("25");
        await this.breakTimeTextbox.fill("10");
        await this.preventSchedulingBeforeTextbox.fill("0");
        await this.tableSameSlotAllDaysSelect.selectOption("0");
        await this.fillInlineDaySlot(1, "10", "35", "13", "45", false);
        await this.fillInlineDaySlot(2, "09", "25", "13", "25", false);
        await this.saveProductButton.click();
        return product.name;
    }

    async createTableBookingProductChargePerTableSameSlotAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("table");
        await this.bookingInput("location").fill(product.location);
        await this.bookingSelect("available_every_week").selectOption("0");
        await this.fillAvailabilityRange(
            "2027-04-08 16:00:00",
            "2027-04-25 18:00",
        );
        await this.bookingSelect("price_type").selectOption("table");
        await this.guestLimitPerTableTextbox.fill("4");
        await this.guestCapacityTextbox.fill("3");
        await this.slotDurationTextbox.fill("25");
        await this.breakTimeTextbox.fill("10");
        await this.preventSchedulingBeforeTextbox.fill("0");
        await this.tableSameSlotAllDaysSelect.selectOption("1");
        await this.addSlotsButton.click();
        await this.fillTimeTextbox("From", 0, "14", "20");
        await this.fillTimeTextbox("To", 0, "18", "35");
        await this.escapeTarget.press("Escape");
        await this.modalSaveButton.click();
        await this.saveProductButton.click();
        return product.name;
    }

    async createTableBookingProductChargePerTableNotSameSlotAllDays() {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("table");
        await this.bookingInput("location").fill(product.location);
        await this.bookingSelect("available_every_week").selectOption("0");
        await this.fillAvailabilityRange(
            "2027-04-08 16:00:00",
            "2027-04-25 18:00",
        );
        await this.bookingSelect("price_type").selectOption("table");
        await this.guestLimitPerTableTextbox.fill("4");
        await this.guestCapacityTextbox.fill("3");
        await this.slotDurationTextbox.fill("25");
        await this.breakTimeTextbox.fill("10");
        await this.preventSchedulingBeforeTextbox.fill("0");
        await this.tableSameSlotAllDaysSelect.selectOption("0");
        await this.fillInlineDaySlot(1, "10", "35", "13", "45", true);
        await this.fillInlineDaySlot(2, "09", "25", "13", "25", false);
        await this.saveProductButton.click();
        return product.name;
    }

    async handleGuestTableBookingWithShorterTimeRangeThanSlots(isAvailableEveryWeek: boolean, isSameSlotAllDays: boolean) {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("table");
        await this.bookingInput("location").fill(product.location);

        if (!isAvailableEveryWeek) {
            await this.bookingSelect("available_every_week").selectOption("0");
            await this.fillAvailabilityRange(
                "2027-04-08 16:00:00",
                "2027-04-25 18:00",
            );
            await this.bookingSelect("price_type").selectOption("guest");
            await this.guestCapacityTextbox.fill("2");
            await this.slotDurationTextbox.fill("45");
            await this.breakTimeTextbox.fill("10");
            await this.preventSchedulingBeforeTextbox.fill("0");
            if (isSameSlotAllDays) {
                await this.tableSameSlotAllDaysSelect.selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "14", "20");
                await this.fillTimeTextbox("To", 0, "14", "35");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();

            } else {
                await this.tableSameSlotAllDaysSelect.selectOption("0");
                await this.fillInlineDaySlot(1, "10", "35", "10", "55", false);

            }
        } else {
            await this.bookingSelect("available_every_week").selectOption("1");
            await this.bookingSelect("price_type").selectOption("guest");
            await this.guestCapacityTextbox.fill("2");
            await this.slotDurationTextbox.fill("45");
            await this.breakTimeTextbox.fill("10");
            await this.preventSchedulingBeforeTextbox.fill("0");
            if (isSameSlotAllDays) {
                await this.tableSameSlotAllDaysSelect.selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "14", "20");
                await this.fillTimeTextbox("To", 0, "14", "45");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();

            } else {
                await this.tableSameSlotAllDaysSelect.selectOption("0");
                await this.fillInlineDaySlot(1, "10", "35", "11", "00", false);
            }
        }
    }

    async handleTable_TableBookingWithShorterTimeRangeThanSlots(isAvailableEveryWeek: boolean, isSameSlotAllDays: boolean) {
        const product = await this.createBookingProductBase();
        await this.bookingSelect("type").selectOption("table");
        await this.bookingInput("location").fill(product.location);

        if (!isAvailableEveryWeek) {
            await this.bookingSelect("available_every_week").selectOption("0");
            await this.fillAvailabilityRange(
                "2027-04-08 16:00:00",
                "2027-04-25 18:00",
            );
            await this.bookingSelect("price_type").selectOption("table");
            await this.guestCapacityTextbox.fill("2");
            await this.slotDurationTextbox.fill("45");
            await this.breakTimeTextbox.fill("10");
            await this.preventSchedulingBeforeTextbox.fill("0");
            if (isSameSlotAllDays) {
                await this.tableSameSlotAllDaysSelect.selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "14", "20");
                await this.fillTimeTextbox("To", 0, "14", "35");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();

            } else {
                await this.tableSameSlotAllDaysSelect.selectOption("0");
                await this.fillInlineDaySlot(1, "10", "35", "10", "55", false);

            }
        } else {
            await this.bookingSelect("available_every_week").selectOption("1");
            await this.bookingSelect("price_type").selectOption("table");
            await this.guestCapacityTextbox.fill("2");
            await this.slotDurationTextbox.fill("45");
            await this.breakTimeTextbox.fill("10");
            await this.preventSchedulingBeforeTextbox.fill("0");
            if (isSameSlotAllDays) {
                await this.tableSameSlotAllDaysSelect.selectOption("1");
                await this.addSlotsButton.click();
                await this.fillTimeTextbox("From", 0, "14", "20");
                await this.fillTimeTextbox("To", 0, "14", "45");
                await this.escapeTarget.press("Escape");
                await this.modalSaveButton.click();

            } else {
                await this.tableSameSlotAllDaysSelect.selectOption("0");
                await this.fillInlineDaySlot(1, "10", "35", "11", "00", false);

            }
        }
    }

    private async fillTimeTextbox(
        label: "From" | "To",
        index: number,
        hour: string,
        minute: string,
    ) {
        await this.slotTimeTextbox(label, index).click();
        await this.flatpickrCalendar.waitFor({
            state: "visible",
        });
        await this.hourSpinbutton.fill(hour);
        await this.minuteSpinbutton.fill(minute);
        await this.page.waitForTimeout(500);
        await this.minuteSpinbutton.press("Enter");
    }

    private async fillInlineDaySlot(
        dayIndex: number,
        fromHour: string,
        fromMinute: string,
        toHour: string,
        toMinute: string,
        pressEscapeBeforeSave: boolean,
    ) {
        await this.inlineDaySlotTrigger(dayIndex).click();
        await this.fillTimeTextbox("From", 0, fromHour, fromMinute);
        await this.page.waitForTimeout(500);
        await this.fillTimeTextbox("To", 0, toHour, toMinute);
        if (pressEscapeBeforeSave) {
            await this.escapeTarget.press("Escape");
        }
        await this.modalSaveButton.click();
=======
        await expect(this.typeSelect).toBeVisible();
>>>>>>> v2.5.0
    }
}
