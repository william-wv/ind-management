<?php

namespace App\Services;

use App\Models\Movement;
use App\Models\Pallet;
use App\Models\PalletMovement;
use Core\Database\Database;
use RuntimeException;

class MovePallet
{
    private const ALLOWED_TYPES = ['inbound', 'outbound', 'inventory'];

    /**
     * @param array{
     *     type: ?string,
     *     observation: ?string,
     *     user_id: ?int,
     *     pallets?: array<int, array{pallet_id: mixed, quantity: mixed}>
     * } $data
     */
    public function create(array $data): Movement
    {
        $movement = new Movement([
            'type' => $data['type'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'observation' => $data['observation'] ?? null,
        ]);

        if (!in_array($movement->type, self::ALLOWED_TYPES, true)) {
            $movement->addError('type', 'Tipo de movimentação inválido.');
            return $movement;
        }

        $pallets = $data['pallets'] ?? [];
        if (empty($pallets)) {
            $movement->addError('pallet_movements', 'Informe ao menos um pallet.');
            return $movement;
        }

        try {
            Database::transaction(function () use ($movement, $pallets) {
                if (!$movement->save()) {
                    throw new RuntimeException('Não foi possível registrar a movimentação.');
                }

                foreach ($pallets as $item) {
                    $this->applyPalletMovement($movement, $item);
                }
            });
        } catch (RuntimeException $e) {
            $movement->addError('pallet_movements', $e->getMessage());
        }

        return $movement;
    }

    /**
     * @param array{pallet_id: mixed, quantity: mixed} $item
     */
    private function applyPalletMovement(Movement $movement, array $item): void
    {
        $pallet = Pallet::findById((int) ($item['pallet_id'] ?? 0));

        if ($pallet === null) {
            throw new RuntimeException('Pallet não encontrado.');
        }

        $quantity = (int) ($item['quantity'] ?? 0);
        if ($quantity <= 0) {
            throw new RuntimeException("Quantidade inválida para o pallet {$pallet->item}.");
        }

        $quantityBefore = (int) $pallet->amount;
        $quantityAfter = match ($movement->type) {
            'inbound' => $quantityBefore + $quantity,
            'outbound' => $quantityBefore - $quantity,
            'inventory' => $quantity,
            default => throw new RuntimeException('Tipo de movimentação inválido.'),
        };

        if ($quantityAfter < 0) {
            throw new RuntimeException("Quantidade insuficiente no pallet {$pallet->item}.");
        }

        $palletMovement = new PalletMovement([
            'movement_id' => $movement->id,
            'pallet_id' => $pallet->id,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
        ]);

        if (!$palletMovement->save()) {
            throw new RuntimeException('Não foi possível registrar a movimentação do pallet.');
        }

        if (!$pallet->update(['amount' => $quantityAfter])) {
            throw new RuntimeException('Não foi possível atualizar o estoque do pallet.');
        }
    }

    public function destroy(PalletMovement $palletMovement): PalletMovement
    {
        /** @var Pallet $pallet */
        $pallet = $palletMovement->pallet()->get();

        try {
            Database::transaction(function () use ($palletMovement, $pallet) {
                $quantityBefore = $palletMovement->quantity_before;


                if (!$palletMovement->destroy()) {
                    throw new RuntimeException('Não foi possível excluir movimentação');
                }

                $this->restoreQuantityPallet($pallet, $quantityBefore);
            });
        } catch (RuntimeException $e) {
            $palletMovement->addError('pallet_movements', $e->getMessage());
        }

        return $palletMovement;
    }

    private function restoreQuantityPallet(Pallet $pallet, int $previous): void
    {

        if (!$pallet->update(['amount' => $previous])) {
            throw new RuntimeException('Não foi possível atualizar a quantidade do pallet durante a exclusão');
        }
    }
}
