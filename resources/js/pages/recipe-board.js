// resources/js/pages/recipe-board.js

export default function registerRecipeBoardPage(Alpine) {

    /**
     * recipeBoard()
     * Outer wrapper — broadcasts expand / collapse to all cards.
     */
    Alpine.data('recipeBoard', () => ({
        expandAll() {
            window.dispatchEvent(new CustomEvent('board-expand'));
        },
        collapseAll() {
            window.dispatchEvent(new CustomEvent('board-collapse'));
        },
    }));

    /**
     * recipeCard(id)
     * Per-card open/close. Responds to board-level broadcasts.
     * openModifiers controls the modifier pages section independently.
     */
    Alpine.data('recipeCard', (id) => ({
        id,
        open: false,
        openModifiers: false,
    }));

    /**
     * conversiPreview(itemId)
     * Controls the inline conversi sub-recipe expansion within a card row.
     * Each conversi ingredient row gets its own independent toggle.
     */
    Alpine.data('conversiPreview', (itemId) => ({
        itemId,
        open: false,
        toggle() {
            this.open = !this.open;
        },
    }));

    /**
     * comboPreview(itemId)
     * Controls the inline combo menu expansion within a card row.
     * Same shape as conversiPreview — separate name for easy diagnosis.
     */
    Alpine.data('comboPreview', (itemId) => ({
        itemId,
        open: false,
        toggle() {
            this.open = !this.open;
        },
    }));

}