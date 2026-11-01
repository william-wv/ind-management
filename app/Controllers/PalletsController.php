<?php

namespace App\Controllers;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;
use App\Models\Pallet;


class PalletsController extends Controller{

   public function new():void
   {
         $title = "Cadastro de Pallets";
         $newPallet =  new Pallet();

         $this->render('pallets/create',compact('title','newPallet'));
   }

   public function show( ):void
   {
            $this->retrievePallet();

   }

   public function showUpdateForm(Request $request):void
   {    $params = $request->getParam('pallet');
        $pallet = Pallet::findBy(['id' => $params['id']]);

        if($pallet){
             $title = "Atualizar Pallet";
             $this->render('pallets/update',compact('title','pallet'));
             return;
        }
        FlashMessage::danger('Falha ao atualizar dados do Pallet');
        $this->redirectTo(route('pallets.show'));
   }
   
   public function deletePallet(Request $request):void
   {    
        $params = $request->getParam('pallet');
         
        $pallet = Pallet::findBy(['id' => $params['id']]);
        if($pallet){
            if($pallet->destroy()){
                FlashMessage::success('Pallet Excluido');
                $this->redirectTo(route('pallets.show'));
            }
                 FlashMessage::danger('não foi possivel excluir pallet');
                $this->redirectTo(route('pallets.show'));
        }
                 FlashMessage::danger('Impossivel excluir, pallet inexistente');
                 $this->redirectTo(route('pallets.show'));

   }

   public function registerPallet(Request $request):void
   {
        $params = $request->getParam('pallet');

        $newPallet = new Pallet($params);

        if(Pallet::exists(['length' => $newPallet->length, 'width' => $newPallet->width])){
            FlashMessage::danger('Pallet já cadastrado');
            $this->redirectTo(route('pallets.form'));
        }
        
        $newPallet->convertToInt('amount');
        $newPallet->convertToInt('minimum_amount');

        if($newPallet->save()){
            FlashMessage::success('Pallet registrado com sucesso!');
            $this->redirectTo(route('root'));
        }

        else{
            $title = "Cadastro de Pallets";
            FlashMessage::danger('Erro ao cadastrar pallet');
            $this->render('pallets/create',compact('title','newPallet'));
             return;
        }
   }

   public function retrievePallet():void
   {
         $pallets = Pallet::all();   
         $title = "Estoque Virtual";

        if($pallets){   
            $this->render('pallets/show',compact('title', 'pallets'));
            return;
        }

        $pallets = [];
        $this->render('pallets/show',compact('title','pallets'));

   }

   public function updatePallet(Request $request):void
   {    
        $params = $request->getParam('pallet');

        $pallet = Pallet::findBy(['id' => $params['id']]);

        if($pallet){

            $pallet->width = $params['width'];
            $pallet->length = $params['length'];
            $pallet->amount = $params['amount'];
            $pallet->minimum_amount = $params['minimum_amount'];
            $pallet->location = $params['location'];
            $pallet->item = $params['item'];

            $palletExist = Pallet::findBy(['length' => $pallet->length, 'width' => $pallet->width]);

            if($palletExist == $pallet){
                 FlashMessage::danger('Nenhuma alteração foi detectada');
                 $title = "Atualizar Pallets";
                 $this->render('pallets/update',compact('title','pallet'));
                 return;
            }

            if($palletExist && $palletExist->id !== $pallet->id ){
            FlashMessage::danger('Pallet já cadastrado');
            $this->redirectTo(route('pallets.show'));
            }

            if($pallet->isValid()){

                if($pallet->update(['width' => $params['width'], 'length' => $params['length'], 'amount' => $params['amount'], 'minimum_amount' => $params['minimum_amount'], 'location' => $params['location'], 'item' => $params['item'] ])){
                    FlashMessage::success('Pallet atualizado');
                    $this->redirectTo(route('pallets.show'));
                }

                else{ 
                   FlashMessage::danger('Falha ao atualizar pallet');
                   $this->redirectTo(route('pallets.show'));
                }
            }
            
            $title = "Atualizar Pallets";
            $this->render('pallets/update',compact('title','pallet'));
            return;
        }

        FlashMessage::danger('Pallet inexistente');
        $this->redirectTo(route('pallets.show'));

   }


}

?>