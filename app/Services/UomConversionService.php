<?php

namespace App\Services;

class UomConversionService
{
    public function convertToBase(float $qtyEntered, string $uomEntered, object $item): float
    {
        $rec = $item->recipeUom ?? '';
        $pur = $item->purchaseUom ?? '';
        $iTR = (float) ($item->inventoryToRecipeConversion ?? 0);
        $pTI = (float) ($item->purchaseToInventoryConversion ?? 0);

        if ($uomEntered === $rec && $iTR > 0) {
            return $qtyEntered / $iTR;
        }

        if ($uomEntered === $pur && $pTI > 0) {
            return $qtyEntered * $pTI;
        }

        return $qtyEntered;
    }

    public function getUomOptions(object $item): array
    {
        $options = [];

        if (!empty($item->uom)) {
            $options[] = ['uom' => $item->uom, 'label' => $item->uom . ' (inv)', 'type' => 'inv'];
        }

        if (!empty($item->recipeUom) && $item->recipeUom !== $item->uom) {
            $options[] = ['uom' => $item->recipeUom, 'label' => $item->recipeUom . ' (recipe)', 'type' => 'recipe'];
        }

        if (!empty($item->purchaseUom) && $item->purchaseUom !== $item->uom) {
            $options[] = ['uom' => $item->purchaseUom, 'label' => $item->purchaseUom . ' (purchase)', 'type' => 'purchase'];
        }

        return $options;
    }
}
