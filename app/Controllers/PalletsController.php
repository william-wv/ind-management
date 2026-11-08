<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;
use App\Models\Pallet;

class PalletsController extends Controller
{
    public function new(): void
    {
         $title = "Cadastro de Pallets";
         $newPallet =  new Pallet();

         $this->render('pallets/create', compact('title', 'newPallet'));
    }

    public function show(Request $request): void
    {
            $this->retrievePallet($request);
    }

    public function showUpdateForm(Request $request): void
    {
        $pallet = Pallet::findBy(['id' => $request->getParam('id')]);

        if ($pallet) {
             $title = "Atualizar Pallet";
             $this->render('pallets/update', compact('title', 'pallet'));
             return;
        }
        FlashMessage::danger('Falha ao atualizar dados do Pallet');
        $this->redirectTo(route('pallets.index'));
    }

    public function showUpload(Request $request):void
    {
        $pallet = Pallet::findBy(['id' => $request->getParam('id')]);

        if($pallet){
            $title = "Anexar Ordem de Venda";
            $this->render('pallets/upload', compact('title', 'pallet'));
            return;
        }

         FlashMessage::danger('Falha ao atualizar dados do Pallet');
         $this->redirectTo(route('pallets.index'));
    }

    public function deletePallet(Request $request): void
    {
        $pallet = Pallet::findBy(['id' => $request->getParam('id')]);
        if ($pallet) {
            if ($pallet->destroy()) {
                FlashMessage::success('Pallet Excluido');
                $this->redirectTo(route('pallets.index'));
            }
                 FlashMessage::danger('não foi possivel excluir pallet');
                $this->redirectTo(route('pallets.index'));
        }
                 FlashMessage::danger('Impossivel excluir, pallet inexistente');
                 $this->redirectTo(route('pallets.index'));
    }

    public function registerPallet(Request $request): void
    {
        $params = $request->getParam('pallet');

        $newPallet = new Pallet($params);

        if ($newPallet->save()) {
            FlashMessage::success('Pallet registrado com sucesso!');
            $this->redirectTo(route('root'));
        } else {
            $title = "Cadastro de Pallets";
            FlashMessage::danger('Erro ao cadastrar pallet');
            $this->render('pallets/create', compact('title', 'newPallet'));
             return;
        }
    }

    public function retrievePallet(Request $request): void
    {
        $page = filter_var($request->getParam('page', 1), FILTER_VALIDATE_INT);
        $page = $page !== false && $page > 0 ? $page : 1;
        $perPage = 10;

        $paginator = Pallet::paginate($page, $perPage, 'pallets.index');
        if ($paginator->totalOfPages() > 0 && $page > $paginator->totalOfPages()) {
            $page = $paginator->totalOfPages();
            $paginator = Pallet::paginate($page, $perPage, 'pallets.index');
        }

        $title = "Estoque Virtual";
        $this->render('pallets/show', compact('title', 'paginator'));
    }

    public function updatePallet(Request $request): void
    {
        $params = $request->getParam('pallet');

        $pallet = Pallet::findBy(['id' => $request->getParam('id')]);

        if ($pallet) {
            $pallet->width = $params['width'];
            $pallet->length = $params['length'];
            $pallet->amount = $params['amount'];
            $pallet->minimum_amount = $params['minimum_amount'];
            $pallet->location = $params['location'];
            $pallet->item = $params['item'];

            $palletExist = Pallet::findBy(['length' => $pallet->length, 'width' => $pallet->width]);

            if ($palletExist == $pallet) {
                 FlashMessage::danger('Nenhuma alteração foi detectada');
                 $title = "Atualizar Pallets";
                 $this->render('pallets/update', compact('title', 'pallet'));
                 return;
            }

            if ($palletExist && $palletExist->id !== $pallet->id) {
                FlashMessage::danger('Pallet já cadastrado');
                $this->redirectTo(route('pallets.index'));
            }

            if ($pallet->isValid()) {
                $updated = $pallet->update([
                    'width' => $params['width'],
                    'length' => $params['length'],
                    'amount' => $params['amount'],
                    'minimum_amount' => $params['minimum_amount'],
                    'location' => $params['location'],
                    'item' => $params['item'],
                ]);
                if ($updated) {
                    FlashMessage::success('Pallet atualizado');
                    $this->redirectTo(route('pallets.index'));
                } else {
                    FlashMessage::danger('Falha ao atualizar pallet');
                    $this->redirectTo(route('pallets.index'));
                }
            }

            $title = "Atualizar Pallets";
            $this->render('pallets/update', compact('title', 'pallet'));
            return;
        }

        FlashMessage::danger('Pallet inexistente');
        $this->redirectTo(route('pallets.index'));
    }

    public function upload( Request $request):void
    {
          $id = $request->getParam('id');

          //$images = $_FILES['images'];

          $pallet = Pallet::findBy(['id' => $id]);

         $photo = $pallet->photos()->new(['url' => 'screnshot/01','original_name' => 'teste', 'mime_type' =>'png', 'size_bytes' => 1234]);

          var_dump($photo->save());


          echo "ID recebido: " . $id . "<br>";

            echo "<pre>";
            //print_r($pallet->photos);
            echo "</pre>";

    }
}
