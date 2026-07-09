// resources/js/pages/stock-counts.js

export default function registerStockCountsPage(Alpine) {
    Alpine.data('stockCountActions', () => ({
        showApproveModal: false,
        showRejectModal: false,
        rejectReason: '',

        openApprove() {
            this.showApproveModal = true;
        },
        closeApprove() {
            this.showApproveModal = false;
        },
        openReject() {
            this.rejectReason = '';
            this.showRejectModal = true;
        },
        closeReject() {
            this.showRejectModal = false;
        },
    }));
}
