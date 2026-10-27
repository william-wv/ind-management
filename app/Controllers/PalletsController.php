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

         $this->render('pallets/create',compact('title'));
   }

   public function registerPallet(Request $request){
        $params = $request->getParam('pallet');

        $newPallet = new Pallet($params);

        if(!$newPallet->isValid()){
            FlashMessage::danger('Preencha todos os campos');
            $this->redirectTo(route('pallets.form'));
        }
        $palletExist = Pallet::exists(['length' => $params['length'], 'width' => $params['width'],]) || Pallet::findBy(['item' => $params['item']]);

        if($palletExist){
            FlashMessage::danger('Pallet já está cadastrado no sistema');
            $this->redirectTo(route('pallets.form'));
        }


        $newPallet->convertToInt('amount');
        $newPallet->convertToInt('minimum_amount');

        if($newPallet->save()){
            FlashMessage::success('Pallet registrado com sucesso!');
            $this->redirectTo(route('root'));
        }

        else{
            FlashMessage::danger('falha ao cadastrar pallet');
            $this->redirectTo(route('pallets.form'));
        }
   }


}

?>