// resources/js/app.js
import Alpine from "alpinejs";
import Collapse from "@alpinejs/collapse";
import registerSalesPage from "./pages/sales";
import registerRecipeBoardPage from "./pages/recipe-board";
import registerStockCountsPage from "./pages/stock-counts";

window.Alpine = Alpine;

Alpine.plugin(Collapse);

// import halaman
registerSalesPage(Alpine);
registerRecipeBoardPage(Alpine);
registerStockCountsPage(Alpine);

Alpine.start();
 