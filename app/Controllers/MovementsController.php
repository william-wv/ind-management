<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;
use App\Models\Pallet;
use App\Models\Movement;
use App\Services\MovePallet;
use App\Models\PalletMovement;

class MovementsController extends Controller
{
    public function new(): void
    {
        $title = "Registro de movimentação";
        $pallets = Pallet::all();

        $this->render('movements/new', compact('title', 'pallets'));
    }

    public function index(): void
    {
        $title = "Movimentações";
        $movements = Movement::all();

        $this->render('movements/history', compact('title', 'movements'));
    }

    public function create(Request $request): void
    {
        $movementParams = $request->getParam('movement', []);
        $palletMovementsParams = $request->getParam('pallet_movements', []);

        $data = [
            'type' => $movementParams['type'] ?? null,
            'observation' => $movementParams['observation'] ?? null,
            'user_id' => $this->currentUser()?->id,
            'pallets' => $palletMovementsParams,
        ];

        $movement = (new MovePallet())->create($data);

        if (!$movement->hasErrors()) {
            FlashMessage::success('Movimentação registrada com sucesso.');
            $this->redirectTo(route('movements.index'));
            return;
        }

        $message = $movement->errors('type') ?? $movement->errors('pallet_movements') ?? 'Erro ao registrar movimentação.';
        FlashMessage::danger($message);

        $title = "Registro de movimentação";
        $pallets = Pallet::all();
        $movementType = $movementParams['type'] ?? 'inbound';

        $this->render('movements/new', compact('title', 'pallets', 'movementType'));
    }

    public function delete(Request $request):void
    {
        $params = $request->getParam('id');

        $palletMovement = PalletMovement::findById($params);

        if(!$palletMovement){
            FlashMessage::danger('Movimentação inexistente');
            $this->redirectTo(route('movements.history'));
            return;
        }

        $excludedMovement = (new MovePallet())->destroy($palletMovement);

        if(!$excludedMovement->hasErrors()){
            FlashMessage::success('Movimentação excluida com sucesso.');
            $this->redirectTo(route('movements.history'));
            return;
        }

        $message = $excludedMovement->errors('pallet_movements') ?? 'Erro ao excluir movimentação.';
        FlashMessage::danger($message);
        $this->redirectTo(route('movements.history'));
    }
}
