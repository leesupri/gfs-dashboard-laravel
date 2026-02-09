// resources/js/app.js
import Alpine from "alpinejs";
import registerSalesPage from "./pages/sales";

window.Alpine = Alpine;

// import halaman

registerSalesPage(Alpine);

Alpine.start();