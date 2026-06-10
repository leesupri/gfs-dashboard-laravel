export default function registerStockCountsPage(Alpine) {
    Alpine.data('stockCountActions', () => ({
        showApproveModal: false,
        showRejectModal: false,
        rejectReason: '',
        openApprove() { this.showApproveModal = true; },
        closeApprove(){ this.showApproveModal = false; },
        openReject()  { this.showRejectModal  = true; },
        closeReject() {
            this.showRejectModal = false;
            this.rejectReason    = '';
        },
    }));
}
