/**
 * Alpine.js component for the Printer Settings page.
 * Manages the add/edit station modal and logo preview.
 *
 * Usage in blade:
 *   import { registerPrinterSettingsPage } from '.../printer-settings.js';
 *   registerPrinterSettingsPage(window.Alpine);
 */
export function registerPrinterSettingsPage(Alpine) {
    Alpine.data('printerSettings', () => ({
        showModal: false,
        editingId: null,

        form: {
            name: '',
            ip_address: '',
            location: '',
            is_active: true,
        },

        storeUrl: '',
        updateUrlTemplate: '',

        logoPreview: null,

        init(el) {
            const card = el.querySelector('[data-store-url]');
            if (card) {
                this.storeUrl         = card.dataset.storeUrl;
                this.updateUrlTemplate = card.dataset.updateUrl;
            }
        },

        get formAction() {
            if (!this.editingId) return this.storeUrl;
            return this.updateUrlTemplate.replace('__ID__', this.editingId);
        },

        get isEditing() {
            return this.editingId !== null;
        },

        openAdd() {
            this.editingId = null;
            this.form = { name: '', ip_address: '', location: '', is_active: true };
            this.showModal = true;
        },

        openEdit(station) {
            this.editingId     = station.id;
            this.form.name      = station.name ?? '';
            this.form.ip_address = station.ip_address ?? '';
            this.form.location  = station.location ?? '';
            this.form.is_active = Boolean(station.is_active);
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        previewLogo(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => { this.logoPreview = e.target.result; };
            reader.readAsDataURL(file);
        },
    }));
}
