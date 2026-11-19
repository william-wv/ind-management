<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;
use App\Models\Pallet;
use App\Services\UploaderPhotos;

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

    public function showUpload(Request $request): void
    {
        $pallet = Pallet::findBy(['id' => $request->getParam('id')]);

        if ($pallet) {
            $title = "Anexar Ordem de Venda";
            $this->render('pallets/upload', compact('title', 'pallet'));
            return;
        }

         FlashMessage::danger('Falha ao atualizar dados do Pallet');
         $this->redirectTo(route('pallets.index'));
    }

    public function showGallery(Request $request): void
    {
        $pallet = Pallet::findBy(['id' => $request->getParam('id')]);

        if ($pallet) {
            $photos = $pallet->photos()->get();
            $title = "Galeria de Ordens de Venda";
            $this->render('pallets/gallery', compact('title', 'pallet', 'photos'));
            return;
        }

         FlashMessage::danger('Falha ao acessar galeria do Pallet');
         $this->redirectTo(route('pallets.index'));
    }

    public function deletePallet(Request $request): void
    {
        $pallet = Pallet::findBy(['id' => $request->getParam('id')]);
        if ($pallet) {
            $serviceUploader = new UploaderPhotos($pallet);

            if (!$serviceUploader->destroyAll()) {
                FlashMessage::danger('não foi possivel excluir pallet, arquivos vinculados não foram excluidos');
                $this->redirectTo(route('pallets.index'));
            }
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

    public function upload(Request $request): void
    {
         $id = $request->getParam('id');

         $images = $_FILES['images'];

         $pallet = Pallet::findBy(['id' => $id]);

        if (!$pallet) {
            FlashMessage::danger('Pallet inexistente');
            $this->redirectTo(route('pallets.index'));
        }

        $uploaderService = new UploaderPhotos($pallet);

        if ($uploaderService->upload($images)) {
             FlashMessage::success('Imagem gravada');
             $this->redirectTo(route('pallets.index'));
        }

            $title = "Anexar Ordem de Venda";
            $this->render('pallets/upload', compact('title', 'pallet'));
            return;
    }

    public function removeImg(Request $request): void
    {
        $pallet = Pallet::findBy(['id' => $request->getParam('id')]);
        $photoId = $request->getParam('photo_id');

        if (!$pallet) {
            FlashMessage::danger('não foi possivel excluir a foto, pallet não encontrado');
            $this->redirectTo(route('pallets.index'));
        }

        if (!$photoId || $photoId < 0) {
            FlashMessage::danger('não foi possivel excluir a foto, valor de referência vazio');
            $this->redirectTo(route('pallets.gallery', ['id' => $pallet->id]));
        }


        $uploaderService = new UploaderPhotos($pallet);
        $photoId = (int) $photoId;

        if (!$uploaderService->destroy($photoId)) {
             $message = $pallet->errors('photo') ?? 'Não foi possível excluir a foto.';
             FlashMessage::danger($message);
            $this->redirectTo(route('pallets.gallery', ['id' => $pallet->id]));
            return;
        };

        FlashMessage::success('Foto excluída.');
        $this->redirectTo(route('pallets.gallery', ['id' => $pallet->id]));
    }
}
