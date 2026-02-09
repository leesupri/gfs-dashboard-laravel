// resources/js/pages/sales.js
export default function registerSalesPage(Alpine) {
    Alpine.data("salesPage", () => ({
        filtersOpen: false,
        receiptOpen: false,
        receipt: null,
        receiptItems: [],
        receiptPayments: [],
        receiptError: "",
        loadingReceipt: false,

        init() {
            console.log("salesPage mounted");

            const url = new URL(window.location.href);
            const hasFilters =
                url.searchParams.get("q") ||
                url.searchParams.get("start") ||
                url.searchParams.get("end") ||
                url.searchParams.get("outlet") ||
                url.searchParams.get("payment_type") ||
                url.searchParams.get("sort");

            if (hasFilters) this.filtersOpen = true;

            // Quick date presets
            const quick = document.getElementById("quickDate");
            const start = document.getElementById("startDate");
            const end = document.getElementById("endDate");

            if (quick && start && end) {
                const pad2 = (n) => String(n).padStart(2, "0");
                const toYMD = (d) =>
                    `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;

                const today = new Date();

                quick.addEventListener("change", () => {
                    if (!quick.value) return;

                    if (quick.value === "today") {
                        start.value = toYMD(today);
                        end.value = toYMD(today);
                    } else if (quick.value === "this_month") {
                        start.value = toYMD(new Date(today.getFullYear(), today.getMonth(), 1));
                        end.value = toYMD(new Date(today.getFullYear(), today.getMonth() + 1, 0));
                    } else if (quick.value === "last_month") {
                        start.value = toYMD(new Date(today.getFullYear(), today.getMonth() - 1, 1));
                        end.value = toYMD(new Date(today.getFullYear(), today.getMonth(), 0));
                    }

                    quick.closest("form")?.submit();
                });
            }
        },

        async openReceipt(invoiceId) {
            if (!invoiceId) {
                this.receiptOpen = true;
                this.receiptError = "Invoice ID kosong.";
                return;
            }
            

            this.receiptOpen = true;
            this.loadingReceipt = true;
            this.receiptError = "";
            this.receipt = null;
            this.receiptItems = [];
            this.receiptPayments = [];

            document.body.classList.add("overflow-hidden");

            try {
                const res = await fetch(`/sales/${invoiceId}/receipt`, {
                    headers: { Accept: "application/json" },
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const data = await res.json();

                this.receipt = {
                    ...data.sale,
                    invoice_id: data.invoice_id,
                    paid_total: data.paid_total,
                    diff: data.diff,
                };

                this.receiptItems = this.groupReceiptItems(
                    Array.isArray(data.items) ? data.items : []
                );

                this.receiptPayments = Array.isArray(data.payments)
                    ? data.payments
                    : [];
            } catch (e) {
                console.error(e);
                this.receiptError = "Gagal load receipt: " + (e?.message || "");
            } finally {
                this.loadingReceipt = false;
            }
        },

        groupReceiptItems(items) {
            const isModifier = (it) => {
                const desc = String(it.description || "").trim();
                const cat = String(it.category || "").toUpperCase();
                const dept = String(it.department || "").toUpperCase();

                if (cat === "MODIFIER") return true;
                if (dept === "MODIFIER") return true;
                if (desc.startsWith("~") || desc.startsWith("-")) return true;
                return false;
            };

            const parents = [];
            let lastParent = null;

            for (const it of items) {
                if (isModifier(it)) {
                    if (!lastParent) {
                        parents.push({ ...it, _modifiers: [], _isModifier: true });
                    } else {
                        lastParent._modifiers.push({ ...it, _isModifier: true });
                    }
                    continue;
                }

                const parent = { ...it, _modifiers: [], _isModifier: false };
                parents.push(parent);
                lastParent = parent;
            }

            return parents;
        },

        closeReceipt() {
            this.receiptOpen = false;
            document.body.classList.remove("overflow-hidden");
        },

        /* =========================
         * Currency formatter
         * ALWAYS 2 decimals
         * ========================= */
        idr(value) {
            const num = Number(value || 0);
            return (
                "Rp " +
                num.toLocaleString("id-ID", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        /* =========================
         * Safe date formatter
         * ========================= */
        formatDate(v) {
            if (!v) return "-";
            const d = new Date(v);
            if (isNaN(d)) return "-";
            return d.toLocaleString("id-ID");
        },
    }));
}
